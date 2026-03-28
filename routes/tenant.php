<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\EnsureTenantHasPlan;
use App\Http\Middleware\EnsureTenantHasPlanForApp;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
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
    ->where(['tenant' => '^(?!sas$|www$|api$|test$|staging$|localhost$|127$)[a-z0-9\-]+'])
    ->middleware([
        'web',
        PreventAccessFromCentralDomains::class, // Debe ir ANTES de inicializar
        InitializeTenancyByDomain::class,
        EnsureTenantHasPlanForApp::class,
        EnsureTenantActive::class,
    ])
    ->group(base_path('routes/app.php'));

// Rutas públicas QR (sin auth, sin tenant-active check)
Route::domain('{tenant}.dev-app.cl')
    ->where(['tenant' => '^(?!sas$|www$|api$|test$|staging$|localhost$|127$)[a-z0-9\-]+'])
    ->middleware([
        'web',
        PreventAccessFromCentralDomains::class, // Debe ir ANTES de inicializar
        InitializeTenancyByDomain::class,
        EnsureTenantHasPlan::class,
    ])
    ->group(base_path('routes/qr-public.php'));
