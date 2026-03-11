<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class TenantBackupCommand extends Command
{
    protected $signature = 'tenant:backup
                            {--tenant= : Backup only a specific tenant ID}
                            {--keep=7 : Number of days to keep old backups}';

    protected $description = 'Create MySQL dumps for tenant databases';

    public function handle(): int
    {
        $specificTenant = $this->option('tenant');
        $keepDays = (int) $this->option('keep');

        $query = Tenant::where('activo', true);
        if ($specificTenant) {
            $query->where('id', $specificTenant);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return self::SUCCESS;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $this->info("Backing up {$tenants->count()} tenant(s)...");
        $this->newLine();

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $ok = 0;
        $fail = 0;

        foreach ($tenants as $tenant) {
            $dbName = $tenant->database()->getName();
            $date = now()->format('Y-m-d_His');
            $filename = "{$dbName}_{$date}.sql.gz";
            $filepath = "{$backupDir}/{$filename}";

            $this->info("  Backing up {$dbName}...");

            // Check if mysqldump is available
            $mysqldumpPath = $this->findMysqldump();
            if (!$mysqldumpPath) {
                $this->error("  ✗ mysqldump not found. Install mysql-client.");
                $fail++;
                continue;
            }

            $success = $this->runBackup($mysqldumpPath, $host, $port, $username, $password, $dbName, $filepath);

            if ($success && file_exists($filepath) && filesize($filepath) > 0) {
                $size = $this->formatBytes(filesize($filepath));
                $this->info("  ✓ Saved: {$filename} ({$size})");
                Log::channel('tenant')->info("Backup created for {$dbName}: {$filename} ({$size})");
                $ok++;
            } else {
                $this->error("  ✗ Failed to backup {$dbName}");
                Log::channel('tenant')->error("Backup failed for {$dbName}");
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                $fail++;
            }
        }

        // Cleanup old backups
        $this->cleanOldBackups($backupDir, $keepDays);

        $this->newLine();
        $this->info("Done: {$ok} OK, {$fail} failed.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run backup using available methods (Process, proc_open, or pure PHP fallback).
     */
    protected function runBackup(string $mysqldump, string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool
    {
        // Try 1: Symfony Process (if proc_open is available)
        if (function_exists('proc_open')) {
            try {
                $command = [
                    $mysqldump,
                    '--host=' . $host,
                    '--port=' . $port,
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
            } catch (\Throwable $e) {
                Log::warning("Symfony Process backup failed: {$e->getMessage()}");
            }
        }

        // Try 2: proc_open directly
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

        // Try 3: Pure PHP fallback (always works but slower)
        $this->warn("  → Usando método PHP puro (más lento pero compatible)...");
        return $this->runBackupPurePhp($host, $port, $username, $password, $dbName, $filepath);
    }

    /**
     * Pure PHP backup implementation (no system calls needed).
     */
    protected function runBackupPurePhp(string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool
    {
        try {
            // Connect to database
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $gzFile = gzopen($filepath, 'wb9');
            if (!$gzFile) {
                return false;
            }

            // Header
            gzwrite($gzFile, "-- GuardiAPP Backup\n");
            gzwrite($gzFile, "-- Database: {$dbName}\n");
            gzwrite($gzFile, "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n");
            gzwrite($gzFile, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Table structure
                gzwrite($gzFile, "-- Table: {$table}\n");
                gzwrite($gzFile, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                gzwrite($gzFile, $createTable['Create Table'] . ";\n\n");

                // Table data
                $rows = $pdo->query("SELECT * FROM `{$table}`", \PDO::FETCH_ASSOC);
                $rowCount = 0;
                $values = [];

                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $rowValues[] = $value;
                        } else {
                            $rowValues[] = "'" . str_replace("'", "''", $value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                    $rowCount++;

                    // Write in batches of 1000 rows to avoid memory issues
                    if (count($values) >= 1000) {
                        gzwrite($gzFile, "INSERT INTO `{$table}` VALUES " . implode(', ', $values) . ";\n");
                        $values = [];
                    }
                }

                if (count($values) > 0) {
                    gzwrite($gzFile, "INSERT INTO `{$table}` VALUES " . implode(', ', $values) . ";\n");
                }

                if ($rowCount > 0) {
                    gzwrite($gzFile, "\n");
                }
            }

            // Footer
            gzwrite($gzFile, "SET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($gzFile);

            return true;
        } catch (\Throwable $e) {
            Log::error("Pure PHP backup failed: {$e->getMessage()}");
            if (file_exists($filepath)) {
                unlink($filepath);
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
            'mysqldump',  // Let PATH find it
        ];

        foreach ($paths as $path) {
            if ($path === 'mysqldump') {
                // Try to run via shell
                $result = shell_exec('which mysqldump 2>/dev/null') ?? '';
                $result = trim($result);
                if ($result && is_executable($result)) {
                    return $result;
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function cleanOldBackups(string $dir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $deleted = 0;

        foreach (glob("{$dir}/*.sql.gz") as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s) (older than {$keepDays} days).");
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
    }
}
