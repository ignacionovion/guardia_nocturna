<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasPlan;
use App\Http\Middleware\EnsureTenantHasPlanForApp;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Todas las rutas de la aplicación operativa viven aquí.
| Se resuelven por subdominio: {tenant}.dev-app.cl
|
| IMPORTANTE: Las rutas públicas QR se cargan PRIMERO para que tengan
| prioridad y no sean interceptadas por rutas autenticadas.
|
*/

// Rutas públicas QR (sin auth, sin tenant-active check)
// DEBE IR PRIMERO para que /qr/forms/* no sea capturado por rutas auth
Route::domain('{tenant}.dev-app.cl')
    ->where(['tenant' => '^(?!sas$|www$|api$|test$|staging$|localhost$|127$)[a-z0-9\-]+'])
    ->middleware([
        'web',
        PreventAccessFromCentralDomains::class, // Debe ir ANTES de inicializar
        'tenant',
        EnsureTenantHasPlan::class,
    ])
    ->group(base_path('routes/qr-public.php'));

// Rutas principales de la app (con auth y tenant-active check)
Route::domain('{tenant}.dev-app.cl')
    ->where(['tenant' => '^(?!sas$|www$|api$|test$|staging$|localhost$|127$)[a-z0-9\-]+'])
    ->middleware([
        'web',
        PreventAccessFromCentralDomains::class, // Debe ir ANTES de inicializar
        'tenant',
        EnsureTenantHasPlanForApp::class,
        'activo',
    ])
    ->group(base_path('routes/app.php'));
