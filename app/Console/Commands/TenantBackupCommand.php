<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Respaldos MySQL por tenant (activos, vencidos, suspendidos; excluye cancelados salvo --include-cancelled).
 *
 * Política: el booleano `activo` no define si se respalda; el estado comercial no debe privar
 * de continuidad operativa ante mora (los datos siguen siendo responsabilidad del servicio).
 */
class TenantBackupCommand extends Command
{
    protected $signature = 'tenant:backup
                            {--tenant= : Solo este ID de tenant}
                            {--keep= : Días de retención de archivos .sql.gz (por defecto: config backups.retention_days)}
                            {--include-cancelled : Con --tenant, permite respaldar un tenant cancelado (exportación puntual)}';

    protected $description = 'Dump MySQL de cada base tenant (incluye suspendidos/vencidos; excluye cancelados)';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $specificTenant = $this->option('tenant');
        $keepDays = $this->option('keep') !== null && $this->option('keep') !== ''
            ? (int) $this->option('keep')
            : (int) config('backups.retention_days', 7);
        $includeCancelled = (bool) $this->option('include-cancelled');

        $backupDir = $this->resolveBackupDirectory();

        Log::channel('tenant')->info('tenant:backup job_start', [
            'backup_dir' => $backupDir,
            'keep_days' => $keepDays,
            'tenant_filter' => $specificTenant,
            'include_cancelled' => $includeCancelled,
            'app_env' => config('app.env'),
        ]);

        $preflight = $this->runPreflight($backupDir);
        if ($preflight !== null) {
            Log::channel('tenant')->error('tenant:backup preflight_failed', $preflight);

            return self::FAILURE;
        }

        $tenants = $this->resolveTenants($specificTenant, $includeCancelled);
        if ($tenants->isEmpty()) {
            if ($specificTenant && ! $includeCancelled) {
                $one = Tenant::query()->where('id', $specificTenant)->first();
                if ($one && $one->estado === Tenant::ESTADO_CANCELADO) {
                    $this->warn('Ese tenant está cancelado. Usá --include-cancelled para un dump puntual.');
                }
            }
            $this->warn('No hay tenants elegibles para backup.');
            Log::channel('tenant')->warning('tenant:backup skipped: no eligible tenants', [
                'tenant_filter' => $specificTenant,
                'include_cancelled' => $includeCancelled,
            ]);

            return self::SUCCESS;
        }

        $host = (string) config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $username = (string) config('database.connections.mysql.username');
        $password = (string) config('database.connections.mysql.password');

        $mysqldumpPath = $this->findMysqldump();
        if ($mysqldumpPath === null) {
            $hint = 'Instalá mysql-client / mariadb-client y asegurá mysqldump en PATH o en rutas estándar.';
            $this->error("mysqldump no encontrado. {$hint}");
            Log::channel('tenant')->error('tenant:backup aborted: mysqldump not found', ['hint' => $hint]);

            return self::FAILURE;
        }

        Log::channel('tenant')->info('tenant:backup batch_start', [
            'tenant_count' => $tenants->count(),
            'mysqldump' => $mysqldumpPath,
            'mysql_host' => $host,
            'mysql_port' => $port,
        ]);

        $this->info("Respaldando {$tenants->count()} tenant(s)…");
        $this->newLine();

        $ok = 0;
        $fail = 0;

        foreach ($tenants as $tenant) {
            $dbName = $tenant->database()->getName();
            $date = now()->format('Y-m-d_His');
            $filename = "{$dbName}_{$date}__tenant-{$tenant->id}.sql.gz";
            $filepath = "{$backupDir}/{$filename}";

            $t0 = microtime(true);
            $this->line("  [{$tenant->id}] {$dbName} (estado={$tenant->estado}, activo=" . ($tenant->activo ? '1' : '0') . ')…');

            try {
                $success = $this->runBackup($mysqldumpPath, $host, $port, $username, $password, $dbName, $filepath);
            } catch (Throwable $e) {
                $success = false;
                Log::channel('tenant')->error('tenant:backup tenant_exception', [
                    'tenant_id' => $tenant->id,
                    'database' => $dbName,
                    'message' => $e->getMessage(),
                ]);
            }

            $durationMs = (int) round((microtime(true) - $t0) * 1000);

            if ($success && file_exists($filepath) && filesize($filepath) > 0) {
                $size = $this->formatBytes((int) filesize($filepath));
                $this->info("    ✓ {$filename} ({$size})");
                Log::channel('tenant')->info('tenant:backup tenant_ok', [
                    'tenant_id' => $tenant->id,
                    'estado' => $tenant->estado,
                    'activo' => (bool) $tenant->activo,
                    'database' => $dbName,
                    'file' => $filename,
                    'size_human' => $size,
                    'duration_ms' => $durationMs,
                ]);
                $ok++;
            } else {
                $this->error("    ✗ Falló backup de {$dbName}");
                Log::channel('tenant')->error('tenant:backup tenant_failed', [
                    'tenant_id' => $tenant->id,
                    'estado' => $tenant->estado,
                    'database' => $dbName,
                    'filepath' => $filepath,
                    'duration_ms' => $durationMs,
                ]);
                if (file_exists($filepath)) {
                    @unlink($filepath);
                }
                $fail++;
            }
        }

