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

$centralDomains = config('tenancy.central_domains', []);

if (!empty($centralDomains)) {
    // Build regex pattern for all central domains
    $domainPattern = implode('|', array_map('preg_quote', $centralDomains));

    // Rutas públicas QR (sin auth, sin tenant-active check)
    // Solo inicializa tenant y web middleware
    Route::domain('{tenant}.sas.dev-app.cl')
        ->where(['domain' => $domainPattern])
        ->middleware([
            'web',
            InitializeTenancyBySubdomain::class,
        ])
        ->group(base_path('routes/qr-public.php'));

    // Rutas principales de la app (con auth y tenant-active check)
    Route::domain('{tenant}.sas.dev-app.cl')
        ->where(['domain' => $domainPattern])
        ->middleware([
            'web',
            InitializeTenancyBySubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureTenantActive::class,
        ])
        ->group(base_path('routes/app.php'));
}
