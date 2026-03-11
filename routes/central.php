<?php

declare(strict_types=1);

use App\Http\Controllers\Central\BodyController;
use App\Http\Controllers\Central\CentralAuthController;
use App\Http\Controllers\Central\CentralDashboardController;
use App\Http\Controllers\Central\AuditController;
use App\Http\Controllers\Central\BackupController;
use App\Http\Controllers\Central\ImpersonationController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantDataExplorerController;
use App\Http\Controllers\Central\TenantAdminController;
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

Route::get('/login', [CentralAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CentralAuthController::class, 'login']);
Route::post('/logout', [CentralAuthController::class, 'logout'])->name('central.logout');

// Protected
Route::middleware('auth:central')->prefix('admin')->group(function () {
    Route::get('/', [CentralDashboardController::class, 'index'])->name('central.dashboard');

    Route::resource('bodies', BodyController::class)->names('central.bodies');
    Route::resource('tenants', TenantController::class)->names('central.tenants');

    // Feature flags toggle
    Route::post('tenants/{tenant}/features', [TenantController::class, 'updateFeatures'])->name('central.tenants.features');

    // Plan management
    Route::post('tenants/{tenant}/change-plan', [TenantController::class, 'changePlan'])->name('central.tenants.change-plan');

    // Manual actions
    Route::post('tenants/{tenant}/run-migrations', [TenantController::class, 'runMigrations'])->name('central.tenants.run-migrations');
    Route::post('tenants/{tenant}/run-seed', [TenantController::class, 'runSeed'])->name('central.tenants.run-seed');
    Route::get('tenants/{tenant}/timeline', [TenantController::class, 'timeline'])->name('central.tenants.timeline');
    Route::post('tenants/{tenant}/impersonate', [ImpersonationController::class, 'start'])->name('central.tenants.impersonate');

    // AJAX: Check slug availability
    Route::get('check-slug', [TenantController::class, 'checkSlugAvailability'])->name('central.check-slug');

    // Administración Técnica
    Route::get('tenants/{tenant}/admin', [TenantController::class, 'admin'])->name('central.tenants.admin');
    Route::get('tenants/{tenant}/explorer', [TenantDataExplorerController::class, 'index'])->name('central.tenants.explorer.index');
    Route::get('tenants/{tenant}/explorer/{table}', [TenantDataExplorerController::class, 'table'])->name('central.tenants.explorer.table');
    Route::get('tenants/{tenant}/explorer/{table}/record/{id}', [TenantDataExplorerController::class, 'showRecord'])->name('central.tenants.explorer.record');
    Route::post('tenants/{tenant}/reset-database', [TenantAdminController::class, 'resetDatabase'])->name('central.tenants.reset-database');
    Route::delete('tenants/{tenant}/destroy-completely', [TenantAdminController::class, 'destroyCompletely'])->name('central.tenants.destroy-completely');

    // Audit log
    Route::get('audit', [AuditController::class, 'index'])->name('central.audit.index');

    // Backups
    Route::get('backups', [BackupController::class, 'index'])->name('central.backups.index');
    Route::post('backups', [BackupController::class, 'store'])->name('central.backups.store');
    Route::get('backups/download', [BackupController::class, 'download'])->name('central.backups.download');
    Route::post('backups/restore', [BackupController::class, 'restore'])->name('central.backups.restore');
    Route::delete('backups', [BackupController::class, 'destroy'])->name('central.backups.destroy');
});
