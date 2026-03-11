<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use App\Services\TenantMetricsService;

class CentralDashboardController extends Controller
{
    public function __construct(
        protected TenantMetricsService $metrics,
    ) {}

    public function index()
    {
        $tenantsCount = Tenant::count();
        $activeTenantsCount = Tenant::where('activo', true)->count();
        $bodiesCount = Body::count();
        $recentTenants = Tenant::with(['domains', 'body'])->latest()->take(5)->get();

        $globalMetrics = $this->metrics->globalSummary();

        // Per-tenant quick metrics for recent tenants
        $tenantHealthMap = [];
        foreach ($recentTenants as $tenant) {
            $tenantHealthMap[$tenant->id] = $this->metrics->healthStatus($tenant);
        }

        // Tenants by estado
        $tenantsByEstado = Tenant::selectRaw('estado, COUNT(*) as count')
            ->groupBy('estado')
            ->pluck('count', 'estado')
            ->toArray();

        // Tenants by plan
        $tenantsByPlan = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        // Expiring soon (next 7 days)
        $expiringSoon = Tenant::where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->orderBy('fecha_vencimiento')
            ->get(['id', 'nombre', 'fecha_vencimiento', 'plan']);

        // Recent audit logs
        $recentAuditLogs = CentralAuditLog::latest()
            ->take(10)
            ->get();

        // Backup stats
        $backupDir = storage_path('app/backups');
        $backupCount = 0;
        $backupSize = 0;
        $lastBackup = null;
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql.gz');
            $backupCount = count($files);
            foreach ($files as $file) {
                $backupSize += filesize($file);
            }
            if ($backupCount > 0) {
                $lastBackup = date('Y-m-d H:i', max(array_map('filemtime', $files)));
            }
        }

        return view('central.dashboard', compact(
            'tenantsCount',
            'activeTenantsCount',
            'bodiesCount',
            'recentTenants',
            'globalMetrics',
            'tenantHealthMap',
            'tenantsByEstado',
            'tenantsByPlan',
            'expiringSoon',
            'recentAuditLogs',
            'backupCount',
            'backupSize',
            'lastBackup',
        ));
    }
}
