<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\TableroController;
use App\Http\Controllers\GuardiaLiveController;
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
use App\Http\Controllers\Admin\EmergencyKeyController;
use App\Http\Controllers\Admin\EmergencyUnitController;

use App\Http\Controllers\Admin\PreventiveEventController;
use App\Http\Controllers\PreventivePublicController;
use App\Http\Controllers\TurnoDraftController;
use App\Http\Controllers\BedQrController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\FormExecutionController;
use App\Http\Controllers\Admin\TenantSettingsController;

Route::get('/impersonate/callback', [\App\Http\Controllers\ImpersonateCallbackController::class, 'callback'])->name('impersonate.callback');
Route::get('/impersonate/stop', [\App\Http\Controllers\Central\ImpersonationController::class, 'stop'])->name('impersonate.stop');

// Rutas de Autenticación
Route::get('/', [AuthController::class, 'showLoginForm'])->name('tenant.login')->middleware('guest');
Route::post('/', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Broadcasting authentication routes
Illuminate\Support\Facades\Broadcast::routes(['middleware' => ['auth']]);

// Ruta media (ya dentro de contexto tenant via tenant.php)
Route::get('/media/{path}', function (string $tenant, string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(
        Storage::disk('public')->path($path)
    );
})->where('path', '.*')->name('media');

// Rutas Protegidas (Dashboard)
use App\Http\Controllers\NotificationController;

// Phase 4: Modal data endpoints (auth only, no guardia_on_duty restriction)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/bomberos', [BomberoController::class, 'apiIndex'])->name('api.bomberos');
    Route::get('/api/emergency-keys', [EmergencyKeyController::class, 'apiIndex'])->name('api.emergency-keys');
    Route::get('/api/emergency-units', [EmergencyUnitController::class, 'apiIndex'])->name('api.emergency-units');
});

