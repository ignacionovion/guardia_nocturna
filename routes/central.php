<?php

declare(strict_types=1);

use App\Http\Controllers\Central\BillingController;
use App\Http\Controllers\Central\BodyController;
use App\Http\Controllers\Central\CentralAdminController;
use App\Http\Controllers\Central\CentralDashboardController;
use App\Http\Controllers\Central\FinancialDashboardController;
use App\Http\Controllers\Central\AuditController;
use App\Http\Controllers\Central\BackupController;
use App\Http\Controllers\Central\ImpersonationController;
use App\Http\Controllers\Central\PaymentController;
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

Route::get('/', [CentralDashboardController::class, 'index'])->centralNamed('central.dashboard');

$bodiesResource = Route::resource('bodies', BodyController::class);
if ($GLOBALS['__central_named_routes'] ?? true) {
    $bodiesResource->names('central.bodies');
}

$tenantsResource = Route::resource('tenants', TenantController::class)
    ->where(['tenant' => '[a-z0-9\-]+']);
if ($GLOBALS['__central_named_routes'] ?? true) {
    $tenantsResource->names('central.tenants');
}

// Check slug availability (AJAX)
Route::get('check-slug', [TenantController::class, 'checkSlugAvailability'])->centralNamed('central.check-slug');

// Feature flags toggle
Route::post('tenants/{tenant}/features', [TenantController::class, 'updateFeatures'])->centralNamed('central.tenants.features');

// Plan management
Route::post('tenants/{tenant}/change-plan', [TenantController::class, 'changePlan'])->centralNamed('central.tenants.change-plan');

// Manual actions
Route::post('tenants/{tenant}/run-migrations', [TenantController::class, 'runMigrations'])->centralNamed('central.tenants.run-migrations');
Route::post('tenants/{tenant}/run-seed', [TenantController::class, 'runSeed'])->centralNamed('central.tenants.run-seed');
Route::post('tenants/{tenant}/reset-captain-password', [TenantController::class, 'resetCaptainPassword'])->centralNamed('central.tenants.reset-captain-password');
Route::get('tenants/{tenant}/timeline', [TenantController::class, 'timeline'])->centralNamed('central.tenants.timeline');
Route::get('tenants/{tenant}/admin', [TenantController::class, 'admin'])->centralNamed('central.tenants.admin');

// Impersonation
Route::post('tenants/{tenant}/impersonate', [ImpersonationController::class, 'start'])->centralNamed('central.tenants.impersonate');
Route::post('impersonate/stop', [ImpersonationController::class, 'stop'])->centralNamed('central.impersonate.stop');

// Data Explorer
Route::get('tenants/{tenant}/explorer', [TenantDataExplorerController::class, 'index'])->centralNamed('central.tenants.explorer');
Route::get('tenants/{tenant}/explorer/{table}', [TenantDataExplorerController::class, 'showTable'])->centralNamed('central.tenants.explorer.table');
Route::get('tenants/{tenant}/explorer/{table}/record/{id}', [TenantDataExplorerController::class, 'showRecord'])->centralNamed('central.tenants.explorer.record');

// Admin actions
Route::post('tenants/{tenant}/reset-database', [TenantAdminController::class, 'resetDatabase'])->centralNamed('central.tenants.reset-database');
Route::delete('tenants/{tenant}/destroy-completely', [TenantAdminController::class, 'destroyCompletely'])->centralNamed('central.tenants.destroy-completely');

// Pagos (historial / caja base)
$paymentsResource = Route::resource('payments', PaymentController::class)->except(['destroy']);
if ($GLOBALS['__central_named_routes'] ?? true) {
    $paymentsResource->names('central.payments');
}
Route::post('payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->centralNamed('central.payments.mark-paid');
Route::get('financial', [FinancialDashboardController::class, 'index'])->centralNamed('central.financial.index');

// Billing
Route::get('billing', [BillingController::class, 'index'])->centralNamed('central.billing.index');
Route::post('billing', [BillingController::class, 'create'])->centralNamed('central.billing.create');
Route::post('billing/{billing}/mark-paid', [BillingController::class, 'markPaid'])->centralNamed('central.billing.mark-paid');
Route::patch('billing/{billing}/suspend', [BillingController::class, 'suspend'])->centralNamed('central.billing.suspend');
Route::patch('billing/{billing}/extend', [BillingController::class, 'extend'])->centralNamed('central.billing.extend');
Route::patch('billing/{billing}/change-plan', [BillingController::class, 'changePlan'])->centralNamed('central.billing.change-plan');
Route::patch('billing/{billing}/observation', [BillingController::class, 'updateObservation'])->centralNamed('central.billing.observation');

// Plans Management
Route::get('billing/plans', [\App\Http\Controllers\Central\PlanController::class, 'index'])->centralNamed('central.billing.plans.index');
Route::get('billing/plans/create', [\App\Http\Controllers\Central\PlanController::class, 'create'])->centralNamed('central.billing.plans.create');
Route::post('billing/plans', [\App\Http\Controllers\Central\PlanController::class, 'store'])->centralNamed('central.billing.plans.store');
Route::get('billing/plans/{plan}/edit', [\App\Http\Controllers\Central\PlanController::class, 'edit'])->centralNamed('central.billing.plans.edit');
Route::patch('billing/plans/{plan}', [\App\Http\Controllers\Central\PlanController::class, 'update'])->centralNamed('central.billing.plans.update');
Route::delete('billing/plans/{plan}', [\App\Http\Controllers\Central\PlanController::class, 'destroy'])->centralNamed('central.billing.plans.destroy');
Route::patch('billing/plans/{plan}/toggle', [\App\Http\Controllers\Central\PlanController::class, 'toggle'])->centralNamed('central.billing.plans.toggle');

// Backups
Route::get('backups', [BackupController::class, 'index'])->centralNamed('central.backups.index');
Route::post('backups', [BackupController::class, 'store'])->centralNamed('central.backups.store');
Route::get('backups/download', [BackupController::class, 'download'])->centralNamed('central.backups.download');
Route::post('backups/restore', [BackupController::class, 'restore'])->centralNamed('central.backups.restore');
Route::delete('backups', [BackupController::class, 'destroy'])->centralNamed('central.backups.destroy');

// Audit Log
Route::get('audit', [AuditController::class, 'index'])->centralNamed('central.audit.index');

// Administradores SaaS (central_admins)
Route::middleware(['central.super_admin'])->group(function () {
    $adminsResource = Route::resource('admins', CentralAdminController::class)->except(['show']);
    if ($GLOBALS['__central_named_routes'] ?? true) {
        $adminsResource->names('central.admins');
    }
});

