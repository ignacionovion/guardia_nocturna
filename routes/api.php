<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BedController;
use App\Http\Controllers\BedAssignmentController;
use App\Http\Controllers\StaffEventController;
use App\Http\Controllers\CleaningTaskController;
use App\Http\Controllers\CleaningAssignmentController;
use App\Http\Controllers\NoveltyController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ShiftController;

use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Usuarios
    Route::get('users/birthdays', [UserController::class, 'birthdays']);
    Route::apiResource('users', UserController::class);

    // Camas
    Route::apiResource('beds', BedController::class);
    Route::apiResource('bed-assignments', BedAssignmentController::class);

    // Personal (Remplazos, Permisos, etc)
    Route::apiResource('staff-events', StaffEventController::class);

    // Aseo
    Route::apiResource('cleaning-tasks', CleaningTaskController::class);
    Route::apiResource('cleaning-assignments', CleaningAssignmentController::class);

    // Novedades
    Route::apiResource('novelties', NoveltyController::class);

    // Recordatorios
    Route::apiResource('reminders', ReminderController::class);

    // Turnos / Guardia
    Route::apiResource('shifts', ShiftController::class);
    Route::post('shifts/{id}/users', [ShiftController::class, 'addUser']);
});
