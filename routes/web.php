<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Central routes for specific domains
|
*/

$centralDomains = config('tenancy.central_domains', []);

// Cada dominio central debe tener las mismas rutas; los nombres de ruta solo
// se registran una vez (primer dominio) para evitar colisiones en Laravel.
foreach ($centralDomains as $centralDomainIndex => $domain) {
    Route::domain($domain)->group(function () use ($centralDomainIndex) {

        // Rutas públicas centrales
        Route::get('/', function () {
            if (auth('central')->check()) {
                return redirect()->route('central.dashboard');
            }

            return redirect('/login');
        })->name($centralDomainIndex === 0 ? 'central.landing' : null);

        Route::get('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'showLogin'])
            ->name($centralDomainIndex === 0 ? 'central.login' : null);
        Route::post('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'login']);
        Route::post('/logout', [\App\Http\Controllers\Central\CentralAuthController::class, 'logout'])
            ->name($centralDomainIndex === 0 ? 'central.logout' : null);

        // Rutas protegidas centrales (prefijo /admin)
        Route::middleware(['web', 'auth:central', 'central.active'])
            ->prefix('admin')
            ->group(function () use ($centralDomainIndex) {
                $GLOBALS['__central_named_routes'] = ($centralDomainIndex === 0);
                require base_path('routes/central.php');
            });
    });
}