// ================================
// OPERACIÓN DE GUARDIA (EN VIVO)
// ================================
Route::middleware(['auth', 'guardia_on_duty', \App\Http\Middleware\ExpireReplacements::class])->group(function () {

    // Dashboard operativo
    Route::get('/dashboard', [TableroController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-live', [GuardiaLiveController::class, 'index'])->name('dashboard.live');

    // Guardia en vivo API
    Route::get('/api/guardia-live/state', [GuardiaLiveController::class, 'state'])->name('guardia.live.state');
    Route::get('/api/guardia-live/emergencies', [GuardiaLiveController::class, 'emergencies'])->name('guardia.live.emergencies');
    Route::get('/api/guardia-live/cleaning-assignments', [GuardiaLiveController::class, 'cleaningAssignments'])->name('guardia.live.cleaning_assignments');

    // Camas (operación)
    Route::get('/camas', [TableroController::class, 'camas'])->name('camas');
    Route::post('/camas/asignar', [AsignacionCamaController::class, 'store'])->name('beds.assign');
    Route::post('/camas/liberar/{id}', [AsignacionCamaController::class, 'update'])->name('beds.release');

    // Guardia
    Route::get('/guardia', [GuardiaController::class, 'index'])->name('guardia');
    Route::post('/guardia', [GuardiaController::class, 'start'])->name('guardia.start');
    Route::post('/guardia/{id}/close', [GuardiaController::class, 'close'])->name('guardia.close');

    // NOW (pantalla en vivo)
    Route::get('/now', [GuardiaController::class, 'now'])->name('guardia.now');
    Route::get('/now/data', [GuardiaController::class, 'nowData'])->name('guardia.now.data');
    Route::get('/now/snapshot/pdf', [GuardiaController::class, 'nowSnapshotPdf'])->name('guardia.now.snapshot.pdf');
    Route::post('/now/snapshot/email', [GuardiaController::class, 'nowSnapshotEmail'])->name('guardia.now.snapshot.email');

    // Notificaciones operativas
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [InAppNotificationController::class, 'markRead'])->name('notifications.read');

});

Route::middleware(['auth'])->group(function () {
    Route::view('/guardia/fuera-de-servicio', 'guardia.off_duty')->name('guardia.off_duty');
    
    // Bed management API (auth only, no guardia_on_duty restriction)
    Route::get('/api/beds', [\App\Http\Controllers\BedManagementController::class, 'index'])->name('api.beds.index');
    Route::get('/api/beds/available-firefighters', [\App\Http\Controllers\BedManagementController::class, 'availableFirefighters'])->name('api.beds.available_firefighters');
    Route::post('/api/beds/assign', [\App\Http\Controllers\BedManagementController::class, 'assign'])->name('api.beds.assign');
    Route::post('/api/beds/release', [\App\Http\Controllers\BedManagementController::class, 'release'])->name('api.beds.release');
    
    Route::get('/kiosk/ping', [TableroController::class, 'kioskPing'])->name('kiosk.ping');

    Route::get('/guardia/snapshot', [TableroController::class, 'guardiaSnapshot'])->name('guardia.snapshot');

    Route::get('/aseo', [CleaningWebController::class, 'index'])->name('guardia.aseo');
    Route::get('/aseo/modal', [CleaningWebController::class, 'modalContent'])->name('guardia.aseo.modal');
    Route::post('/aseo', [CleaningWebController::class, 'store'])->name('guardia.aseo.store');

    // Draft persistente de Turno (Dashboard) - ventana 22:00-07:00
    Route::get('/draft/turno/current', [TurnoDraftController::class, 'current'])->name('draft.turno.current');
    Route::post('/draft/turno/item', [TurnoDraftController::class, 'upsertItem'])->name('draft.turno.item');
    Route::post('/draft/turno/confirm', [TurnoDraftController::class, 'persistConfirmation'])->name('draft.turno.confirm');
    Route::post('/draft/turno/bed', [TurnoDraftController::class, 'assignBed'])->name('draft.turno.bed');
    Route::post('/draft/turno/seed', [TurnoDraftController::class, 'seedItems'])->name('draft.turno.seed');

    // Rutas operativas de Guardia
    Route::post('/guardia', [GuardiaController::class, 'start'])->middleware('plan.limit:guardias')->name('guardia.start');
    Route::post('/guardia/{id}/close', [GuardiaController::class, 'close'])->name('guardia.close');
    Route::post('/guardia/{id}/add-user', [GuardiaController::class, 'addUser'])->name('guardia.add_user');
    Route::post('/guardia/{shiftId}/remove-user/{userId}', [GuardiaController::class, 'removeUser'])->name('guardia.remove_user');

    // DEBUG: Rutas temporales para diagnóstico (ELIMINAR después de verificar)
    Route::get('/debug/camas/contexto', function () {
        return response()->json([
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenant('id'),
            'tenant_key' => tenant()?->getTenantKey(),
            'db_connection' => DB::connection()->getDatabaseName(),
            'bed_connection' => \App\Models\Bed::query()->getConnection()->getDatabaseName(),
            'bed_assignment_connection' => \App\Models\BedAssignment::query()->getConnection()->getDatabaseName(),
            'total_beds' => \App\Models\Bed::count(),
            'total_assignments' => \App\Models\BedAssignment::count(),
        ]);
    });
    
    Route::get('/debug/camas/middleware-check', function (\Illuminate\Http\Request $request) {
        $route = $request->route();
        
        return response()->json([
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenant('id'),
            'db_connection' => DB::connection()->getDatabaseName(),
            'route_name' => $route?->getName(),
            'current_url' => $request->url(),
            'request_host' => $request->getHost(),
            'request_http_host' => $request->getHttpHost(),
            'middleware_stack' => $route?->gatherMiddleware() ?? [],
            'expected_middleware' => [
                'web',
                \App\Http\Middleware\EnsureTenantActive::class,
            ],
            'subdomain_extracted' => explode('.', $request->getHost())[0] ?? null,
        ]);
    });
    
    Route::get('/debug/camas/assignment/{id}', function (\Illuminate\Http\Request $request, $id) {
        $id = $request->route('id') ?? $id;

        DB::enableQueryLog();

        // 1. Inspección del parámetro
        $paramInfo = [
            'raw_value'  => $id,
            'php_type'   => gettype($id),
            'strlen'     => strlen((string) $id),
            'hex'        => bin2hex((string) $id),
            'trimmed'    => trim((string) $id),
            'int_cast'   => (int) $id,
        ];

        // 2. Eloquent queries
        $eloquentExists   = \App\Models\BedAssignment::where('id', $id)->exists();
        $eloquentWhereKey = \App\Models\BedAssignment::whereKey($id)->exists();
        $eloquentFind     = \App\Models\BedAssignment::find($id);
        $eloquentFirst    = \App\Models\BedAssignment::query()->where('id', $id)->first();
        $eloquentAll      = \App\Models\BedAssignment::pluck('id')->toArray();

        // 3. Raw DB queries (bypass Eloquent)
        $rawExists        = DB::table('bed_assignments')->where('id', $id)->exists();
        $rawExistsInt     = DB::table('bed_assignments')->where('id', (int) $id)->exists();
        $rawFirst         = DB::table('bed_assignments')->where('id', $id)->first();

        // 4. Conexión exacta que usa el modelo
        $modelConnection  = \App\Models\BedAssignment::query()->getConnection()->getDatabaseName();
        $modelConnName    = \App\Models\BedAssignment::query()->getConnection()->getName();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        return response()->json([
            'param_info'             => $paramInfo,
            'tenant_initialized'     => tenancy()->initialized,
            'tenant_id'              => tenant('id'),
            'db_default_connection'  => DB::connection()->getDatabaseName(),
            'model_connection_name'  => $modelConnName,
            'model_connection_db'    => $modelConnection,
            'eloquent_exists'        => $eloquentExists,
            'eloquent_wherekey'      => $eloquentWhereKey,
            'eloquent_find'          => $eloquentFind,
            'eloquent_first'         => $eloquentFirst,
            'eloquent_all_ids'       => $eloquentAll,
            'raw_exists_string'      => $rawExists,
            'raw_exists_int'         => $rawExistsInt,
            'raw_first'              => $rawFirst,
            'query_log'              => $queryLog,
        ]);
    });

    Route::get('/debug/camas/liberar-check/{id}', function (\Illuminate\Http\Request $request, $id) {
        $id = $request->route('id') ?? $id;

        DB::enableQueryLog();

        $paramInfo = [
            'raw_value'  => $id,
            'php_type'   => gettype($id),
            'strlen'     => strlen((string) $id),
            'hex'        => bin2hex((string) $id),
            'int_cast'   => (int) $id,
        ];

        $eloquentExists   = \App\Models\BedAssignment::where('id', $id)->exists();
        $eloquentWhereKey = \App\Models\BedAssignment::whereKey($id)->exists();
        $eloquentFind     = \App\Models\BedAssignment::find($id);
        $rawExists        = DB::table('bed_assignments')->where('id', $id)->exists();
        $rawExistsInt     = DB::table('bed_assignments')->where('id', (int) $id)->exists();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        return response()->json([
            'param_info'             => $paramInfo,
            'tenant_initialized'     => tenancy()->initialized,
            'tenant_id'              => tenant('id'),
            'db_default_connection'  => DB::connection()->getDatabaseName(),
            'model_connection_db'    => \App\Models\BedAssignment::query()->getConnection()->getDatabaseName(),
            'eloquent_exists'        => $eloquentExists,
            'eloquent_wherekey'      => $eloquentWhereKey,
            'eloquent_find'          => $eloquentFind,
            'raw_exists_string'      => $rawExists,
            'raw_exists_int'         => $rawExistsInt,
            'query_log'              => $queryLog,
        ]);
    });

    Route::get('/debug/camas/scan-check/{bedId}', function (\Illuminate\Http\Request $request, $bedId) {
        $bedId = $request->route('bedId') ?? $bedId;

        DB::enableQueryLog();

        $paramInfo = [
            'raw_value'  => $bedId,
            'php_type'   => gettype($bedId),
            'strlen'     => strlen((string) $bedId),
            'hex'        => bin2hex((string) $bedId),
            'int_cast'   => (int) $bedId,
        ];

        $eloquentExists   = \App\Models\Bed::where('id', $bedId)->exists();
        $eloquentWhereKey = \App\Models\Bed::whereKey($bedId)->exists();
        $eloquentFind     = \App\Models\Bed::find($bedId);
        $rawExists        = DB::table('beds')->where('id', $bedId)->exists();
        $rawExistsInt     = DB::table('beds')->where('id', (int) $bedId)->exists();
        $allIds           = \App\Models\Bed::pluck('id')->toArray();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        return response()->json([
            'param_info'             => $paramInfo,
            'tenant_initialized'     => tenancy()->initialized,
            'tenant_id'              => tenant('id'),
            'db_default_connection'  => DB::connection()->getDatabaseName(),
            'model_connection_db'    => \App\Models\Bed::query()->getConnection()->getDatabaseName(),
            'eloquent_exists'        => $eloquentExists,
            'eloquent_wherekey'      => $eloquentWhereKey,
            'eloquent_find'          => $eloquentFind,
            'raw_exists_string'      => $rawExists,
            'raw_exists_int'         => $rawExistsInt,
            'all_bed_ids'            => $allIds,
            'query_log'              => $queryLog,
        ]);
    });

    Route::get('/debug/camas/qr/{bedId}', function (\Illuminate\Http\Request $request, $bedId) {
        $bedId = $request->route('bedId') ?? $bedId;
        $exists = \App\Models\Bed::where('id', $bedId)->exists();
        $bed = $exists ? \App\Models\Bed::find($bedId) : null;
        
        return response()->json([
            'tenant_id' => tenant('id'),
            'db_connection' => DB::connection()->getDatabaseName(),
            'bed_exists' => $exists,
            'bed' => $bed,
            'all_bed_ids' => \App\Models\Bed::pluck('id')->toArray(),
        ]);
    });

    // Rutas de Gestión de Camas
    Route::post('/camas/asignar', [AsignacionCamaController::class, 'store'])->middleware('plan.limit:beds')->name('beds.assign');
    Route::post('/camas/liberar/{id}', [AsignacionCamaController::class, 'update'])->name('beds.release');
    Route::put('/camas/{bed}/mantencion', [AsignacionCamaController::class, 'markMaintenance'])->name('beds.maintenance');
    Route::put('/camas/{bed}/habilitar', [AsignacionCamaController::class, 'markAvailable'])->name('beds.available');
    Route::post('/camas/reporte/email', [AsignacionCamaController::class, 'sendReportEmail'])->name('camas.report.email');

    // Imprimir QR por cama (requiere login)
    Route::get('/camas/{bedId}/qr/imprimir', [BedQrController::class, 'printQr'])->name('camas.qr.print');

    // Rutas Admin - Guardias
    Route::post('/admin/guardias', [AdministradorController::class, 'storeGuardia'])->name('admin.guardias.store');
    Route::get('/admin/guardias/{id}/edit', [AdministradorController::class, 'editGuardia'])->name('admin.guardias.edit');
    Route::put('/admin/guardias/{id}', [AdministradorController::class, 'updateGuardia'])->name('admin.guardias.update');
    Route::delete('/admin/guardias/{id}', [AdministradorController::class, 'destroyGuardia'])->name('admin.guardias.destroy');
    Route::post('/admin/guardias/{id}/activate-week', [AdministradorController::class, 'activateWeek'])->name('admin.guardias.activate_week');
    Route::post('/admin/guardias/{id}/regenerate-credentials', [AdministradorController::class, 'regenerateCredentials'])->name('admin.guardias.regenerate_credentials');
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
    Route::get('/admin/dotaciones', [AdministradorController::class, 'dotaciones'])->middleware('feature:dotaciones')->name('admin.dotaciones');
    Route::post('/admin/guardias/assign', [AdministradorController::class, 'assignBombero'])->name('admin.guardias.assign');
    Route::match(['get', 'post', 'delete'], '/admin/guardias/unassign', [AdministradorController::class, 'unassignBombero'])->name('admin.guardias.unassign');
    Route::post('/admin/guardias/refuerzo', [AdministradorController::class, 'assignRefuerzo'])->name('admin.guardias.refuerzo');
    Route::post('/admin/guardias/refuerzo/remove', [AdministradorController::class, 'removeRefuerzo'])->name('admin.guardias.refuerzo.remove');
    Route::post('/admin/guardias/replacement', [AdministradorController::class, 'assignReplacement'])->name('admin.guardias.replacement'); // Nueva ruta
    Route::post('/admin/guardias/replacement/{replacement}/undo', [AdministradorController::class, 'undoReplacement'])->name('admin.guardias.replacement.undo');
    Route::post('/admin/guardias/{guardia}/replacements/cleanup', [AdministradorController::class, 'cleanupReplacements'])->name('admin.guardias.replacements.cleanup');
    
    // Rutas legacy de bomberos - mantener solo las que no están en el resource
    Route::post('/admin/bomberos/{id}/toggle-titular', [AdministradorController::class, 'toggleTitular'])->name('admin.bomberos.toggle_titular');
    Route::post('/admin/bomberos/{id}/toggle-fuera-servicio', [AdministradorController::class, 'toggleFueraDeServicio'])->name('admin.bomberos.toggle_fuera_servicio');

    // Rutas de Reportes
    Route::middleware(['tenant.feature:reportes'])->group(function () {
    Route::get('/admin/reports', [App\Http\Controllers\ReportController::class, 'attendance'])->name('admin.reports.index');
    Route::get('/admin/reports/attendance', [App\Http\Controllers\ReportController::class, 'attendance'])->name('admin.reports.attendance');
    Route::get('/admin/reports/attendance/export', [App\Http\Controllers\ReportController::class, 'attendanceExport'])->name('admin.reports.attendance.export');
    Route::get('/admin/reports/preventivas', [App\Http\Controllers\ReportController::class, 'preventivas'])->name('admin.reports.preventivas');
    Route::get('/admin/reports/reemplazos', [App\Http\Controllers\ReportController::class, 'replacements'])->name('admin.reports.replacements');
    Route::get('/admin/reports/refuerzos', [App\Http\Controllers\ReportController::class, 'refuerzos'])->name('admin.reports.refuerzos');
    Route::get('/admin/reports/refuerzos/export', [App\Http\Controllers\ReportController::class, 'refuerzosExport'])->name('admin.reports.refuerzos.export');
    Route::get('/admin/reports/conductores', [App\Http\Controllers\ReportController::class, 'drivers'])->name('admin.reports.drivers');
    Route::get('/admin/reports/conductores/export', [App\Http\Controllers\ReportController::class, 'driversExport'])->name('admin.reports.drivers.export');
    Route::get('/admin/reports/emergencias', [App\Http\Controllers\ReportController::class, 'emergencies'])->name('admin.reports.emergencias');
    Route::get('/admin/reports/emergencias/export', [App\Http\Controllers\ReportController::class, 'emergenciesExport'])->name('admin.reports.emergencias.export');
    Route::get('/admin/reports/reemplazos/export', [App\Http\Controllers\ReportController::class, 'replacementsExport'])->name('admin.reports.replacements.export');
    Route::get('/admin/reports/reemplazos/print', [App\Http\Controllers\ReportController::class, 'replacementsPrint'])->name('admin.reports.replacements.print');
    });

    // Rutas Admin - Calendario
    Route::middleware(['feature:calendario'])->group(function () {
        Route::get('/admin/calendario', [AdminCalendarController::class, 'index'])->name('admin.calendario');
        Route::post('/admin/calendario/assign-range', [AdminCalendarController::class, 'assignRange'])->name('admin.calendario.assign_range');
        Route::post('/admin/calendario/generate-rotation', [AdminCalendarController::class, 'generateRotation'])->name('admin.calendario.generate_rotation');
    });

    // Rutas Admin - Camas 2.0 (CRUD completo)
    Route::prefix('admin/beds')->name('admin.beds.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BedController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\BedController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\BedController::class, 'store'])->name('store');
        Route::get('/{bed}/edit', [\App\Http\Controllers\BedController::class, 'edit'])->name('edit');
        Route::put('/{bed}', [\App\Http\Controllers\BedController::class, 'update'])->name('update');
        Route::get('/{bed}/qr', [\App\Http\Controllers\BedController::class, 'showQr'])->name('qr');
        Route::get('/{bed}/qr/print', [\App\Http\Controllers\BedController::class, 'printQr'])->name('qr.print');
        
        // Cambio rápido de estado (disponible / mantenimiento / deshabilitada)
        Route::post('/{bed}/status', [\App\Http\Controllers\BedController::class, 'changeStatus'])->name('status');

        // Rutas de asignación y liberación
        Route::post('/{bed}/assign', [\App\Http\Controllers\BedAssignmentController::class, 'assign'])->name('assign');
        Route::post('/{bed}/release', [\App\Http\Controllers\BedAssignmentController::class, 'release'])->name('release');
        Route::get('/{bed}/history', [\App\Http\Controllers\BedAssignmentController::class, 'history'])->name('history');
        
        // API para obtener voluntarios disponibles
        Route::get('/api/volunteers', [\App\Http\Controllers\BedAssignmentController::class, 'getAvailableVolunteers'])->name('api.volunteers');
    });

    // Rutas de Novedades
    Route::post('/novedades', [NovedadController::class, 'store'])->name('novelties.store_web');
    Route::delete('/novedades/{id}', [NovedadController::class, 'destroy'])->name('novelties.destroy');

    // Notificaciones in-app
    Route::post('/notifications/read', [App\Http\Controllers\InAppNotificationController::class, 'markRead'])->name('notifications.read');

    // Emergencias (Guardia + Super Admin)
    Route::middleware(['tenant.feature:emergencias', 'emergency_access'])->group(function () {
        Route::get('admin/emergencies/modal', [App\Http\Controllers\Admin\EmergencyController::class, 'modalContent'])->name('admin.emergencies.modal');
        Route::get('admin/emergencies/create/modal', [App\Http\Controllers\Admin\EmergencyController::class, 'createModalContent'])->name('admin.emergencies.create.modal');
        Route::resource('admin/emergencies', App\Http\Controllers\Admin\EmergencyController::class, ['as' => 'admin']);
    });

    // Rutas Admin - Usuarios del Sistema (solo Super Admin)
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/tenant-settings', [TenantSettingsController::class, 'index'])->name('admin.tenant-settings.index');
        Route::put('/admin/tenant-settings', [TenantSettingsController::class, 'update'])->name('admin.tenant-settings.update');

        // Rutas Admin - Branding Personalizado (Marca Personalizada addon)
        Route::get('/admin/branding', [App\Http\Controllers\Admin\BrandingController::class, 'index'])->name('admin.branding.index');
        Route::post('/admin/branding', [App\Http\Controllers\Admin\BrandingController::class, 'store'])->name('admin.branding.store');
        Route::post('/admin/branding/logo', [App\Http\Controllers\Admin\BrandingController::class, 'uploadLogo'])->name('admin.branding.upload-logo');
        Route::post('/admin/branding/favicon', [App\Http\Controllers\Admin\BrandingController::class, 'uploadFavicon'])->name('admin.branding.upload-favicon');
        Route::delete('/admin/branding/logo', [App\Http\Controllers\Admin\BrandingController::class, 'removeLogo'])->name('admin.branding.remove-logo');
        Route::delete('/admin/branding/favicon', [App\Http\Controllers\Admin\BrandingController::class, 'removeFavicon'])->name('admin.branding.remove-favicon');

        Route::get('/admin/system', [SystemAdminController::class, 'index'])->name('admin.system.index');
        Route::post('/admin/system/schedule', [SystemAdminController::class, 'saveSchedule'])->name('admin.system.schedule.save');
        Route::post('/admin/system/mail', [SystemAdminController::class, 'saveMailSettings'])->name('admin.system.mail.save');
        Route::post('/admin/system/purge', [SystemAdminController::class, 'purge'])->name('admin.system.purge');
        Route::post('/admin/system/clear-guardias', [SystemAdminController::class, 'clearGuardias'])->name('admin.system.clear_guardias');

        Route::middleware(['tenant.feature:preventiva', 'preventivas_admin'])->group(function () {
            Route::get('/admin/preventivas', [PreventiveEventController::class, 'index'])->name('admin.preventivas.index');
            Route::get('/admin/preventivas/create', [PreventiveEventController::class, 'create'])->name('admin.preventivas.create');
            Route::post('/admin/preventivas', [PreventiveEventController::class, 'store'])->name('admin.preventivas.store');
            Route::get('/admin/preventivas/{event}', [PreventiveEventController::class, 'show'])->name('admin.preventivas.show');
            Route::get('/admin/preventivas/{event}/reporte', [PreventiveEventController::class, 'report'])->name('admin.preventivas.report');
            Route::get('/admin/preventivas/{event}/reporte/excel', [PreventiveEventController::class, 'reportExcel'])->name('admin.preventivas.report.excel');
            Route::get('/admin/preventivas/{event}/reporte/pdf', [PreventiveEventController::class, 'reportPdf'])->name('admin.preventivas.report.pdf');
            Route::post('/admin/preventivas/{event}/templates', [PreventiveEventController::class, 'saveTemplates'])->name('admin.preventivas.templates.save');
            Route::post('/admin/preventivas/{event}/assignments', [PreventiveEventController::class, 'addAssignment'])->name('admin.preventivas.assignments.add');
            Route::delete('/admin/preventivas/{event}/assignments/{assignment}', [PreventiveEventController::class, 'removeAssignment'])->name('admin.preventivas.assignments.remove');
            Route::post('/admin/preventivas/{event}/assignments/{assignment}/attendance/toggle', [PreventiveEventController::class, 'toggleAttendance'])->name('admin.preventivas.assignments.attendance.toggle');
            Route::get('/admin/preventivas/{event}/pdf', [PreventiveEventController::class, 'pdf'])->name('admin.preventivas.pdf');
            Route::get('/admin/preventivas/{event}/qr', [PreventiveEventController::class, 'qr'])->name('admin.preventivas.qr');
            Route::get('/admin/preventivas/{event}/qr/print', [PreventiveEventController::class, 'qrPrint'])->name('admin.preventivas.qr.print');
            Route::post('/admin/preventivas/{event}/qr/regenerar', [PreventiveEventController::class, 'regenerateQr'])->name('admin.preventivas.qr.regenerate');
            Route::post('/admin/preventivas/{event}/estado/activar', [PreventiveEventController::class, 'activate'])->name('admin.preventivas.status.activate');
            Route::post('/admin/preventivas/{event}/estado/cerrar', [PreventiveEventController::class, 'close'])->name('admin.preventivas.status.close');
            Route::post('/admin/preventivas/{event}/estado/borrador', [PreventiveEventController::class, 'setDraft'])->name('admin.preventivas.status.draft');
            Route::delete('/admin/preventivas/{event}', [PreventiveEventController::class, 'destroy'])->name('admin.preventivas.destroy');
        });

        Route::post('admin/users', [App\Http\Controllers\Admin\SystemUserController::class, 'store'])->middleware('max_users')->name('admin.users.store');
        Route::post('admin/users/{id}/regenerate-password', [App\Http\Controllers\Admin\SystemUserController::class, 'regeneratePassword'])->name('admin.users.regenerate-password');
        Route::resource('admin/users', App\Http\Controllers\Admin\SystemUserController::class, ['as' => 'admin'])->except(['store']);
        Route::resource('admin/roles', App\Http\Controllers\Admin\RoleController::class, ['as' => 'admin']);

        Route::get('/admin/emergency-keys/import', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'importForm'])->name('admin.emergency-keys.import');
        Route::post('/admin/emergency-keys/import/upload', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'uploadImport'])->name('admin.emergency-keys.import.upload');
        Route::post('/admin/emergency-keys/import/process', [App\Http\Controllers\Admin\EmergencyKeyController::class, 'processImport'])->name('admin.emergency-keys.import.process');

        Route::resource('admin/emergency-keys', App\Http\Controllers\Admin\EmergencyKeyController::class, ['as' => 'admin']);
        Route::resource('admin/emergency-units', App\Http\Controllers\Admin\EmergencyUnitController::class, ['as' => 'admin']);
        Route::post('admin/emergency-units/{id}/toggle-status', [App\Http\Controllers\Admin\EmergencyUnitController::class, 'toggleStatus'])->name('admin.emergency-units.toggle-status');

        // Formularios Dinámicos
        Route::middleware(['auth'])->group(function () {
            // Builder - Solo capitanes y admins
            Route::middleware(['role:capitan,super_admin,capitania'])->prefix('admin/forms')->group(function () {
                Route::get('/builder', [FormBuilderController::class, 'index'])->name('forms.builder.index');
                Route::get('/builder/create', [FormBuilderController::class, 'create'])->name('forms.builder.create');
                Route::post('/builder', [FormBuilderController::class, 'store'])->name('forms.builder.store');
                Route::get('/builder/{template}/edit', [FormBuilderController::class, 'edit'])->name('forms.builder.edit');
                Route::put('/builder/{template}', [FormBuilderController::class, 'update'])->name('forms.builder.update');
                Route::delete('/builder/{template}', [FormBuilderController::class, 'destroy'])->name('forms.builder.destroy');
                Route::post('/builder/{template}/duplicate', [FormBuilderController::class, 'duplicate'])->name('forms.builder.duplicate');
                Route::post('/builder/{template}/toggle', [FormBuilderController::class, 'toggleActive'])->name('forms.builder.toggle');
            });

            // Execution - Todos los usuarios autenticados
            Route::prefix('forms')->group(function () {
                Route::get('/', [FormExecutionController::class, 'index'])
                    ->name('forms.execution.index');

                Route::get('/{template}', [FormExecutionController::class, 'show'])
                    ->whereNumber('template')
                    ->name('forms.execution.show');

                Route::post('/{template}', [FormExecutionController::class, 'submit'])
                    ->whereNumber('template')
                    ->name('forms.execution.submit');

                Route::post('/{template}/draft', [FormExecutionController::class, 'draft'])
                    ->whereNumber('template')
                    ->name('forms.execution.draft');

                Route::get('/submissions/{submission}/edit', [FormExecutionController::class, 'edit'])
                    ->name('forms.execution.edit');

                Route::put('/submissions/{submission}', [FormExecutionController::class, 'update'])
                    ->name('forms.execution.update');

                Route::delete('/submissions/{submission}', [FormExecutionController::class, 'destroy'])
                    ->name('forms.execution.destroy');

                Route::post('/submissions/{submission}/finalize', [FormExecutionController::class, 'finalize'])
                    ->name('forms.execution.finalize');
            });
        });
    });
});
