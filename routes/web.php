<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Central routes for specific domains + tenant routes
|
*/

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        
        // Rutas públicas centrales
        Route::get('/', function () {
            if (\Illuminate\Support\Facades\Auth::guard('central')->check()) {
                return new \Illuminate\Http\RedirectResponse('/admin', 302, ['Location' => '/admin']);
            }
            return view('central.landing');
        })->name('central.landing');

        Route::get('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Central\CentralAuthController::class, 'login']);
        Route::post('/logout', [\App\Http\Controllers\Central\CentralAuthController::class, 'logout'])->name('central.logout');

        // Rutas protegidas centrales (prefijo /admin)
        Route::middleware(['web', 'auth:central'])
            ->prefix('admin')
            ->group(function () {
                require base_path('routes/central.php');
            });

    });
}
