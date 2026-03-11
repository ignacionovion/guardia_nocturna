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

        $command = sprintf(
            'gunzip -c %s | mysql --host=%s --port=%s --user=%s --password=%s %s 2>&1',
            escapeshellarg($filepath),
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($dbName)
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            // Find tenant by DB name
            $tenantId = str_replace('tenant_', '', $dbName);
            CentralAuditLog::log('backup_restored', "Backup restaurado: {$filename}", $tenantId, [
                'file' => $filename,
                'database' => $dbName,
            ]);

            return back()->with('success', "Backup «{$filename}» restaurado exitosamente en {$dbName}.");
        }

        Log::error("Backup restore failed", [
            'file' => $filename,
            'database' => $dbName,
            'exit_code' => $exitCode,
            'output' => implode("\n", $output),
        ]);

        return back()->with('error', 'Error al restaurar backup: ' . implode(' ', $output));
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