        $deletedOld = $this->cleanOldBackups($backupDir, $keepDays);

        $totalMs = (int) round((microtime(true) - $startedAt) * 1000);

        $this->newLine();
        $this->info("Listo: {$ok} OK, {$fail} fallidos. Retención: {$deletedOld} archivo(s) eliminado(s). Tiempo total: " . round($totalMs / 1000, 1) . 's.');
        $this->line('Log operativo: canal `tenant` → storage/logs/tenant/tenant.log');

        Log::channel('tenant')->info('tenant:backup job_finished', [
            'ok' => $ok,
            'failed' => $fail,
            'retention_deleted_files' => $deletedOld,
            'keep_days' => $keepDays,
            'backup_dir' => $backupDir,
            'duration_ms' => $totalMs,
        ]);

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null null = OK
     */
    protected function runPreflight(string $backupDir): ?array
    {
        if (! is_dir($backupDir)) {
            if (! @mkdir($backupDir, 0755, true)) {
                return ['reason' => 'cannot_create_dir', 'dir' => $backupDir];
            }
        }

        if (! is_writable($backupDir)) {
            return ['reason' => 'dir_not_writable', 'dir' => $backupDir];
        }

        try {
            $this->assertMysqlServerReachable();
        } catch (Throwable $e) {
            return [
                'reason' => 'mysql_connection_failed',
                'message' => $e->getMessage(),
            ];
        }

        $this->info("Preflight OK · directorio: {$backupDir}");

        return null;
    }

