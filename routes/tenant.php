<?php

declare(strict_types=1);

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

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain('{tenant}.' . $domain)
        ->middleware([
            'web',
            InitializeTenancyBySubdomain::class,
            PreventAccessFromCentralDomains::class,
        ])
        ->group(base_path('routes/app.php'));
}
