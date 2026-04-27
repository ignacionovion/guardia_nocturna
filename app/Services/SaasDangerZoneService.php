<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Body;
use App\Models\CentralAuditLog;
use App\Models\OperationalAlert;
use App\Models\OperationalMetric;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Operaciones destructivas masivas del panel central (solo invocadas desde controladores protegidos).
 */
final class SaasDangerZoneService
{
    public const WRITTEN_CONFIRMATION_PHRASE = 'ELIMINAR TODO';

    /**
     * Contadores actuales para UI y auditoría.
     *
     * @return array<string, int>
     */
    public function snapshotCounts(): array
    {
        return [
            'tenants' => (int) Tenant::query()->count(),
            'bodies' => (int) Body::query()->count(),
            'operational_metrics' => (int) OperationalMetric::query()->count(),
            'operational_alerts' => (int) OperationalAlert::query()->count(),
            'central_audit_logs' => (int) CentralAuditLog::query()->count(),
        ];
    }

    /**
     * Modo 1: vaciar compañías (tenants + datos centrales ligados + cuerpos).
     *
     * @return array<string, int|string>
     */
    public function clearTenantsAndOrgData(): array
    {
        $tenantsCount = (int) Tenant::query()->count();
        $backupFilesDeleted = 0;

        foreach (Tenant::query()->orderBy('id')->cursor() as $tenant) {
            $backupFilesDeleted += $this->deleteTenantBackupFiles((string) $tenant->id);
            $tenant->delete();
        }

        $bodiesDeleted = (int) Body::query()->delete();

        CentralAuditLog::query()->whereNotNull('tenant_id')->delete();

        return [
            'tenants_deleted' => $tenantsCount,
            'bodies_deleted' => $bodiesDeleted,
            'backup_files_deleted' => $backupFilesDeleted,
        ];
    }

    /**
     * Modo 2: además de vaciar compañías, limpia métricas/alertas operativas y el historial de auditoría central.
     *
     * @return array<string, int|string>
     */
    public function resetSaasOperationalState(): array
    {
        $org = $this->clearTenantsAndOrgData();

        $metricsDeleted = (int) OperationalMetric::query()->delete();
        $alertsDeleted = (int) OperationalAlert::query()->delete();

        $auditDeleted = (int) DB::connection('central')->table('central_audit_logs')->delete();

        return array_merge($org, [
            'operational_metrics_deleted' => $metricsDeleted,
            'operational_alerts_deleted' => $alertsDeleted,
            'central_audit_logs_deleted' => $auditDeleted,
        ]);
    }

    private function deleteTenantBackupFiles(string $tenantId): int
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            return 0;
        }

        $pattern = $backupDir . '/tenant_' . $tenantId . '_*.sql.gz';
        $files = glob($pattern) ?: [];
        $n = 0;
        foreach ($files as $file) {
            if (is_file($file) && @unlink($file)) {
                $n++;
            }
        }

        return $n;
    }
}
