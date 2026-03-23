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
use App\Models\Tenant;
use Stancl\Tenancy\Facades\Tenancy;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar el cleanup diario para que se ejecute automáticamente a las 07:00
// El comando tiene una ventana de 5 minutos (07:00-07:05) para ejecutar la lógica
Schedule::command('guardia:daily-cleanup')->everyMinute();

// Scheduler de guardias que corre en todos los tenants
Schedule::call(function () {
    runGuardiaScheduler();
})->everyMinute();

/**
 * Ejecuta el scheduler de guardias en el contexto de cada tenant
 */
function runGuardiaScheduler()
{
    $tenants = Tenant::all();
    
    foreach ($tenants as $tenant) {
        try {
            // Inicializar contexto del tenant
            Tenancy::initialize($tenant);
            
            $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
            $nowLocal = Carbon::now($scheduleTz);
            $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));
            $logContext = [
                'command' => 'guardia-weekly-transition',
                'tenant' => tenant('id'),
                'now_local' => $nowLocal->toDateTimeString(),
                'now_app' => $nowApp->toDateTimeString(),
                'schedule_tz' => $scheduleTz,
            ];

            // Reset estado de guardia anterior
            $resetGuardiaState = function (Guardia $guardia) {
                User::where('guardia_id', $guardia->id)
                    ->where('is_titular', true)
                    ->update([
                        'attendance_status' => 'constituye',
                        'job_replacement_id' => null,
                        'is_shift_leader' => false,
                        'is_exchange' => false,
                        'is_penalty' => false,
                        'role' => DB::raw("CASE WHEN role = 'jefe_guardia' THEN 'bombero' ELSE role END"),
                    ]);
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
                            $previousActiveGuardia = Guardia::where('is_active_week', true)->first();
                            
                            DB::transaction(function () use ($targetGuardia, $resetGuardiaState, $previousActiveGuardia, $logContext) {
                                if ($previousActiveGuardia && $previousActiveGuardia->id !== $targetGuardia->id) {
                                    $resetGuardiaState($previousActiveGuardia);
                                }

                                Guardia::query()->update(['is_active_week' => false]);
                                $targetGuardia->update(['is_active_week' => true]);
                            });

                            Log::info('Guardia weekly transition executed', $logContext + [
                                'transition_time' => $weekTransitionTime,
                                'calendar_date' => $nowLocal->toDateString(),
                                'previous_guardia_id' => $previousActiveGuardia?->id,
                                'previous_guardia_name' => $previousActiveGuardia?->name,
                                'target_guardia_id' => $targetGuardia->id,
                                'target_guardia_name' => $targetGuardia->name,
                            ]);
                        }
                    }
                }
            }

            // Finalizar contexto del tenant
            Tenancy::end();
            
        } catch (\Exception $e) {
            Log::error('Guardia scheduler failed for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Asegurar terminar contexto en caso de error
            try {
                Tenancy::end();
            } catch (\Exception $endException) {
                Log::error('Failed to end tenant context', [
                    'tenant' => $tenant->id,
                    'error' => $endException->getMessage()
                ]);
            }
        }
    }
}

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
    $logContext = [
        'command' => 'guardia:daily-cleanup',
        'now_local' => $nowLocal->toDateTimeString(),
        'now_app' => $nowApp->toDateTimeString(),
        'schedule_tz' => $scheduleTz,
    ];

    $cleanupTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
    [$cleanupH, $cleanupM] = array_map('intval', explode(':', (string) $cleanupTime));
    $runAt = $nowLocal->copy()->startOfDay()->addHours($cleanupH)->addMinutes($cleanupM);
    $windowEnd = $runAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($runAt) && $nowLocal->lessThan($windowEnd))) {
        Log::info('Guardia daily cleanup skipped: outside execution window', $logContext + [
            'configured_time' => $cleanupTime,
            'window_start' => $runAt->toDateTimeString(),
            'window_end' => $windowEnd->toDateTimeString(),
        ]);
        return;
    }

    Log::info('Guardia daily cleanup started', $logContext + [
        'configured_time' => $cleanupTime,
        'window_start' => $runAt->toDateTimeString(),
        'window_end' => $windowEnd->toDateTimeString(),
    ]);

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

        // Dejar guardias solo con dotación titular: sacar todo NO titular.
        // Nota: algunos NO titulares tienen "guardia anterior" (refuerzos), otros simplemente se liberan (null).
        $nonTitular = Bombero::query()
            ->whereNotNull('guardia_id')
            ->where('es_titular', false)
            ->get();

        foreach ($nonTitular as $bombero) {
            $bomberoLocalDate = $localDateString($bombero->updated_at);
            if (!$bomberoLocalDate || $bomberoLocalDate >= $todayLocal) {
                continue;
            }

            $prevGuardiaId = $bombero->refuerzo_guardia_anterior_id;

            $bombero->update([
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
                    ->where('firefighter_id', $bombero->id)
                    ->update([
                        'guardia_id' => $prevGuardiaId,
                        'attendance_status' => 'constituye',
                        'assignment_type' => null,
                        'replaced_user_id' => null,
                        'replaced_firefighter_id' => null,
                    ]);
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

        // Liberar camas de refuerzos y reemplazos activos
        $refuerzoIds = Bombero::query()
            ->where('es_refuerzo', true)
            ->pluck('id')
            ->toArray();

        $reemplazanteIds = ReemplazoBombero::query()
            ->where('estado', 'activo')
            ->pluck('bombero_reemplazante_id')
            ->filter()
            ->toArray();

        $replacementAndRefuerzoBomberoIds = array_values(array_unique(array_merge($refuerzoIds, $reemplazanteIds)));

        if (!empty($replacementAndRefuerzoBomberoIds)) {
            $assignmentsToRelease = BedAssignment::query()
                ->whereNull('released_at')
                ->whereIn('firefighter_id', $replacementAndRefuerzoBomberoIds)
                ->get();

            foreach ($assignmentsToRelease as $assignment) {
                $assignment->update(['released_at' => $nowApp]);
                if ($assignment->bed_id) {
                    Bed::where('id', $assignment->bed_id)->update(['status' => 'available']);
                }
            }
        }
    });

    $this->info('Daily cleanup ejecutado (' . $nowLocal->toDateTimeString() . ')');
})->purpose('A las 07:00 AM: cierra reemplazos, resetea estados, elimina refuerzos y deja solo dotación titular');

