<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    /**
     * List all backups, optionally filtered by tenant.
     */
    public function index(Request $request)
    {
        $tenants = Tenant::orderBy('nombre')->get(['id', 'nombre']);
        $filterTenant = $request->get('tenant_id');

        $backups = $this->scanBackups($filterTenant);

        return view('central.backups.index', compact('backups', 'tenants', 'filterTenant'));
    }

    /**
     * Run a backup for a specific tenant now.
     */
    public function store(Request $request)
    {
        $tenantId = $request->input('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            Artisan::call('tenant:backup', ['--tenant' => $tenantId]);
            CentralAuditLog::log('backup_run', "Backup manual ejecutado para «{$tenant->nombre}»", $tenantId);
            $message = "Backup creado para «{$tenant->nombre}».";
        } else {
            Artisan::call('tenant:backup');
            CentralAuditLog::log('backup_run', 'Backup manual ejecutado para todos los tenants');
            $message = 'Backup creado para todos los tenants.';
        }

        return redirect()->route('central.backups.index')
            ->with('success', $message);
    }

    /**
     * Download a backup file.
     */
    public function download(Request $request)
    {
        $filename = basename($request->get('file', ''));
        $filepath = $this->backupDir . '/' . $filename;

        if (!$filename || !file_exists($filepath) || !str_ends_with($filename, '.sql.gz')) {
            return back()->with('error', 'Archivo de backup no encontrado.');
        }

        return response()->download($filepath);
    }

    /**
     * Restore a backup for a tenant.
     */
    public function restore(Request $request)
    {
        $filename = basename($request->input('file', ''));
        $filepath = $this->backupDir . '/' . $filename;

        if (!$filename || !file_exists($filepath) || !str_ends_with($filename, '.sql.gz')) {
            return back()->with('error', 'Archivo de backup no encontrado.');
        }

        // Extract tenant DB name from filename: tenant_slug_2026-03-11_120000.sql.gz
        $dbName = $this->extractDbName($filename);
        if (!$dbName) {
            return back()->with('error', 'No se pudo determinar la base de datos del backup.');
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Use pure PHP restore (works on all servers)
        $result = $this->restorePurePhp($host, $port, $username, $password, $dbName, $filepath);

        if ($result === true) {
            $tenantId = str_replace('tenant_', '', $dbName);
            CentralAuditLog::log('backup_restored', "Backup restaurado: {$filename}", $tenantId, [
                'file' => $filename,
                'database' => $dbName,
            ]);

            return back()->with('success', "Backup «{$filename}» restaurado exitosamente en {$dbName}.");
        }

        return back()->with('error', 'Error al restaurar backup: ' . $result);
    }

    /**
     * Pure PHP restore implementation (no system calls needed).
     */
    protected function restorePurePhp(string $host, string $port, string $username, string $password, string $dbName, string $filepath): bool|string
    {
        try {
            // Connect to database
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            ]);

            // Read and decompress the backup file
            $gzHandle = gzopen($filepath, 'rb');
            if (!$gzHandle) {
                return 'No se pudo abrir el archivo de backup.';
            }

            $sql = '';
            while (!gzeof($gzHandle)) {
                $sql .= gzread($gzHandle, 8192);
            }
            gzclose($gzHandle);

            if (empty($sql)) {
                return 'El archivo de backup está vacío.';
            }

            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            // Split SQL into statements and execute
            // Handle multi-line statements properly
            $statements = $this->splitSqlStatements($sql);
            $executed = 0;
            $errors = [];

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement) || strpos($statement, '--') === 0) {
                    continue;
                }

                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (\PDOException $e) {
                    // Log but continue (some statements may fail due to existing data)
                    $errors[] = substr($e->getMessage(), 0, 100);
                    if (count($errors) > 10) {
                        break; // Too many errors, stop
                    }
                }
            }

            // Re-enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            if ($executed === 0 && count($errors) > 0) {
                Log::error("Restore failed", ['errors' => $errors]);
                return 'No se pudo ejecutar ninguna sentencia SQL.';
            }

            Log::info("Restore completed", [
                'database' => $dbName,
                'statements' => $executed,
                'errors' => count($errors),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Pure PHP restore failed: {$e->getMessage()}");
            return $e->getMessage();
        }
    }

    /**
     * Split SQL dump into individual statements.
     */
    protected function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            // Handle string literals
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
                continue;
            }

            if ($inString) {
                $current .= $char;
                // Check for escaped quotes
                if ($char === $stringChar) {
                    // Check if it's escaped
                    $escapes = 0;
                    for ($j = $i - 1; $j >= 0 && $sql[$j] === '\\'; $j--) {
                        $escapes++;
                    }
                    if ($escapes % 2 === 0) {
                        $inString = false;
                    }
                }
                continue;
            }

            // Check for statement terminator
            if ($char === ';') {
                $current .= $char;
                $statements[] = $current;
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Add any remaining content
        if (trim($current)) {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request)
    {
        $filename = basename($request->input('file', ''));
        $filepath = $this->backupDir . '/' . $filename;

        if (!$filename || !file_exists($filepath) || !str_ends_with($filename, '.sql.gz')) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        unlink($filepath);

        return back()->with('success', "Backup «{$filename}» eliminado.");
    }

    /**
     * Scan the backup directory and return structured info.
     */
    protected function scanBackups(?string $filterTenant = null): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $files = glob($this->backupDir . '/*.sql.gz');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $dbName = $this->extractDbName($filename);
            $tenantSlug = $dbName ? str_replace('tenant_', '', $dbName) : null;

            if ($filterTenant && $tenantSlug !== $filterTenant) {
                continue;
            }

            $backups[] = [
                'filename' => $filename,
                'tenant_id' => $tenantSlug,
                'database' => $dbName,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'date_human' => \Carbon\Carbon::createFromTimestamp(filemtime($file))->diffForHumans(),
            ];
        }

        // Sort newest first
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $backups;
    }

    protected function extractDbName(string $filename): ?string
    {
        // Format: tenant_slug_2026-03-11_120000.sql.gz
        if (preg_match('/^(.+)_\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
    }
}
