<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCalendarController;
use App\Http\Controllers\GuardiaController;

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas Protegidas (Dashboard)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/camas', [DashboardController::class, 'camas'])->name('camas');
    Route::get('/guardia', [GuardiaController::class, 'index'])->name('guardia');

    // Rutas operativas de Guardia
    Route::post('/guardia', [GuardiaController::class, 'start'])->name('guardia.start');
    Route::post('/guardia/{id}/close', [GuardiaController::class, 'close'])->name('guardia.close');
    Route::post('/guardia/{id}/add-user', [GuardiaController::class, 'addUser'])->name('guardia.add_user');
    Route::post('/guardia/{shiftId}/remove-user/{userId}', [GuardiaController::class, 'removeUser'])->name('guardia.remove_user');

    // Rutas de Gestión de Camas
    Route::post('/camas/asignar', [App\Http\Controllers\BedAssignmentController::class, 'store'])->name('beds.assign');
    Route::put('/camas/liberar/{id}', [App\Http\Controllers\BedAssignmentController::class, 'update'])->name('beds.release');
    Route::put('/camas/{bed}/mantencion', [App\Http\Controllers\BedAssignmentController::class, 'markMaintenance'])->name('beds.maintenance');
    Route::put('/camas/{bed}/habilitar', [App\Http\Controllers\BedAssignmentController::class, 'markAvailable'])->name('beds.available');

    // Rutas Admin - Guardias
    Route::post('/admin/guardias', [AdminController::class, 'storeGuardia'])->name('admin.guardias.store');
    Route::get('/admin/guardias/{id}/edit', [AdminController::class, 'editGuardia'])->name('admin.guardias.edit');
    Route::put('/admin/guardias/{id}', [AdminController::class, 'updateGuardia'])->name('admin.guardias.update');
    Route::delete('/admin/guardias/{id}', [AdminController::class, 'destroyGuardia'])->name('admin.guardias.destroy');
    Route::post('/admin/guardias/{id}/activate-week', [AdminController::class, 'activateWeek'])->name('admin.guardias.activate_week');
    Route::post('/admin/guardias/{id}/bulk-update', [AdminController::class, 'bulkUpdateGuardia'])->name('admin.guardias.bulk_update');

    // Rutas Admin - Voluntarios (Gestión Completa)
    Route::get('/admin/volunteers/import', [App\Http\Controllers\VolunteerController::class, 'importForm'])->name('admin.volunteers.import');
    // Rutas para carga con progreso
    Route::post('/admin/volunteers/import/upload', [App\Http\Controllers\VolunteerController::class, 'uploadImport'])->name('admin.volunteers.import.upload');
    Route::post('/admin/volunteers/import/process', [App\Http\Controllers\VolunteerController::class, 'processImport'])->name('admin.volunteers.import.process');
    
    Route::post('/admin/volunteers/import', [App\Http\Controllers\VolunteerController::class, 'import'])->name('admin.volunteers.import.post'); // Fallback o legacy
    
    // Ruta para eliminación masiva
    Route::delete('/admin/volunteers/bulk-destroy', [App\Http\Controllers\VolunteerController::class, 'bulkDestroy'])->name('admin.volunteers.bulk_destroy');
    
    Route::resource('admin/volunteers', App\Http\Controllers\VolunteerController::class, ['as' => 'admin']);

    // Rutas Admin - Bomberos (Legacy/Guardias specific)
    Route::get('/admin/guardias', [AdminController::class, 'index'])->name('admin.guardias');
    Route::get('/admin/dotaciones', [AdminController::class, 'dotaciones'])->name('admin.dotaciones');
    Route::post('/admin/guardias/assign', [AdminController::class, 'assignBombero'])->name('admin.guardias.assign');
    Route::delete('/admin/guardias/unassign', [AdminController::class, 'unassignBombero'])->name('admin.guardias.unassign');
    Route::post('/admin/guardias/replacement', [AdminController::class, 'assignReplacement'])->name('admin.guardias.replacement'); // Nueva ruta
    Route::post('/admin/bomberos', [AdminController::class, 'storeBombero'])->name('admin.bomberos.store');
    
    // Editar y Eliminar Bomberos
    Route::get('/admin/bomberos/{id}/edit', [AdminController::class, 'editBombero'])->name('admin.bomberos.edit');
    Route::put('/admin/bomberos/{id}', [AdminController::class, 'updateBombero'])->name('admin.bomberos.update');
    Route::delete('/admin/bomberos/{id}', [AdminController::class, 'destroyBombero'])->name('admin.bomberos.destroy');
    Route::post('/admin/bomberos/{id}/toggle-titular', [AdminController::class, 'toggleTitular'])->name('admin.bomberos.toggle_titular'); // Nueva ruta

    // Rutas de Reportes
    Route::get('/admin/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/reemplazos', [App\Http\Controllers\ReportController::class, 'replacements'])->name('admin.reports.replacements');
    Route::get('/admin/reports/reemplazos/export', [App\Http\Controllers\ReportController::class, 'replacementsExport'])->name('admin.reports.replacements.export');
    Route::get('/admin/reports/reemplazos/print', [App\Http\Controllers\ReportController::class, 'replacementsPrint'])->name('admin.reports.replacements.print');

    // Rutas Admin - Calendario
    Route::get('/admin/calendario', [AdminCalendarController::class, 'index'])->name('admin.calendario');
    Route::post('/admin/calendario/assign-range', [AdminCalendarController::class, 'assignRange'])->name('admin.calendario.assign_range');
    Route::post('/admin/calendario/generate-rotation', [AdminCalendarController::class, 'generateRotation'])->name('admin.calendario.generate_rotation');

    // Rutas de Novedades
    Route::post('/novedades', [App\Http\Controllers\NoveltyController::class, 'store'])->name('novelties.store_web');
});
