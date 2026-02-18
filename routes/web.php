<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TableroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\AdminCalendarController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\CleaningWebController;
use App\Http\Controllers\BomberoController;
use App\Http\Controllers\Admin\SystemAdminController;
use App\Http\Controllers\AsignacionCamaController;
use App\Http\Controllers\NovedadController;
use App\Http\Controllers\Admin\GuardiaArchiveController;

use App\Http\Controllers\Admin\PreventiveEventController;
use App\Http\Controllers\Admin\PlanillaController;
use App\Http\Controllers\PreventivePublicController;
use App\Http\Controllers\InventarioQrController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\PlanillasQrController;
use App\Http\Controllers\Admin\InventarioQrAdminController;
use App\Http\Controllers\Admin\PlanillaQrFijoController;
use App\Http\Controllers\Admin\InventarioImportController;

Route::get('/preventivas/{token}', [PreventivePublicController::class, 'show'])->name('preventivas.public.show');
Route::post('/preventivas/{token}/confirmar', [PreventivePublicController::class, 'confirm'])->name('preventivas.public.confirm');

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
Route::post('/planillas/qr/{token}/guardar', [PlanillasQrController::class, 'store'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('planillas.qr.store');

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/media/{path}', function (string $path) {
    $path = str_replace(['%2F', '%2f'], '/', $path);
    $path = rawurldecode($path);

    if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
        abort(404);
    }

    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    if (!$disk->exists($path)) {
        abort(404);
    }

    $stream = $disk->readStream($path);
    if ($stream === false) {
        abort(404);
    }

    $mime = $disk->mimeType($path) ?: 'application/octet-stream';

    return response()->stream(function () use ($stream) {
        fpassthru($stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
    }, 200, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('media');

// Rutas Protegidas (Dashboard)
Route::middleware('auth')->group(function () {
    Route::get('/', [TableroController::class, 'index'])->name('dashboard');
    Route::get('/camas', [TableroController::class, 'camas'])->name('camas');
    Route::get('/guardia', [GuardiaController::class, 'index'])->name('guardia');
    Route::get('/guardia/now', [GuardiaController::class, 'now'])->name('guardia.now');
    Route::get('/guardia/now/data', [GuardiaController::class, 'nowData'])->name('guardia.now.data');

    Route::get('/inventario', function () {
        return redirect()->route('inventario.dashboard');
    })->name('inventario.index');

    Route::middleware('inventory_access')->group(function () {
        Route::get('/inventario/panel', [InventarioController::class, 'index'])->name('inventario.dashboard');
        Route::get('/inventario/retiro/acceso', [InventarioController::class, 'retiroAccess'])->name('inventario.retiro.access');
        Route::get('/inventario/retiro/identificar', [InventarioController::class, 'identificarForm'])->name('inventario.retiro.identificar.form');
        Route::post('/inventario/retiro/identificar', [InventarioController::class, 'identificarStore'])->name('inventario.retiro.identificar.store');
        Route::get('/inventario/retiro', [InventarioController::class, 'retiroForm'])->name('inventario.retiro.form');
        Route::post('/inventario/retiro', [InventarioController::class, 'retiroStore'])->name('inventario.retiro.store');

        Route::get('/inventario/config', [InventarioController::class, 'configForm'])->name('inventario.config.form');
        Route::post('/inventario/config/bodega', [InventarioController::class, 'bodegaStore'])->name('inventario.config.bodega.store');
        Route::post('/inventario/config/items', [InventarioController::class, 'itemStore'])->name('inventario.config.items.store');
        Route::post('/inventario/config/stock/ingreso', [InventarioController::class, 'stockIngresoStore'])->name('inventario.config.stock.ingreso.store');
        Route::delete('/inventario/config/items/{itemId}', [InventarioController::class, 'itemDestroy'])->name('inventario.config.items.destroy');

        Route::get('/inventario/movimientos', [InventarioController::class, 'movimientosIndex'])->name('inventario.movimientos.index');

        Route::get('/inventario/qr', [InventarioQrAdminController::class, 'show'])->name('inventario.qr.admin');
        Route::get('/inventario/qr/imprimir', [InventarioQrAdminController::class, 'print'])->name('inventario.qr.print');
        Route::post('/inventario/qr/regenerar', [InventarioQrAdminController::class, 'regenerar'])->name('inventario.qr.regenerar');

        Route::get('/inventario/importar', [InventarioImportController::class, 'importForm'])->name('inventario.import.form');
        Route::post('/inventario/importar/upload', [InventarioImportController::class, 'uploadImport'])->name('inventario.import.upload');
        Route::post('/inventario/importar/process', [InventarioImportController::class, 'processImport'])->name('inventario.import.process');
    });

    Route::get('/kiosk/ping', [TableroController::class, 'kioskPing'])->name('kiosk.ping');

    Route::get('/guardia/snapshot', [TableroController::class, 'guardiaSnapshot'])->name('guardia.snapshot');

    Route::get('/aseo', [CleaningWebController::class, 'index'])->name('guardia.aseo');
    Route::post('/aseo', [CleaningWebController::class, 'store'])->name('guardia.aseo.store');

    // Rutas operativas de Guardia
    Route::post('/guardia', [GuardiaController::class, 'start'])->name('guardia.start');
    Route::post('/guardia/{id}/close', [GuardiaController::class, 'close'])->name('guardia.close');
    Route::post('/guardia/{id}/add-user', [GuardiaController::class, 'addUser'])->name('guardia.add_user');
    Route::post('/guardia/{shiftId}/remove-user/{userId}', [GuardiaController::class, 'removeUser'])->name('guardia.remove_user');

    // Rutas de Gestión de Camas
    Route::post('/camas/asignar', [AsignacionCamaController::class, 'store'])->name('beds.assign');
    Route::put('/camas/liberar/{id}', [AsignacionCamaController::class, 'update'])->name('beds.release');
    Route::put('/camas/{bed}/mantencion', [AsignacionCamaController::class, 'markMaintenance'])->name('beds.maintenance');
    Route::put('/camas/{bed}/habilitar', [AsignacionCamaController::class, 'markAvailable'])->name('beds.available');

    // Rutas Admin - Guardias
    Route::post('/admin/guardias', [AdministradorController::class, 'storeGuardia'])->name('admin.guardias.store');
    Route::get('/admin/guardias/{id}/edit', [AdministradorController::class, 'editGuardia'])->name('admin.guardias.edit');
    Route::put('/admin/guardias/{id}', [AdministradorController::class, 'updateGuardia'])->name('admin.guardias.update');
    Route::delete('/admin/guardias/{id}', [AdministradorController::class, 'destroyGuardia'])->name('admin.guardias.destroy');
    Route::post('/admin/guardias/{id}/activate-week', [AdministradorController::class, 'activateWeek'])->name('admin.guardias.activate_week');
    Route::post('/admin/guardias/{guardia}/bomberos/{bombero}/confirm', [AdministradorController::class, 'confirmBombero'])->name('admin.guardias.bomberos.confirm');
    Route::post('/admin/guardias/{id}/bulk-update', [AdministradorController::class, 'bulkUpdateGuardia'])->name('admin.guardias.bulk_update');

    Route::get('/admin/guardias/{guardia}/history', [App\Http\Controllers\Admin\GuardiaArchiveController::class, 'index'])->name('admin.guardias.history.index');
    Route::get('/admin/guardias/{guardia}/history/{archive}', [App\Http\Controllers\Admin\GuardiaArchiveController::class, 'show'])->name('admin.guardias.history.show');

    // Rutas Admin - Voluntarios (Gestión Completa)
    Route::get('/admin/volunteers/import', [BomberoController::class, 'importForm'])->name('admin.volunteers.import');
    // Rutas para carga con progreso
    Route::post('/admin/volunteers/import/upload', [BomberoController::class, 'uploadImport'])->name('admin.volunteers.import.upload');
    Route::post('/admin/volunteers/import/process', [BomberoController::class, 'processImport'])->name('admin.volunteers.import.process');
    
    Route::post('/admin/volunteers/import', [BomberoController::class, 'import'])->name('admin.volunteers.import.post'); // Fallback o legacy
    
    // Ruta para eliminación masiva
    Route::delete('/admin/volunteers/bulk-destroy', [BomberoController::class, 'bulkDestroy'])->name('admin.volunteers.bulk_destroy');

    Route::delete('/admin/volunteers/purge', [BomberoController::class, 'purgeAll'])->name('admin.volunteers.purge');

    Route::delete('/admin/volunteers/{volunteer}/photo', [BomberoController::class, 'destroyPhoto'])->name('admin.volunteers.photo.destroy');
    
    Route::resource('admin/volunteers', BomberoController::class, ['as' => 'admin']);

    // Rutas Admin - Bomberos (Legacy/Guardias specific)
    Route::get('/admin/guardias', [AdministradorController::class, 'index'])->name('admin.guardias');
    Route::get('/admin/dotaciones', [AdministradorController::class, 'dotaciones'])->name('admin.dotaciones');
    Route::post('/admin/guardias/assign', [AdministradorController::class, 'assignBombero'])->name('admin.guardias.assign');
    Route::match(['get', 'post', 'delete'], '/admin/guardias/unassign', [AdministradorController::class, 'unassignBombero'])->name('admin.guardias.unassign');
    Route::post('/admin/guardias/refuerzo', [AdministradorController::class, 'assignRefuerzo'])->name('admin.guardias.refuerzo');
    Route::post('/admin/guardias/refuerzo/remove', [AdministradorController::class, 'removeRefuerzo'])->name('admin.guardias.refuerzo.remove');
    Route::post('/admin/guardias/replacement', [AdministradorController::class, 'assignReplacement'])->name('admin.guardias.replacement'); // Nueva ruta
    Route::post('/admin/guardias/replacement/{replacement}/undo', [AdministradorController::class, 'undoReplacement'])->name('admin.guardias.replacement.undo');
    Route::post('/admin/guardias/{guardia}/replacements/cleanup', [AdministradorController::class, 'cleanupReplacements'])->name('admin.guardias.replacements.cleanup');
    Route::post('/admin/bomberos', [BomberoController::class, 'store'])->name('admin.bomberos.store');
    
    // Editar y Eliminar Bomberos
    Route::get('/admin/bomberos/{id}/edit', [BomberoController::class, 'edit'])->name('admin.bomberos.edit');
    Route::put('/admin/bomberos/{id}', [BomberoController::class, 'update'])->name('admin.bomberos.update');
    Route::delete('/admin/bomberos/{id}', [BomberoController::class, 'destroy'])->name('admin.bomberos.destroy');
    Route::post('/admin/bomberos/{id}/toggle-titular', [AdministradorController::class, 'toggleTitular'])->name('admin.bomberos.toggle_titular'); // Nueva ruta
    Route::post('/admin/bomberos/{id}/toggle-fuera-servicio', [AdministradorController::class, 'toggleFueraDeServicio'])->name('admin.bomberos.toggle_fuera_servicio');

    // Rutas de Reportes
    Route::get('/admin/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/preventivas', [App\Http\Controllers\ReportController::class, 'preventivas'])->name('admin.reports.preventivas');
    Route::get('/admin/reports/reemplazos', [App\Http\Controllers\ReportController::class, 'replacements'])->name('admin.reports.replacements');
    Route::get('/admin/reports/conductores', [App\Http\Controllers\ReportController::class, 'drivers'])->name('admin.reports.drivers');
    Route::get('/admin/reports/reemplazos/export', [App\Http\Controllers\ReportController::class, 'replacementsExport'])->name('admin.reports.replacements.export');
    Route::get('/admin/reports/reemplazos/print', [App\Http\Controllers\ReportController::class, 'replacementsPrint'])->name('admin.reports.replacements.print');

    // Rutas Admin - Calendario
    Route::get('/admin/calendario', [AdminCalendarController::class, 'index'])->name('admin.calendario');
    Route::post('/admin/calendario/assign-range', [AdminCalendarController::class, 'assignRange'])->name('admin.calendario.assign_range');
    Route::post('/admin/calendario/generate-rotation', [AdminCalendarController::class, 'generateRotation'])->name('admin.calendario.generate_rotation');

    // Rutas de Novedades
    Route::post('/novedades', [NovedadController::class, 'store'])->name('novelties.store_web');

    // Notificaciones in-app
    Route::post('/notifications/read', [App\Http\Controllers\InAppNotificationController::class, 'markRead'])->name('notifications.read');

    // Emergencias (Guardia + Super Admin)
    Route::middleware('emergency_access')->group(function () {
        Route::resource('admin/emergencies', App\Http\Controllers\Admin\EmergencyController::class, ['as' => 'admin']);
    });

    // Rutas Admin - Usuarios del Sistema (solo Super Admin)
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/system', [SystemAdminController::class, 'index'])->name('admin.system.index');
        Route::post('/admin/system/schedule', [SystemAdminController::class, 'saveSchedule'])->name('admin.system.schedule.save');
        Route::post('/admin/system/mail', [SystemAdminController::class, 'saveMailSettings'])->name('admin.system.mail.save');
        Route::post('/admin/system/purge', [SystemAdminController::class, 'purge'])->name('admin.system.purge');
        Route::post('/admin/system/clear-guardias', [SystemAdminController::class, 'clearGuardias'])->name('admin.system.clear_guardias');

        Route::get('/admin/planillas', [PlanillaController::class, 'index'])->name('admin.planillas.index');
        Route::get('/admin/planillas/create', [PlanillaController::class, 'create'])->name('admin.planillas.create');
        Route::post('/admin/planillas', [PlanillaController::class, 'store'])->name('admin.planillas.store');
        Route::get('/admin/planillas/{planilla}', [PlanillaController::class, 'show'])->whereNumber('planilla')->name('admin.planillas.show');
        Route::get('/admin/planillas/{planilla}/edit', [PlanillaController::class, 'edit'])->whereNumber('planilla')->name('admin.planillas.edit');
        Route::put('/admin/planillas/{planilla}', [PlanillaController::class, 'update'])->whereNumber('planilla')->name('admin.planillas.update');
        Route::put('/admin/planillas/{planilla}/estado', [PlanillaController::class, 'updateEstado'])->whereNumber('planilla')->name('admin.planillas.estado.update');
        Route::delete('/admin/planillas/{planilla}', [PlanillaController::class, 'destroy'])->whereNumber('planilla')->name('admin.planillas.destroy');

        Route::get('/admin/planillas/qr-fijo', [PlanillaQrFijoController::class, 'show'])->name('admin.planillas.qr_fijo');
        Route::get('/admin/planillas/qr-fijo/imprimir', [PlanillaQrFijoController::class, 'print'])->name('admin.planillas.qr_fijo.print');
        Route::post('/admin/planillas/qr-fijo/regenerar', [PlanillaQrFijoController::class, 'regenerar'])->name('admin.planillas.qr_fijo.regenerar');

        Route::get('/planillas/qr-fijo', function () {
            return redirect()->route('admin.planillas.qr_fijo');
        })->name('planillas.qr_fijo.alias');

        Route::get('/admin/preventivas', [PreventiveEventController::class, 'index'])->name('admin.preventivas.index');
        Route::get('/admin/preventivas/create', [PreventiveEventController::class, 'create'])->name('admin.preventivas.create');
        Route::post('/admin/preventivas', [PreventiveEventController::class, 'store'])->name('admin.preventivas.store');
        Route::get('/admin/preventivas/{event}', [PreventiveEventController::class, 'show'])->name('admin.preventivas.show');
        Route::post('/admin/preventivas/{event}/templates', [PreventiveEventController::class, 'saveTemplates'])->name('admin.preventivas.templates.save');
        Route::post('/admin/preventivas/{event}/assignments', [PreventiveEventController::class, 'addAssignment'])->name('admin.preventivas.assignments.add');
        Route::delete('/admin/preventivas/{event}/assignments/{assignment}', [PreventiveEventController::class, 'removeAssignment'])->name('admin.preventivas.assignments.remove');
        Route::post('/admin/preventivas/{event}/assignments/{assignment}/attendance/toggle', [PreventiveEventController::class, 'toggleAttendance'])->name('admin.preventivas.assignments.attendance.toggle');
        Route::get('/admin/preventivas/{event}/pdf', [PreventiveEventController::class, 'pdf'])->name('admin.preventivas.pdf');
        Route::get('/admin/preventivas/{event}/qr', [PreventiveEventController::class, 'qr'])->name('admin.preventivas.qr');
        Route::post('/admin/preventivas/{event}/qr/regenerar', [PreventiveEventController::class, 'regenerateQr'])->name('admin.preventivas.qr.regenerate');
        Route::post('/admin/preventivas/{event}/estado/activar', [PreventiveEventController::class, 'activate'])->name('admin.preventivas.status.activate');
        Route::post('/admin/preventivas/{event}/estado/cerrar', [PreventiveEventController::class, 'close'])->name('admin.preventivas.status.close');
        Route::post('/admin/preventivas/{event}/estado/borrador', [PreventiveEventController::class, 'setDraft'])->name('admin.preventivas.status.draft');

        Route::resource('admin/users', App\Http\Controllers\Admin\SystemUserController::class, ['as' => 'admin']);
        Route::resource('admin/roles', App\Http\Controllers\Admin\RoleController::class, ['as' => 'admin']);

        Route::get('/admin/emergency-keys/import', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'importForm'])->name('admin.emergency-keys.import');
        Route::post('/admin/emergency-keys/import/upload', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'uploadImport'])->name('admin.emergency-keys.import.upload');
        Route::post('/admin/emergency-keys/import/process', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'processImport'])->name('admin.emergency-keys.import.process');

        Route::resource('admin/emergency-keys', App\Http\Controllers\Admin\EmergencyKeyController::class, ['as' => 'admin']);
        Route::resource('admin/emergency-units', App\Http\Controllers\Admin\EmergencyUnitController::class, ['as' => 'admin']);
    });
});
