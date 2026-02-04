<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use App\Models\Novelty;
use App\Models\GuardiaAttendanceRecord;
use App\Models\InAppNotification;
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

Artisan::command('guardia:reset-beds {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz') ?: env('GUARDIA_SCHEDULE_TZ', config('app.timezone'));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));

    if (!$nowLocal->isSunday()) {
        return;
    }

    $resetAt = $nowLocal->copy()->startOfDay()->addHours(18);
    $windowEnd = $resetAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($resetAt) && $nowLocal->lessThan($windowEnd))) {
        return;
    }

    DB::transaction(function () use ($nowApp) {
        BedAssignment::whereNull('released_at')->update(['released_at' => $nowApp]);
        Bed::where('status', 'occupied')->update(['status' => 'available']);
    });

    $this->info('Camas reseteadas correctamente (' . $nowLocal->toDateTimeString() . ')');
})->purpose('Resetea camas (libera asignaciones y deja camas disponibles) a las 18:00 del último día de guardia');

Artisan::command('guardia:generate-notifications {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz') ?: env('GUARDIA_SCHEDULE_TZ', config('app.timezone'));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);

    $shiftBusinessDate = function (Carbon $dt) {
        $cutoff = $dt->copy()->startOfDay()->addHours(7);
        return $dt->lessThan($cutoff) ? $dt->copy()->subDay()->toDateString() : $dt->toDateString();
    };

    $withinWindow = function (Carbon $dt, string $hhmm, int $minutes = 5) {
        [$h, $m] = array_map('intval', explode(':', $hhmm));
        $start = $dt->copy()->startOfDay()->addHours($h)->addMinutes($m);
        $end = $start->copy()->addMinutes($minutes);
        return $dt->greaterThanOrEqualTo($start) && $dt->lessThan($end);
    };

    $activeGuardia = (function () use ($nowLocal) {
        $weekStart = $nowLocal->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarDay = GuardiaCalendarDay::with('guardia')
            ->where('date', $weekStart->toDateString())
            ->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::with('guardia')
                ->where('date', $nowLocal->toDateString())
                ->first();
        }

        if ($calendarDay && $calendarDay->guardia) {
            return $calendarDay->guardia;
        }

        return Guardia::where('is_active_week', true)->first();
    })();

    if ($activeGuardia) {
        $businessDate = $shiftBusinessDate($nowLocal);

        if ($withinWindow($nowLocal, '23:55') || $withinWindow($nowLocal, '00:00')) {
            $already = GuardiaAttendanceRecord::where('guardia_id', $activeGuardia->id)
                ->whereDate('date', $businessDate)
                ->exists();

            if (!$already) {
                $targetUsers = User::whereIn('role', ['super_admin', 'capitania'])
                    ->get();

                $guardiaAccount = User::where('role', 'guardia')->where('guardia_id', $activeGuardia->id)->first();
                if ($guardiaAccount) {
                    $targetUsers->push($guardiaAccount);
                }

                $slot = $withinWindow($nowLocal, '23:55') ? '2355' : '0000';
                foreach ($targetUsers->unique('id') as $u) {
                    $uniqueKey = 'guardia_not_constituted_' . $businessDate . '_' . $slot . '_' . $activeGuardia->id . '_' . $u->id;
                    InAppNotification::firstOrCreate(
                        ['unique_key' => $uniqueKey],
                        [
                            'user_id' => $u->id,
                            'type' => 'guardia',
                            'title' => 'Guardia sin constituir',
                            'message' => 'La guardia ' . $activeGuardia->name . ' aún no registra asistencia (' . $businessDate . ').',
                            'action_url' => url('/'),
                        ]
                    );
                }
            }
        }
    }

    if ($withinWindow($nowLocal, '23:00')) {
        $localDate = $nowLocal->toDateString();

        $academies = Novelty::where('type', 'Academia')
            ->whereNotNull('date')
            ->get()
            ->filter(function ($n) use ($scheduleTz, $localDate) {
                if (!$n->date) return false;
                return Carbon::parse($n->date)->setTimezone($scheduleTz)->toDateString() === $localDate;
            });

        if ($academies->isNotEmpty()) {
            foreach ($academies as $academy) {
                $responsible = $academy->user_id ? User::find($academy->user_id) : null;
                $targets = collect();

                $targets = $targets->merge(User::whereIn('role', ['super_admin', 'capitania'])->get());
                if ($responsible) {
                    $targets->push($responsible);
                }

                $guardiaId = $responsible?->guardia_id;
                if ($guardiaId) {
                    $guardiaAccount = User::where('role', 'guardia')->where('guardia_id', $guardiaId)->first();
                    if ($guardiaAccount) {
                        $targets->push($guardiaAccount);
                    }
                }

                foreach ($targets->unique('id') as $u) {
                    $uniqueKey = 'academy_reminder_' . $academy->id . '_' . $localDate . '_' . $u->id;
                    InAppNotification::firstOrCreate(
                        ['unique_key' => $uniqueKey],
                        [
                            'user_id' => $u->id,
                            'type' => 'academy',
                            'title' => 'Academia programada hoy',
                            'message' => ($academy->title ?: 'Academia') . ' - ' . ($academy->description ? \Illuminate\Support\Str::limit($academy->description, 90) : ''),
                            'action_url' => url('/'),
                        ]
                    );
                }
            }
        }
    }
})->purpose('Genera notificaciones in-app (guardia sin constituir y recordatorios de academias)');
