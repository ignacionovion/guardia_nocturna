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

Route::get('/preventivas/{token}', [PreventivePublicController::class, 'show'])->name('preventivas.public.show');
Route::post('/preventivas/{token}/confirmar', [PreventivePublicController::class, 'confirm'])->name('preventivas.public.confirm');

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas Protegidas (Dashboard)
Route::middleware('auth')->group(function () {
    Route::get('/', [TableroController::class, 'index'])->name('dashboard');
    Route::get('/camas', [TableroController::class, 'camas'])->name('camas');
    Route::get('/guardia', [GuardiaController::class, 'index'])->name('guardia');

    Route::get('/kiosk/ping', [TableroController::class, 'kioskPing'])->name('kiosk.ping');

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
        Route::get('/admin/planillas/{planilla}', [PlanillaController::class, 'show'])->name('admin.planillas.show');
        Route::get('/admin/planillas/{planilla}/edit', [PlanillaController::class, 'edit'])->name('admin.planillas.edit');
        Route::put('/admin/planillas/{planilla}', [PlanillaController::class, 'update'])->name('admin.planillas.update');
        Route::put('/admin/planillas/{planilla}/estado', [PlanillaController::class, 'updateEstado'])->name('admin.planillas.estado.update');
        Route::delete('/admin/planillas/{planilla}', [PlanillaController::class, 'destroy'])->name('admin.planillas.destroy');

        Route::get('/admin/preventivas', [PreventiveEventController::class, 'index'])->name('admin.preventivas.index');
        Route::get('/admin/preventivas/create', [PreventiveEventController::class, 'create'])->name('admin.preventivas.create');
        Route::post('/admin/preventivas', [PreventiveEventController::class, 'store'])->name('admin.preventivas.store');
        Route::get('/admin/preventivas/{event}', [PreventiveEventController::class, 'show'])->name('admin.preventivas.show');
        Route::post('/admin/preventivas/{event}/templates', [PreventiveEventController::class, 'saveTemplates'])->name('admin.preventivas.templates.save');
        Route::post('/admin/preventivas/{event}/assignments', [PreventiveEventController::class, 'addAssignment'])->name('admin.preventivas.assignments.add');
        Route::delete('/admin/preventivas/{event}/assignments/{assignment}', [PreventiveEventController::class, 'removeAssignment'])->name('admin.preventivas.assignments.remove');
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
