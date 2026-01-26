<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use App\Services\ReplacementService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('guardia:expire-replacements', function () {
    $processed = ReplacementService::expire(Carbon::now());
    $this->info("Reemplazos vencidos procesados: {$processed}");
})->purpose('Libera reemplazos vencidos y restaura el estado original del reemplazante');

Artisan::command('guardia:run-calendar {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz') ?: env('GUARDIA_SCHEDULE_TZ', config('app.timezone'));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));

    $closeActiveShifts = function (?int $exceptShiftId = null) use ($nowApp, $nowLocal) {
        Shift::where('status', 'active')
            ->when($exceptShiftId, fn ($q) => $q->where('id', '!=', $exceptShiftId))
            ->chunkById(50, function ($shifts) use ($nowApp, $nowLocal) {
                foreach ($shifts as $shift) {
                    ShiftUser::where('shift_id', $shift->id)
                        ->whereNull('end_time')
                        ->update(['end_time' => $nowApp]);

                    $notesPrefix = $shift->notes ? rtrim($shift->notes) . "\n" : '';
                    $shift->update([
                        'status' => 'closed',
                        'notes' => $notesPrefix . 'Cerrado automáticamente por Calendario (' . $nowLocal->toDateTimeString() . ')',
                    ]);
                }
            });
    };

    $resetGuardiaState = function (Guardia $guardia) {
        $transitorios = User::where('guardia_id', $guardia->id)
            ->where('is_titular', false)
            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'job_replacement_id' => null,
                'attendance_status' => 'constituye',
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
                'role' => ($user->role === 'jefe_guardia') ? 'bombero' : $user->role,
            ]);
        }

        $titulares = User::where('guardia_id', $guardia->id)
            ->where('is_titular', true)
            ->get();

        foreach ($titulares as $user) {
            $user->update([
                'attendance_status' => 'constituye',
                'job_replacement_id' => null,
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
                'role' => ($user->role === 'jefe_guardia') ? 'bombero' : $user->role,
            ]);
        }
    };

    $closeAt = $nowLocal->copy()->startOfDay()->addHours(7);
    $closeWindowEnd = $closeAt->copy()->addMinutes(5);
    if ($nowLocal->greaterThanOrEqualTo($closeAt) && $nowLocal->lessThan($closeWindowEnd)) {
        $closeActiveShifts();
        return;
    }

    $scheduleHour = $nowLocal->isSunday() ? 22 : 23;
    $startAt = $nowLocal->copy()->startOfDay()->addHours($scheduleHour);
    $startWindowEnd = $startAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($startAt) && $nowLocal->lessThan($startWindowEnd))) {
        return;
    }

    $calendarDay = GuardiaCalendarDay::where('date', $nowLocal->toDateString())->first();
    if (!$calendarDay) {
        return;
    }

    $targetGuardia = Guardia::find($calendarDay->guardia_id);
    if (!$targetGuardia) {
        return;
    }

    $leader = User::where('guardia_id', $targetGuardia->id)
        ->where('role', 'guardia')
        ->first();

    if (!$leader) {
        $leader = User::where('guardia_id', $targetGuardia->id)->first();
    }

    if (!$leader) {
        return;
    }

    $existingShift = Shift::where('status', 'active')
        ->where('date', $nowLocal->toDateString())
        ->first();

    DB::transaction(function () use ($targetGuardia, $resetGuardiaState) {
        $previousActiveGuardia = Guardia::where('is_active_week', true)->first();
        if ($previousActiveGuardia && $previousActiveGuardia->id !== $targetGuardia->id) {
            $resetGuardiaState($previousActiveGuardia);
        }

        Guardia::query()->update(['is_active_week' => false]);
        $targetGuardia->update(['is_active_week' => true]);
    });

    $closeActiveShifts($existingShift?->id);

    if ($existingShift) {
        return;
    }

    Shift::create([
        'date' => $nowLocal->toDateString(),
        'status' => 'active',
        'shift_leader_id' => $leader->id,
        'notes' => 'Guardia generada automáticamente por Calendario',
    ]);
})->purpose('Activa guardia según calendario y crea/cierra turnos automáticamente');
