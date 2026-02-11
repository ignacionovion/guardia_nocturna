<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CamaController;
use App\Http\Controllers\AsignacionCamaController;
use App\Http\Controllers\EventoPersonalController;
use App\Http\Controllers\TareaAseoController;
use App\Http\Controllers\AsignacionAseoController;
use App\Http\Controllers\NovedadController;
use App\Http\Controllers\RecordatorioController;
use App\Http\Controllers\TurnoController;

use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Usuarios
    Route::get('users/birthdays', [UserController::class, 'birthdays']);
    Route::apiResource('users', UserController::class);

    // Camas
    Route::apiResource('beds', CamaController::class);
    Route::apiResource('bed-assignments', AsignacionCamaController::class);

    // Personal (Remplazos, Permisos, etc)
    Route::apiResource('staff-events', EventoPersonalController::class);

    // Aseo
    Route::apiResource('cleaning-tasks', TareaAseoController::class);
    Route::apiResource('cleaning-assignments', AsignacionAseoController::class);

    // Novedades
    Route::apiResource('novelties', NovedadController::class);

    // Recordatorios
    Route::apiResource('reminders', RecordatorioController::class);

    // Turnos / Guardia
    Route::apiResource('shifts', TurnoController::class);
    Route::post('shifts/{id}/users', [TurnoController::class, 'addUser']);
});
