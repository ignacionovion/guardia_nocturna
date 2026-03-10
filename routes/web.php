<?php

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| Rutas del dominio central (sas.dev-app.cl / app.tudominio.cl).
| Aquí vivirá el panel del super admin para gestionar tenants.
| Las rutas de la app operativa están en routes/app.php y se cargan
| dentro del contexto tenant via routes/tenant.php.
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('central.landing');
})->name('central.landing');

// TODO: Fase 1 - Panel Super Admin
// Route::prefix('admin')->middleware('auth:central')->group(function () {
//     Route::get('/', [CentralAdminController::class, 'dashboard'])->name('central.admin.dashboard');
//     Route::resource('bodies', BodyController::class);
//     Route::resource('companies', TenantController::class);
// });
