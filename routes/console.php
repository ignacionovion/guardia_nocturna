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
use App\Models\ReemplazoBombero;
use App\Models\Bombero;
use App\Models\SystemSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('guardia:expire-replacements', function () {
    $processed = ReplacementService::expire(Carbon::now());
    $this->info("Reemplazos vencidos procesados: {$processed}");
})->purpose('Libera reemplazos vencidos y restaura el estado original del reemplazante');

Artisan::command('guardia:snapshot-assignments', function () {
    $path = base_path('database/seeders/data/guardia_assignments_snapshot.json');
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $guardias = Guardia::with(['bomberos' => function ($q) {
        $q->whereNotNull('rut');
    }])->orderBy('name')->get();

    $assignments = [];
    foreach ($guardias as $g) {
        $ruts = $g->bomberos
            ->pluck('rut')
            ->filter(fn ($v) => trim((string) $v) !== '')
            ->map(fn ($v) => trim((string) $v))
            ->values()
            ->toArray();

        if (empty($ruts)) {
            continue;
        }

        $assignments[] = [
            'guardia_name' => $g->name,
            'bomberos_rut' => $ruts,
        ];
    }

    $payload = [
        'generated_at' => Carbon::now()->toIso8601String(),
        'assignments' => $assignments,
    ];

    file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $this->info('Snapshot guardias/bomberos guardado en: ' . $path);
    $this->info('Guardias exportadas: ' . count($assignments));
})->purpose('Exporta asignaciones Guardia->Bomberos a JSON para rehidratar en seed');

