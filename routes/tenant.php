<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantActive;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Todas las rutas de la aplicación operativa viven aquí.
| Se resuelven por subdominio: {tenant}.dev-app.cl
|
*/

// Rutas principales de la app (con auth y tenant-active check)
Route::domain('{tenant}.dev-app.cl')
    ->middleware([
        'web',
        InitializeTenancyBySubdomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantActive::class,
    ])
    ->group(base_path('routes/app.php'));

// Rutas públicas QR (sin auth, sin tenant-active check)
Route::domain('{tenant}.dev-app.cl')
    ->middleware([
        'web',
        InitializeTenancyBySubdomain::class,
    ])
    ->group(base_path('routes/qr-public.php'));
