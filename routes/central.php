<?php

declare(strict_types=1);

use App\Http\Controllers\Central\BodyController;
use App\Http\Controllers\Central\CentralAuthController;
use App\Http\Controllers\Central\CentralDashboardController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes (Super Admin Panel)
|--------------------------------------------------------------------------
|
| Estas rutas corren SOLO en dominios centrales (sas.dev-app.cl, localhost).
| Se registran desde TenancyServiceProvider con Route::domain().
|
*/

// Public
Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::guard('central')->check()) {
        return new \Illuminate\Http\RedirectResponse('/admin', 302, ['Location' => '/admin']);
    }
    return view('central.landing');
})->name('central.landing');

Route::get('/login', [CentralAuthController::class, 'showLogin'])->name('central.login');
Route::post('/login', [CentralAuthController::class, 'login']);
Route::post('/logout', [CentralAuthController::class, 'logout'])->name('central.logout');

// Protected
Route::middleware('auth:central')->prefix('admin')->group(function () {
    Route::get('/', [CentralDashboardController::class, 'index'])->name('central.dashboard');

    Route::resource('bodies', BodyController::class)->names('central.bodies');
    Route::resource('tenants', TenantController::class)->names('central.tenants');
});
