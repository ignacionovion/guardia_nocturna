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
     * Run backup using Symfony Process (works even when exec is disabled).
     */
    protected function runBackup(string $mysqldump, string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool
    {
        // Try Symfony Process first (works with proc_open)
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
                // Compress with gzip
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

        // Fallback: try shell command via proc_open
        $this->warn("  → Fallback to shell backup...");

        $dumpCommand = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --databases %s 2>/dev/null',
            escapeshellarg($mysqldump),
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($dbName)
        );

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['file', '/dev/null', 'w'],  // stderr
        ];

        $process = proc_open($dumpCommand, $descriptors, $pipes);

        if (!is_resource($process)) {
            return false;
        }

        fclose($pipes[0]); // close stdin

        // Read output and compress
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

        return false;
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
