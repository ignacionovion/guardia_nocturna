<?php

declare(strict_types=1);

use App\Http\Controllers\Central\BillingController;
use App\Http\Controllers\Central\BodyController;
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
| Central Routes (Super Admin Panel) - Included File
|--------------------------------------------------------------------------
|
| These routes are included from routes/web.php inside a domain-specific
| group with the 'admin' prefix and 'auth:central' middleware.
|
*/

// RUTA DE PRUEBA CENTRAL (temporal)
Route::get('/__central_probe', function () {
    return response()->json([
        'ok' => true,
        'host' => request()->getHost(),
        'route_name' => request()->route()?->getName(),
        'tenancy_initialized' => app()->bound('tenant'),
        'guard' => 'central',
    ]);
})->name('central.probe');

Route::get('/', [CentralDashboardController::class, 'index'])->name('central.dashboard');

Route::resource('bodies', BodyController::class)->names('central.bodies');
Route::resource('tenants', TenantController::class)
    ->where(['tenant' => '[a-z0-9\-]+'])
    ->names('central.tenants');

// Feature flags toggle
Route::post('tenants/{tenant}/features', [TenantController::class, 'updateFeatures'])->name('central.tenants.features');

// Plan management
Route::post('tenants/{tenant}/change-plan', [TenantController::class, 'changePlan'])->name('central.tenants.change-plan');

// Manual actions
Route::post('tenants/{tenant}/run-migrations', [TenantController::class, 'runMigrations'])->name('central.tenants.run-migrations');
Route::post('tenants/{tenant}/run-seed', [TenantController::class, 'runSeed'])->name('central.tenants.run-seed');
Route::get('tenants/{tenant}/timeline', [TenantController::class, 'timeline'])->name('central.tenants.timeline');
Route::get('tenants/{tenant}/admin', [TenantController::class, 'admin'])->name('central.tenants.admin');

// Impersonation
Route::post('tenants/{tenant}/impersonate', [ImpersonationController::class, 'start'])->name('central.tenants.impersonate');
Route::post('impersonate/stop', [ImpersonationController::class, 'stop'])->name('central.impersonate.stop');

// Data Explorer
Route::get('tenants/{tenant}/explorer', [TenantDataExplorerController::class, 'index'])->name('central.tenants.explorer');
Route::get('tenants/{tenant}/explorer/{table}', [TenantDataExplorerController::class, 'showTable'])->name('central.tenants.explorer.table');
Route::get('tenants/{tenant}/explorer/{table}/record/{id}', [TenantDataExplorerController::class, 'showRecord'])->name('central.tenants.explorer.record');

// Admin actions
Route::post('tenants/{tenant}/reset-database', [TenantAdminController::class, 'resetDatabase'])->name('central.tenants.reset-database');
Route::delete('tenants/{tenant}/destroy-completely', [TenantAdminController::class, 'destroyCompletely'])->name('central.tenants.destroy-completely');

// Billing
Route::get('billing', [BillingController::class, 'index'])->name('central.billing.index');
Route::post('billing', [BillingController::class, 'create'])->name('central.billing.create');
Route::post('billing/{billing}/mark-paid', [BillingController::class, 'markAsPaid'])->name('central.billing.mark-paid');
Route::patch('billing/{billing}/observation', [BillingController::class, 'updateObservation'])->name('central.billing.observation');

// Backups
Route::get('backups', [BackupController::class, 'index'])->name('central.backups.index');
Route::post('backups', [BackupController::class, 'store'])->name('central.backups.store');
Route::get('backups/download', [BackupController::class, 'download'])->name('central.backups.download');
Route::post('backups/restore', [BackupController::class, 'restore'])->name('central.backups.restore');
Route::delete('backups', [BackupController::class, 'destroy'])->name('central.backups.destroy');

// Audit Log
Route::get('audit', [AuditController::class, 'index'])->name('central.audit.index');

