<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use App\Models\Tenant;

class CentralDashboardController extends Controller
{
    public function index()
    {
        $tenantsCount = Tenant::count();
        $activeTenantsCount = Tenant::where('activo', true)->count();
        $bodiesCount = Body::count();
        $recentTenants = Tenant::with('domains')->latest()->take(5)->get();

        return view('central.dashboard', compact(
            'tenantsCount',
            'activeTenantsCount',
            'bodiesCount',
            'recentTenants',
        ));
    }
}
