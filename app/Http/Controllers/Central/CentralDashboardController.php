<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
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

        return view('central.dashboard', compact(
            'tenantsCount',
            'activeTenantsCount',
            'bodiesCount',
            'recentTenants',
            'globalMetrics',
            'tenantHealthMap',
        ));
    }
}
