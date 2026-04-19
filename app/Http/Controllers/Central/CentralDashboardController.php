<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use App\Models\CentralAuditLog;
use App\Models\OperationalAlert;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\OperationalHealthService;
use App\Services\TenantMetricsService;

class CentralDashboardController extends Controller
{
    public function __construct(
        protected TenantMetricsService $metrics,
        protected OperationalHealthService $operationalHealth,
    ) {}

    public function index()
    {
        $operationalHealth = $this->operationalHealth->dashboardSummary();

        $operationalAlertsOpen = OperationalAlert::query()
            ->where('status', OperationalAlert::STATUS_OPEN)
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 0 ELSE 1 END")
            ->orderByDesc('last_triggered_at')
            ->limit(25)
            ->get();

        $operationalAlertsOpenCount = OperationalAlert::query()
            ->where('status', OperationalAlert::STATUS_OPEN)
            ->count();

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

        // Tenants by plan (dynamic from DB plan catalog)
        $tenantPlanCounts = Tenant::whereNotNull('plan_id')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        $tenantsByPlan = Plan::active()->ordered()->get(['id', 'slug', 'nombre'])
            ->map(function (Plan $plan) use ($tenantPlanCounts, $tenantsCount) {
                $count = (int) ($tenantPlanCounts[$plan->id] ?? 0);

                return [
                    'slug' => $plan->slug,
                    'label' => $plan->nombre,
                    'count' => $count,
                    'percentage' => $tenantsCount > 0 ? ($count / $tenantsCount * 100) : 0,
                ];
            });

        // Expiring soon (next 7 days)
        $expiringSoon = Tenant::where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->orderBy('fecha_vencimiento')
            ->with('planRelation')
            ->get(['id', 'nombre', 'fecha_vencimiento', 'plan_id']);

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
            'operationalHealth',
            'operationalAlertsOpen',
            'operationalAlertsOpenCount',
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
