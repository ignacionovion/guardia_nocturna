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

// Registrar rutas centrales para cada dominio exacto (sin parámetros dinámicos)
// Solo registrar nombres de ruta para el primer dominio para evitar duplicación
$firstDomain = true;
foreach ($centralDomains as $domain) {
    Route::domain($domain)->group(function () use (&$firstDomain) {
        
        // Rutas públicas centrales
        Route::get('/', function () {
            if (auth('central')->check()) {
                return redirect()->route('central.dashboard');
            }

            return redirect('/login');
        })->name($firstDomain ? 'central.landing' : null);

        Route::get('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'showLogin'])
            ->name($firstDomain ? 'login' : null);
        Route::post('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'login']);
        Route::post('/logout', [\App\Http\Controllers\Central\CentralAuthController::class, 'logout'])
            ->name($firstDomain ? 'central.logout' : null);

        // Rutas protegidas centrales (prefijo /admin)
        Route::middleware(['web', 'auth:central'])
            ->prefix('admin')
            ->group(function () use (&$firstDomain) {
                // Solo incluir central.php una vez para evitar duplicación de nombres
                if ($firstDomain) {
                    require base_path('routes/central.php');
                }
            });
        
        $firstDomain = false;
    });
}