Artisan::command('guardia:daily-cleanup {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));

    $runAt = $nowLocal->copy()->startOfDay()->addHours(10);
    $windowEnd = $runAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($runAt) && $nowLocal->lessThan($windowEnd))) {
        return;
    }

    $shift = Shift::where('status', 'active')->latest()->first();

    $todayLocal = $nowLocal->toDateString();

    $localDateString = function ($dt) use ($scheduleTz) {
        if (!$dt) {
            return null;
        }
        return Carbon::parse($dt)->setTimezone($scheduleTz)->toDateString();
    };

    DB::transaction(function () use ($nowApp, $shift, $todayLocal, $localDateString) {
        $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('estado', 'activo')
            ->get();

        foreach ($activeReplacements as $rep) {
            $repLocalDate = $localDateString($rep->inicio);
            if (!$repLocalDate || $repLocalDate >= $todayLocal) {
                continue;
            }

            $rep->update([
                'estado' => 'cerrado',
                'fin' => $nowApp,
            ]);

            $original = $rep->originalFirefighter;
            $replacer = $rep->replacementFirefighter;

            if ($original) {
                $original->update([
                    'estado_asistencia' => 'constituye',
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);
            }

            if ($replacer) {
                $prevGuardiaId = null;
                if ($rep->notas) {
                    $decodedNotes = json_decode((string) $rep->notas, true);
                    if (is_array($decodedNotes) && array_key_exists('replacement_previous_guardia_id', $decodedNotes)) {
                        $prevGuardiaId = $decodedNotes['replacement_previous_guardia_id'];
                    }
                }

                $replacer->update([
                    'guardia_id' => $prevGuardiaId,
                    'estado_asistencia' => 'constituye',
                    'es_titular' => false,
                    'es_jefe_guardia' => false,
                    'es_refuerzo' => false,
                    'refuerzo_guardia_anterior_id' => null,
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);

                if ($shift) {
                    ShiftUser::where('shift_id', $shift->id)
                        ->where('firefighter_id', $replacer->id)
                        ->update([
                            'guardia_id' => $prevGuardiaId,
                            'attendance_status' => 'constituye',
                            'assignment_type' => null,
                            'replaced_user_id' => null,
                            'replaced_firefighter_id' => null,
                        ]);
                }
            }
        }

        $temporales = Bombero::query()
            ->whereIn('estado_asistencia', ['ausente', 'permiso', 'licencia', 'falta'])
            ->get();

        foreach ($temporales as $bombero) {
            $bomberoLocalDate = $localDateString($bombero->updated_at);
            if (!$bomberoLocalDate || $bomberoLocalDate >= $todayLocal) {
                continue;
            }

            $bombero->update([
                'estado_asistencia' => 'constituye',
            ]);
        }

        $refuerzos = Bombero::query()
            ->where('es_refuerzo', true)
            ->get();

        foreach ($refuerzos as $refuerzo) {
            $refuerzoLocalDate = $localDateString($refuerzo->updated_at);
            if (!$refuerzoLocalDate || $refuerzoLocalDate >= $todayLocal) {
                continue;
            }

            $prevGuardiaId = $refuerzo->refuerzo_guardia_anterior_id;

            $refuerzo->update([
                'guardia_id' => $prevGuardiaId,
                'estado_asistencia' => 'constituye',
                'es_refuerzo' => false,
                'refuerzo_guardia_anterior_id' => null,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);

            if ($shift) {
                ShiftUser::where('shift_id', $shift->id)
                    ->where('firefighter_id', $refuerzo->id)
                    ->update([
                        'guardia_id' => $prevGuardiaId,
                        'attendance_status' => 'constituye',
                        'assignment_type' => null,
                        'replaced_user_id' => null,
                        'replaced_firefighter_id' => null,
                    ]);
            }
        }
    });

    $this->info('Daily cleanup ejecutado (' . $nowLocal->toDateTimeString() . ')');
})->purpose('A las 10:00 AM: cierra reemplazos, resetea estados y elimina refuerzos');

Artisan::command('guardia:run-calendar {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
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

    $weekTransitionTime = SystemSetting::getValue('guardia_week_transition_time', '18:00');
    if ($nowLocal->isSunday()) {
        [$transH, $transM] = array_map('intval', explode(':', (string) $weekTransitionTime));
        $transitionAt = $nowLocal->copy()->startOfDay()->addHours($transH)->addMinutes($transM);
        $transitionWindowEnd = $transitionAt->copy()->addMinutes(5);

        if ($nowLocal->greaterThanOrEqualTo($transitionAt) && $nowLocal->lessThan($transitionWindowEnd)) {
            $calendarDay = GuardiaCalendarDay::where('date', $nowLocal->toDateString())->first();
            if ($calendarDay) {
                $targetGuardia = Guardia::find($calendarDay->guardia_id);
                if ($targetGuardia) {
                    DB::transaction(function () use ($targetGuardia, $resetGuardiaState) {
                        $previousActiveGuardia = Guardia::where('is_active_week', true)->first();
                        if ($previousActiveGuardia && $previousActiveGuardia->id !== $targetGuardia->id) {
                            $resetGuardiaState($previousActiveGuardia);
                        }

                        Guardia::query()->update(['is_active_week' => false]);
                        $targetGuardia->update(['is_active_week' => true]);
                    });
                }
            }

            return;
        }
    }

    $dailyEndTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
    [$closeH, $closeM] = array_map('intval', explode(':', (string) $dailyEndTime));
    $closeAt = $nowLocal->copy()->startOfDay()->addHours($closeH)->addMinutes($closeM);
    $closeWindowEnd = $closeAt->copy()->addMinutes(5);
    if ($nowLocal->greaterThanOrEqualTo($closeAt) && $nowLocal->lessThan($closeWindowEnd)) {
        $closeActiveShifts();
        return;
    }

    $weekdayStartTime = SystemSetting::getValue('guardia_constitution_weekday_time', '23:00');
    $sundayStartTime = SystemSetting::getValue('guardia_constitution_sunday_time', '22:00');

    $startTime = $nowLocal->isSunday() ? $sundayStartTime : $weekdayStartTime;
    [$startH, $startM] = array_map('intval', explode(':', (string) $startTime));
    $startAt = $nowLocal->copy()->startOfDay()->addHours($startH)->addMinutes($startM);
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
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));

    if (!$nowLocal->isSunday()) {
        return;
    }

    $cleanupTime = SystemSetting::getValue('guardia_week_cleanup_time', '18:00');
    [$resetH, $resetM] = array_map('intval', explode(':', (string) $cleanupTime));
    $resetAt = $nowLocal->copy()->startOfDay()->addHours($resetH)->addMinutes($resetM);
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
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
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