// Comando legacy: usar runGuardiaScheduler() en su lugar
Artisan::command('guardia:run-calendar', function () {
    $this->info('Este comando está obsoleto. El scheduler ahora corre automáticamente en todos los tenants.');
    $this->info('Para ejecutar manualmente, usa: php artisan tinker y ejecuta runGuardiaScheduler();');
})->purpose('Comando obsoleto - el scheduler ahora corre automáticamente en todos los tenants');

Artisan::command('guardia:reset-beds {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));
    $logContext = [
        'command' => 'guardia:reset-beds',
        'now_local' => $nowLocal->toDateTimeString(),
        'now_app' => $nowApp->toDateTimeString(),
        'schedule_tz' => $scheduleTz,
    ];

    if (!$nowLocal->isSunday()) {
        Log::info('Guardia reset beds skipped: not sunday', $logContext);
        return;
    }

    $cleanupTime = SystemSetting::getValue('guardia_week_cleanup_time', '18:00');
    [$resetH, $resetM] = array_map('intval', explode(':', (string) $cleanupTime));
    $resetAt = $nowLocal->copy()->startOfDay()->addHours($resetH)->addMinutes($resetM);
    $windowEnd = $resetAt->copy()->addMinutes(5);
    if (!($nowLocal->greaterThanOrEqualTo($resetAt) && $nowLocal->lessThan($windowEnd))) {
        Log::info('Guardia reset beds skipped: outside execution window', $logContext + [
            'configured_time' => $cleanupTime,
            'window_start' => $resetAt->toDateTimeString(),
            'window_end' => $windowEnd->toDateTimeString(),
        ]);
        return;
    }

    DB::transaction(function () use ($nowApp) {
        BedAssignment::whereNull('released_at')->update(['released_at' => $nowApp]);
        Bed::where('status', 'occupied')->update(['status' => 'available']);
    });

    Log::info('Guardia reset beds executed', $logContext + [
        'configured_time' => $cleanupTime,
    ]);

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
        $activeGuardia = Guardia::where('is_active_week', true)->first();
        if ($activeGuardia) {
            return $activeGuardia;
        }

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

        return null;
    })();

    Log::info('Guardia notifications resolved active guardia', [
        'command' => 'guardia:generate-notifications',
        'now_local' => $nowLocal->toDateTimeString(),
        'schedule_tz' => $scheduleTz,
        'active_guardia_id' => $activeGuardia?->id,
        'active_guardia_name' => $activeGuardia?->name,
    ]);

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
})->purpose('Genera notificaciones automáticas de constitución pendiente y academias del día');

Artisan::command('guardia:weekly-archive-clean {--at=} {--tz=}', function () {
    $scheduleTz = $this->option('tz')
        ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $at = $this->option('at');

    $nowLocal = $at ? Carbon::parse($at, $scheduleTz) : Carbon::now($scheduleTz);
    $nowApp = $nowLocal->copy()->setTimezone(config('app.timezone'));
    $logContext = [
        'command' => 'guardia:weekly-archive-clean',
        'now_local' => $nowLocal->toDateTimeString(),
        'now_app' => $nowApp->toDateTimeString(),
        'schedule_tz' => $scheduleTz,
    ];

    if (!$nowLocal->isSunday()) {
        Log::info('Guardia weekly archive skipped: not sunday', $logContext);
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
        Log::warning('Guardia weekly archive skipped: no previous week calendar day found', $logContext + [
            'previous_week_start' => $weekStartPrevious->toDateString(),
        ]);
        return;
    }

    $outgoingGuardia = Guardia::find($calendarDay->guardia_id);
    if (!$outgoingGuardia) {
        Log::warning('Guardia weekly archive skipped: outgoing guardia not found', $logContext + [
            'calendar_guardia_id' => $calendarDay->guardia_id,
        ]);
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

    Log::info('Guardia weekly archive executed', $logContext + [
        'outgoing_guardia_id' => $outgoingGuardia->id,
        'outgoing_guardia_name' => $outgoingGuardia->name,
        'archive_id' => $archive?->id,
        'archive_label' => $archive?->label,
    ]);
})->purpose('Domingo: archiva y limpia datos operativos al cierre semanal de una guardia (según horario configurado)');
