<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantAdminController extends Controller
{
    /**
     * Reset tenant database - drop all tables, run migrations, run seeders.
     */
    public function resetDatabase(Request $request, Tenant $tenant)
    {
        // Validate confirmation
        $validated = $request->validate([
            'confirmation_slug' => ['required', 'string'],
        ]);

        if ($validated['confirmation_slug'] !== $tenant->id) {
            return back()->with('error', 'El slug de confirmación no coincide.');
        }

        try {
            $dbName = $tenant->database()->getName();

            // Get all tables
            $tables = [];
            DB::connection('central')->select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema = ?",
                [$dbName]
            );

            // Drop all tables
            $tenant->run(function () {
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                }
            });

            // Re-create database if needed
            DB::connection('central')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

            // Run migrations
            $tenant->run(function () {
                Artisan::call('migrate', [
                    '--path' => '/database/migrations/tenant',
                    '--force' => true,
                ]);
            });

            // Run seeders
            $tenant->run(function () {
                Artisan::call('db:seed', ['--force' => true]);
            });

            CentralAuditLog::log('tenant_reset', "Base de datos reiniciada para «{$tenant->nombre}»", $tenant->id);

            return redirect()->route('central.tenants.show', $tenant)
                ->with('success', "Base de datos de «{$tenant->nombre}» reiniciada exitosamente.");

        } catch (\Throwable $e) {
            Log::error("Failed to reset database for tenant {$tenant->id}: {$e->getMessage()}");
            return back()->with('error', 'Error al reiniciar la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Completely delete a tenant and all associated data.
     */
    public function destroyCompletely(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'confirmation_slug' => ['required', 'string'],
        ]);

        if ($validated['confirmation_slug'] !== $tenant->id) {
            return back()->with('error', 'El slug de confirmación no coincide.');
        }

        $tenantName = $tenant->nombre;
        $tenantId = $tenant->id;

        try {
            // 1. Delete backups
            $backupDir = storage_path('app/backups');
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/tenant_' . $tenantId . '_*.sql.gz');
                foreach ($files as $file) {
                    unlink($file);
                }
            }

            // 2. Drop database
            $dbName = $tenant->database()->getName();
            DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$dbName}`");

            // 3. Delete domains
            $tenant->domains()->delete();

            // 4. Delete tenant record
            $tenant->delete();

            CentralAuditLog::log('tenant_deleted', "Compañía «{$tenantName}» eliminada completamente", $tenantId, [
                'database_dropped' => $dbName,
                'backups_deleted' => count($files ?? []),
            ]);

            return redirect()->route('central.tenants.index')
                ->with('success', "Compañía «{$tenantName}» eliminada completamente junto con su base de datos y backups.");

        } catch (\Throwable $e) {
            Log::error("Failed to delete tenant {$tenant->id}: {$e->getMessage()}");
            return back()->with('error', 'Error al eliminar la compañía: ' . $e->getMessage());
        }
    }
}
