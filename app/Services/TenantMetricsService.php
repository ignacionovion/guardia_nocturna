<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantMetricsService
{
    /**
     * Get metrics for a single tenant.
     */
    public function forTenant(Tenant $tenant): array
    {
        $dbName = $tenant->database()->getName();

        return [
            'db_size'       => $this->getDatabaseSize($dbName),
            'table_count'   => $this->getTableCount($dbName),
            'users_count'   => $this->getTenantTableCount($tenant, 'users'),
            'active_users'  => $this->getTenantTableCount($tenant, 'users', ['active' => true]),
            'sessions_count'=> $this->getSessionCount($dbName),
            'storage_size'  => $this->getStorageSize($tenant),
            'db_exists'     => $this->databaseExists($dbName),
            'migrations'    => $this->getMigrationCount($tenant),
            'last_login'    => $this->getLastLogin($tenant),
            'created_at'    => $tenant->created_at,
        ];
    }

    /**
     * Get summary metrics for all tenants (for dashboard).
     */
    public function globalSummary(): array
    {
        $tenants = Tenant::with('domains')->get();
        $totalDbSize = 0;
        $totalUsers = 0;
        $totalStorage = 0;
        $healthyCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $expiringSoon = 0;

        foreach ($tenants as $tenant) {
            $dbName = $tenant->database()->getName();
            $dbExists = $this->databaseExists($dbName);

            if ($dbExists) {
                $size = $this->getDatabaseSizeBytes($dbName);
                $totalDbSize += $size;
                $totalUsers += $this->getTenantTableCount($tenant, 'users');
                $healthyCount++;
            } else {
                $errorCount++;
            }

            $totalStorage += $this->getStorageSizeBytes($tenant);

            if ($tenant->fecha_vencimiento && $tenant->fecha_vencimiento->isBefore(now()->addDays(30))) {
                $expiringSoon++;
                if ($tenant->fecha_vencimiento->isPast()) {
                    $warningCount++;
                }
            }
        }

        return [
            'total_tenants'    => $tenants->count(),
            'active_tenants'   => $tenants->where('activo', true)->count(),
            'inactive_tenants' => $tenants->where('activo', false)->count(),
            'healthy'          => $healthyCount,
            'warnings'         => $warningCount,
            'errors'           => $errorCount,
            'expiring_soon'    => $expiringSoon,
            'total_db_size'    => $this->formatBytes($totalDbSize),
            'total_db_bytes'   => $totalDbSize,
            'total_users'      => $totalUsers,
            'total_storage'    => $this->formatBytes($totalStorage),
        ];
    }

    /**
     * Get health status for a tenant.
     */
    public function healthStatus(Tenant $tenant): array
    {
        $checks = [];
        $dbName = $tenant->database()->getName();

        // Check DB exists
        $dbExists = $this->databaseExists($dbName);
        $checks[] = [
            'name' => 'Base de datos',
            'status' => $dbExists ? 'ok' : 'error',
            'detail' => $dbExists ? $dbName : 'No existe',
        ];

        // Check domains
        $hasDomains = $tenant->domains()->count() > 0;
        $checks[] = [
            'name' => 'Dominio',
            'status' => $hasDomains ? 'ok' : 'error',
            'detail' => $hasDomains ? $tenant->domains->first()->domain : 'Sin dominio',
        ];

        // Check migrations
        if ($dbExists) {
            $migrations = $this->getMigrationCount($tenant);
            $checks[] = [
                'name' => 'Migraciones',
                'status' => $migrations > 0 ? 'ok' : 'warning',
                'detail' => "{$migrations} ejecutadas",
            ];

            // Check users exist
            $userCount = $this->getTenantTableCount($tenant, 'users');
            $checks[] = [
                'name' => 'Usuarios',
                'status' => $userCount > 0 ? 'ok' : 'warning',
                'detail' => "{$userCount} registrados",
            ];
        }

        // Check expiration
        if ($tenant->fecha_vencimiento) {
            $expired = $tenant->fecha_vencimiento->isPast();
            $expiringSoon = !$expired && $tenant->fecha_vencimiento->isBefore(now()->addDays(30));
            $checks[] = [
                'name' => 'Vencimiento',
                'status' => $expired ? 'error' : ($expiringSoon ? 'warning' : 'ok'),
                'detail' => $expired
                    ? 'Vencida el ' . $tenant->fecha_vencimiento->format('d/m/Y')
                    : $tenant->fecha_vencimiento->format('d/m/Y'),
            ];
        }

        // Overall status
        $statuses = array_column($checks, 'status');
        $overall = 'ok';
        if (in_array('error', $statuses)) $overall = 'error';
        elseif (in_array('warning', $statuses)) $overall = 'warning';

        return [
            'overall' => $overall,
            'checks'  => $checks,
        ];
    }

    // ─── Private helpers ─────────────────────────────────────────

    protected function databaseExists(string $name): bool
    {
        try {
            $result = DB::connection('central')->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
                [$name]
            );
            return count($result) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getDatabaseSizeBytes(string $dbName): int
    {
        try {
            $result = DB::connection('central')->select(
                "SELECT SUM(data_length + index_length) AS size
                 FROM information_schema.TABLES
                 WHERE table_schema = ?",
                [$dbName]
            );
            return (int) ($result[0]->size ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getDatabaseSize(string $dbName): string
    {
        return $this->formatBytes($this->getDatabaseSizeBytes($dbName));
    }

    protected function getTableCount(string $dbName): int
    {
        try {
            $result = DB::connection('central')->select(
                "SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE table_schema = ?",
                [$dbName]
            );
            return (int) ($result[0]->cnt ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getTenantTableCount(Tenant $tenant, string $table, array $where = []): int
    {
        try {
            return (int) $tenant->run(function () use ($table, $where) {
                $query = DB::table($table);
                foreach ($where as $col => $val) {
                    $query->where($col, $val);
                }
                return $query->count();
            });
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getSessionCount(string $dbName): int
    {
        try {
            $result = DB::connection('central')->select(
                "SELECT COUNT(*) as cnt FROM information_schema.TABLES WHERE table_schema = ? AND table_name = 'sessions'",
                [$dbName]
            );
            if ((int) ($result[0]->cnt ?? 0) === 0) return 0;

            $result = DB::connection('central')->select("SELECT COUNT(*) as cnt FROM `{$dbName}`.`sessions`");
            return (int) ($result[0]->cnt ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getStorageSizeBytes(Tenant $tenant): int
    {
        $path = storage_path("app/tenant-{$tenant->id}");
        if (!is_dir($path)) return 0;

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    protected function getStorageSize(Tenant $tenant): string
    {
        return $this->formatBytes($this->getStorageSizeBytes($tenant));
    }

    protected function getMigrationCount(Tenant $tenant): int
    {
        try {
            return (int) $tenant->run(function () {
                return DB::table('migrations')->count();
            });
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getLastLogin(Tenant $tenant): ?string
    {
        try {
            return $tenant->run(function () {
                $hasColumn = DB::getSchemaBuilder()->hasColumn('users', 'last_login_at');
                if (!$hasColumn) return null;
                $user = DB::table('users')->whereNotNull('last_login_at')->orderByDesc('last_login_at')->first();
                return $user?->last_login_at;
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
    }
}