Artisan::command('guardia:weekly-archive-clean {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));

    if (!$nowLocal->isSunday()) {
        return;
    }

    $cleanupTime = SystemSetting::getValue('guardia_week_cleanup_time', '18:00');
    [$cleanupH, $cleanupM] = array_map('intval', explode(':', (string) $cleanupTime));
    $runAt = $nowLocal->copy()->startOfDay()->addHours($cleanupH)->addMinutes($cleanupM);
    $windowEnd = $runAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($runAt) && $nowLocal->lessThan($windowEnd))) {
        return;
    }

    $weekStartPrevious = $nowLocal->copy()->startOfWeek(Carbon::SUNDAY)->subWeek();
    $calendarDay = GuardiaCalendarDay::where('date', $weekStartPrevious->toDateString())->first();
    if (!$calendarDay) {
        return;
    }

    $outgoingGuardia = Guardia::find($calendarDay->guardia_id);
    if (!$outgoingGuardia) {
        return;
    }

    $archive = null;

    DB::transaction(function () use ($outgoingGuardia, $nowApp, $scheduleTz, &$archive) {
        $archive = \App\Models\GuardiaArchive::create([
            'guardia_id' => $outgoingGuardia->id,
            'archived_at' => $nowApp,
            'label' => 'Cierre semanal',
        ]);

        $firefighters = Bombero::query()
            ->where('guardia_id', $outgoingGuardia->id)
            ->get();

        $firefighterIds = $firefighters->pluck('id')->map(fn ($v) => (int) $v)->values()->toArray();

        $guardiaUserIds = User::query()
            ->where('guardia_id', $outgoingGuardia->id)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->toArray();

        $createItem = function (?int $firefighterId, string $entityType, $entity, array $payload) use ($archive) {
            \App\Models\GuardiaArchiveItem::create([
                'guardia_archive_id' => $archive->id,
                'firefighter_id' => $firefighterId,
                'entity_type' => $entityType,
                'entity_id' => $entity?->id,
                'payload' => $payload,
            ]);
        };

        // Novedades/Academias
        $novelties = Novelty::query()
            ->when(!empty($firefighterIds), fn ($q) => $q->whereIn('firefighter_id', $firefighterIds), fn ($q) => $q)
            ->orWhereIn('user_id', $guardiaUserIds)
            ->orderByDesc('id')
            ->get();

        foreach ($novelties as $n) {
            $ffId = $n->firefighter_id ? (int) $n->firefighter_id : null;
            $createItem($ffId, 'novelty', $n, [
                'title' => $n->title,
                'description' => $n->description,
                'type' => $n->type,
                'date' => $n->date ? Carbon::parse($n->date)->setTimezone($scheduleTz)->toDateTimeString() : null,
                'user_id' => $n->user_id,
                'firefighter_id' => $ffId,
            ]);
        }

        // Emergencias
        $emergencies = \App\Models\Emergency::query()
            ->where('guardia_id', $outgoingGuardia->id)
            ->orderByDesc('id')
            ->get();

        foreach ($emergencies as $e) {
            $ffId = $e->officer_in_charge_firefighter_id ? (int) $e->officer_in_charge_firefighter_id : null;
            $createItem($ffId, 'emergency', $e, [
                'emergency_key_id' => $e->emergency_key_id,
                'dispatched_at' => $e->dispatched_at?->setTimezone($scheduleTz)?->toDateTimeString(),
                'arrived_at' => $e->arrived_at?->setTimezone($scheduleTz)?->toDateTimeString(),
                'details' => $e->details,
                'shift_id' => $e->shift_id,
                'guardia_id' => $e->guardia_id,
                'officer_in_charge_user_id' => $e->officer_in_charge_user_id,
                'officer_in_charge_firefighter_id' => $ffId,
                'created_by' => $e->created_by,
            ]);
        }

        // Aseo
        $cleaning = \App\Models\CleaningAssignment::query()
            ->when(!empty($firefighterIds), fn ($q) => $q->whereIn('firefighter_id', $firefighterIds), fn ($q) => $q)
            ->orWhereIn('user_id', $guardiaUserIds)
            ->orderByDesc('id')
            ->get();

        foreach ($cleaning as $c) {
            $ffId = $c->firefighter_id ? (int) $c->firefighter_id : null;
            $createItem($ffId, 'cleaning', $c, [
                'cleaning_task_id' => $c->cleaning_task_id,
                'assigned_date' => $c->assigned_date?->toDateString(),
                'status' => $c->status,
                'user_id' => $c->user_id,
                'firefighter_id' => $ffId,
            ]);
        }

        // Camas (snapshot global de asignaciones activas + reset)
        $activeBedAssignments = BedAssignment::query()
            ->whereNull('released_at')
            ->orderByDesc('id')
            ->get();

        foreach ($activeBedAssignments as $ba) {
            $createItem($ba->firefighter_id ? (int) $ba->firefighter_id : null, 'bed_assignment', $ba, [
                'bed_id' => $ba->bed_id,
                'user_id' => $ba->user_id,
                'firefighter_id' => $ba->firefighter_id,
                'assigned_at' => $ba->assigned_at?->setTimezone($scheduleTz)?->toDateTimeString(),
                'released_at' => $ba->released_at?->setTimezone($scheduleTz)?->toDateTimeString(),
                'notes' => $ba->notes,
            ]);
        }

        // Refuerzos activos en la guardia saliente (snapshot y revert)
        $refuerzos = Bombero::query()
            ->where('guardia_id', $outgoingGuardia->id)
            ->where('es_refuerzo', true)
            ->get();

        foreach ($refuerzos as $r) {
            $createItem((int) $r->id, 'refuerzo', $r, [
                'guardia_id' => $r->guardia_id,
                'refuerzo_guardia_anterior_id' => $r->refuerzo_guardia_anterior_id,
                'estado_asistencia' => $r->estado_asistencia,
            ]);

            $prevGuardiaId = $r->refuerzo_guardia_anterior_id;
            $r->update([
                'guardia_id' => $prevGuardiaId,
                'estado_asistencia' => 'constituye',
                'es_refuerzo' => false,
                'refuerzo_guardia_anterior_id' => null,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }

        // Limpieza datos operativos (guardia saliente)
        if (!empty($firefighterIds) || !empty($guardiaUserIds)) {
            Novelty::query()
                ->when(!empty($firefighterIds), fn ($q) => $q->whereIn('firefighter_id', $firefighterIds), fn ($q) => $q)
                ->orWhereIn('user_id', $guardiaUserIds)
                ->delete();

            \App\Models\CleaningAssignment::query()
                ->when(!empty($firefighterIds), fn ($q) => $q->whereIn('firefighter_id', $firefighterIds), fn ($q) => $q)
                ->orWhereIn('user_id', $guardiaUserIds)
                ->delete();
        }

        \App\Models\Emergency::query()->where('guardia_id', $outgoingGuardia->id)->delete();

        BedAssignment::whereNull('released_at')->update(['released_at' => $nowApp]);
        Bed::where('status', 'occupied')->update(['status' => 'available']);
    });

    $this->info('Weekly archive/clean ejecutado para guardia ' . $outgoingGuardia->name . ' (' . $nowLocal->toDateTimeString() . ')');
})->purpose('Domingo: archiva y limpia datos operativos al cierre semanal de una guardia (según horario configurado)');