    protected function resolveBackupDirectory(): string
    {
        $configured = config('backups.path');

        return $configured !== null && $configured !== ''
            ? (string) $configured
            : storage_path('app/backups');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Tenant>
     */
    protected function resolveTenants(?string $specificTenant, bool $includeCancelled)
    {
        $q = Tenant::query();

        if ($specificTenant !== null && $specificTenant !== '') {
            $q->where('id', $specificTenant);
            if (! $includeCancelled) {
                $q->where('estado', '!=', Tenant::ESTADO_CANCELADO);
            }
        } else {
            $q->forDatabaseBackup();
        }

        return $q->orderBy('id')->get();
    }

    /**
     * Run backup using available methods (Process, proc_open, or pure PHP fallback).
     */
    protected function runBackup(string $mysqldump, string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool
    {
        if (function_exists('proc_open')) {
            try {
                $command = [
                    $mysqldump,
                    '--host=' . $host,
                    '--port=' . (string) $port,
                    '--user=' . $username,
                    '--password=' . $password,
                    '--single-transaction',
                    '--routines',
                    '--triggers',
                    '--databases',
                    $dbName,
                ];

                $process = new Process($command, null, null, null, 600);
                $process->run();

                if ($process->isSuccessful()) {
                    $gzFile = gzopen($filepath, 'wb9');
                    if ($gzFile) {
                        gzwrite($gzFile, $process->getOutput());
                        gzclose($gzFile);

                        return true;
                    }
                }

                $err = trim($process->getErrorOutput() . ' ' . $process->getOutput());
                Log::channel('tenant')->warning('tenant:backup mysqldump process failed', [
                    'database' => $dbName,
                    'exit_code' => $process->getExitCode(),
                    'output_excerpt' => mb_substr($err, 0, 2000),
                ]);
            } catch (Throwable $e) {
                Log::channel('tenant')->warning('tenant:backup Symfony Process exception', [
                    'database' => $dbName,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (function_exists('proc_open')) {
            $dumpCommand = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --databases %s',
                escapeshellarg($mysqldump),
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName)
            );

            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['file', '/dev/null', 'w'],
            ];

            $process = @proc_open($dumpCommand, $descriptors, $pipes);

            if (is_resource($process)) {
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $exitCode = proc_close($process);

                if ($exitCode === 0 && $output) {
                    $gzFile = gzopen($filepath, 'wb9');
                    if ($gzFile) {
                        gzwrite($gzFile, $output);
                        gzclose($gzFile);

                        return true;
                    }
                }
            }
        }

        $this->warn('    → Intentando respaldo en PHP (más lento)…');

        return $this->runBackupPurePhp($host, $port, $username, $password, $dbName, $filepath);
    }

    /**
     * Pure PHP backup implementation (no system calls needed).
     */
    protected function runBackupPurePhp(string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $gzFile = gzopen($filepath, 'wb9');
            if (! $gzFile) {
                return false;
            }

            gzwrite($gzFile, "-- GuardiAPP Backup\n");
            gzwrite($gzFile, "-- Database: {$dbName}\n");
            gzwrite($gzFile, '-- Generated: ' . now()->format('Y-m-d H:i:s') . "\n");
            gzwrite($gzFile, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                gzwrite($gzFile, "-- Table: {$table}\n");
                gzwrite($gzFile, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTable = $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch(\PDO::FETCH_ASSOC);
                gzwrite($gzFile, $createTable['Create Table'] . ";\n\n");

                $stmt = $pdo->query('SELECT * FROM `' . $table . '`');
                $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
                $values = [];

                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $rowValues[] = $value;
                        } else {
                            $rowValues[] = "'" . str_replace("'", "''", (string) $value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';

                    if (count($values) >= 1000) {
                        gzwrite($gzFile, 'INSERT INTO `' . $table . '` VALUES ' . implode(', ', $values) . ";\n");
                        $values = [];
                    }
                }

                if (count($values) > 0) {
                    gzwrite($gzFile, 'INSERT INTO `' . $table . '` VALUES ' . implode(', ', $values) . ";\n");
                }

                gzwrite($gzFile, "\n");
            }

            gzwrite($gzFile, "SET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($gzFile);

            return true;
        } catch (Throwable $e) {
            Log::channel('tenant')->error('tenant:backup pure_php_failed', [
                'database' => $dbName,
                'message' => $e->getMessage(),
            ]);
            if (file_exists($filepath)) {
                @unlink($filepath);
            }

            return false;
        }
    }

    protected function findMysqldump(): ?string
    {
        $paths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            'mysqldump',
        ];

        foreach ($paths as $path) {
            if ($path === 'mysqldump') {
                $result = trim((string) shell_exec('which mysqldump 2>/dev/null'));
                if ($result !== '' && is_executable($result)) {
                    return $result;
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Solo archivos *.sql.gz bajo $dir cuyo mtime sea anterior al corte (retención por días).
     */
    protected function cleanOldBackups(string $dir, int $keepDays): int
    {
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $deleted = 0;
        $deletedNames = [];

        foreach (glob($dir . '/*.sql.gz') ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }
            if (filemtime($file) < $cutoff) {
                $name = basename($file);
                if (@unlink($file)) {
                    $deleted++;
                    if (count($deletedNames) < 25) {
                        $deletedNames[] = $name;
                    }
                }
            }
        }

        if ($deleted > 0) {
            $this->info("Retención: eliminados {$deleted} archivo(s) más viejos que {$keepDays} día(s).");
            Log::channel('tenant')->info('tenant:backup retention_cleanup', [
                'deleted_count' => $deleted,
                'keep_days' => $keepDays,
                'cutoff_timestamp' => $cutoff,
                'sample_filenames' => $deletedNames,
            ]);
        }

        return $deleted;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));

        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }

    /**
     * Valida conectividad al servidor MySQL usado por mysqldump (misma config que tenant dumps).
     */
    protected function assertMysqlServerReachable(): void
    {
        try {
            DB::connection('mysql')->select('SELECT 1 AS ok');

            return;
        } catch (Throwable $e) {
            try {
                DB::connection('central')->select('SELECT 1 AS ok');

                return;
            } catch (Throwable $e2) {
                throw new \RuntimeException(
                    'No se pudo conectar al MySQL (connection `mysql` ni `central`): ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }
    }
}
