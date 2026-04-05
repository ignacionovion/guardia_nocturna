<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrBedController;
use App\Http\Controllers\BedQrController;
use App\Http\Controllers\PreventivePublicController;
use App\Http\Controllers\FormQrController;

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

// === QR FORMULARIOS ===

Route::prefix('qr/forms')->group(function () {
    Route::get('/test-width', function() { return view('qr.forms.test-width'); })->name('qr.forms.test');
    Route::get('/validate', [FormQrController::class, 'validateRut'])->name('qr.forms.validate');
    Route::post('/validate', [FormQrController::class, 'processRut'])->name('qr.forms.process');
    Route::get('/list', [FormQrController::class, 'listForms'])->name('qr.forms.list');
    Route::get('/{template}', [FormQrController::class, 'show'])->whereNumber('template')->name('qr.forms.show');
    Route::post('/{template}/draft', [FormQrController::class, 'saveDraft'])->whereNumber('template')->name('qr.forms.draft');
    Route::post('/{template}/submit', [FormQrController::class, 'submitForm'])->whereNumber('template')->name('qr.forms.submit');
    Route::get('/success', [FormQrController::class, 'success'])->name('qr.forms.success');
    Route::post('/logout', [FormQrController::class, 'logout'])->name('qr.forms.logout');
});
