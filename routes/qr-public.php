<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrBedController;
use App\Http\Controllers\BedQrController;
use App\Http\Controllers\PreventivePublicController;
use App\Http\Controllers\InventarioQrController;
use App\Http\Controllers\PlanillasQrController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas QR (Tenant Context)
|--------------------------------------------------------------------------
|
| Rutas accesibles sin autenticación para escaneo de QR desde móvil.
| Middleware aplicado: web + InitializeTenancyBySubdomain
| NO tiene: auth, PreventAccessFromCentralDomains, EnsureTenantActive
|
*/

// === QR CAMAS ===

// Ruta pública QR Camas 2.0 (por token)
Route::get('/qr/{token}', [QrBedController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{32}')
    ->name('qr.bed.show');

// Rutas QR para Camas (flujo de asignación desde móvil)
Route::get('/camas/scan/{bedId}', [BedQrController::class, 'scanForm'])->name('camas.scan.form');
Route::post('/camas/scan/{bedId}/rut', [BedQrController::class, 'processRut'])->name('camas.scan.rut');
Route::get('/camas/scan/{bedId}/no-guardia', [BedQrController::class, 'notInGuardia'])->name('camas.scan.not_in_guardia');
Route::get('/camas/scan/{bedId}/asignar', [BedQrController::class, 'assignForm'])->name('camas.scan.assign');
Route::post('/camas/scan/{bedId}/asignar', [BedQrController::class, 'assignStore'])->name('camas.scan.assign.store');
Route::get('/camas/scan/{bedId}/exito', [BedQrController::class, 'success'])->name('camas.scan.success');

// === QR PREVENTIVAS ===

Route::get('/preventivas/{token}', [PreventivePublicController::class, 'show'])->name('preventivas.public.show');
Route::post('/preventivas/{token}/confirmar', [PreventivePublicController::class, 'confirm'])->name('preventivas.public.confirm');
Route::get('/preventivas/{token}/identificar', [PreventivePublicController::class, 'identificarForm'])->name('preventivas.public.identificar.form');
Route::post('/preventivas/{token}/identificar', [PreventivePublicController::class, 'identificarStore'])->name('preventivas.public.identificar.store');
Route::post('/preventivas/{token}/rut', [PreventivePublicController::class, 'processRut'])->name('preventivas.public.rut');
Route::get('/preventivas/{token}/tipo-ingreso', [PreventivePublicController::class, 'tipoIngresoForm'])->name('preventivas.public.tipo_ingreso.form');
Route::post('/preventivas/{token}/tipo-ingreso', [PreventivePublicController::class, 'tipoIngresoStore'])->name('preventivas.public.tipo_ingreso.store');

// === QR INVENTARIO ===

Route::get('/inventario/qr/{token}', [InventarioQrController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('inventario.qr.show');
Route::get('/inventario/qr/{token}/confirmar', [InventarioQrController::class, 'confirm'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('inventario.qr.confirm');
Route::get('/inventario/qr/{token}/identificar', [InventarioQrController::class, 'identificarForm'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('inventario.qr.identificar.form');
Route::post('/inventario/qr/{token}/identificar', [InventarioQrController::class, 'identificarStore'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('inventario.qr.identificar.store');
Route::post('/inventario/qr/{token}/retirar', [InventarioQrController::class, 'store'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('inventario.qr.retiro.store');

// === QR PLANILLAS ===

Route::get('/planillas/qr/{token}', [PlanillasQrController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.show');
Route::get('/planillas/qr/{token}/identificar', [PlanillasQrController::class, 'identificarForm'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.identificar.form');
Route::post('/planillas/qr/{token}/identificar', [PlanillasQrController::class, 'identificarStore'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.identificar.store');
Route::get('/planillas/qr/{token}/crear', [PlanillasQrController::class, 'createForm'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.create.form');
Route::get('/planillas/qr/{token}/editar/{planilla}', [PlanillasQrController::class, 'editForm'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->whereNumber('planilla')
    ->name('planillas.qr.edit.form');
Route::post('/planillas/qr/{token}/guardar', [PlanillasQrController::class, 'store'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.store');
Route::put('/planillas/qr/{token}/actualizar/{planilla}', [PlanillasQrController::class, 'update'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->whereNumber('planilla')
    ->name('planillas.qr.update');
