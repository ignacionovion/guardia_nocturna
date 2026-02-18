<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bed;
use App\Models\Shift;
use App\Models\User;
use App\Models\Novelty;
use App\Models\BedAssignment;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\GuardiaAttendanceRecord;
use App\Models\SystemSetting;
use App\Models\Bombero;
use App\Models\ReemplazoBombero;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Models\ShiftUser;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TableroController extends Controller
{
    private function resolveActiveGuardia($now)
    {
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $calendarDay = GuardiaCalendarDay::with('guardia')
            ->where('date', $weekStart->toDateString())
            ->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::with('guardia')
                ->where('date', $now->toDateString())
                ->first();
        }

        if ($calendarDay && $calendarDay->guardia) {
            return $calendarDay->guardia;
        }

        return Guardia::where('is_active_week', true)->first();
    }

    public function index()
    {
        $user = auth()->user();
        $now = now();

        $guardiaTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));

        ReplacementService::expire($now);
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();
        $availableBeds = Bed::where('status', 'available')->count();
        
        $activeGuardia = $this->resolveActiveGuardia($now);

        // Buscar turno activo (filtrado por guardia si corresponde)
        $shiftQuery = Shift::where('status', 'active');
        if ($user->guardia_id) {
            $shiftQuery->whereHas('leader', function($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            });
        }
        $currentShift = $shiftQuery->latest()->first();

        // Fallback: datos legacy pueden no tener guardia_id en el líder del turno.
        // En ese caso, intentamos detectar el turno por personal activo con firefighter_id.
        if (!$currentShift && $user->guardia_id) {
            $currentShift = Shift::where('status', 'active')
                ->whereHas('users', function ($q) use ($user) {
                    $q->where(function ($q2) {
                        $q2->whereNull('end_time')
                            ->orWhere('end_time', '>', now());
                    })
                    ->whereHas('firefighter', function ($q3) use ($user) {
                        $q3->where('guardia_id', $user->guardia_id);
                    });
                })
                ->latest()
                ->first();
        }

        // Turno activo global (para excluir "en turno" de listas como reemplazos)
        $globalCurrentShift = Shift::with('users')->where('status', 'active')->latest()->first();
        $globalOnDutyUserIds = $globalCurrentShift
            ? $globalCurrentShift->users->whereNull('end_time')->pluck('user_id')->values()->toArray()
            : [];

        $globalOnDutyFirefighterIds = $globalCurrentShift
            ? $globalCurrentShift->users->whereNull('end_time')->pluck('firefighter_id')->filter()->values()->toArray()
            : [];

        $novelties = Novelty::with('user')->latest()->take(5)->get();
        $guardiaNovelties = null;
        $academies = Novelty::with('user')->where('type', 'Academia')->latest()->take(5)->get();
        $academyLeaders = collect();
        $academyLeadersFirefighters = collect();
        $isMyGuardiaOnDuty = false;
        $hasAttendanceSavedToday = false;

        $computeUpcomingBirthdays = function ($source) {
            return collect($source)
                ->filter(function ($b) {
                    return (bool) ($b->fecha_nacimiento ?? null);
                })
                ->map(function ($b) {
                    $birthdayThisYear = $b->fecha_nacimiento->copy()->year(now()->year);
                    if ($birthdayThisYear->isPast() && !$birthdayThisYear->isToday()) {
                        $birthdayThisYear->addYear();
                    }
                    $b->next_birthday = $birthdayThisYear;
                    return $b;
                })
                ->sortBy('next_birthday')
                ->take(5);
        };

        // Próximos cumpleaños (Bomberos)
        $birthdaysSource = Bombero::query()
            ->whereNotNull('fecha_nacimiento')
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            })
            ->get();

        $birthdays = $computeUpcomingBirthdays($birthdaysSource);

        $birthdaysMonthCount = $birthdaysSource
            ->filter(function ($b) {
                return (bool) ($b->fecha_nacimiento ?? null);
            })
            ->filter(function ($b) {
                return (int) $b->fecha_nacimiento->month === (int) now()->month;
            })
            ->count();

        $birthdaysThisMonth = $birthdaysSource
            ->filter(function ($b) {
                return (bool) ($b->fecha_nacimiento ?? null);
            })
            ->filter(function ($b) {
                return (int) $b->fecha_nacimiento->month === (int) now()->month;
            })
            ->sortBy(function ($b) {
                return (int) $b->fecha_nacimiento->day;
            })
            ->values();

        // Data específica para cuentas de Guardia
        $myGuardia = null;
        $myStaff = collect();
        $replacementCandidates = collect();
        $replacementByOriginal = collect();
        $replacementByReplacement = collect();
        $guardiaIdForGuardiaUser = null;

        if ($user->role === 'guardia') {
            $guardiaIdForGuardiaUser = $user->guardia_id;

            if (!$guardiaIdForGuardiaUser) {
                $guardiaIdForGuardiaUser = Guardia::whereRaw('lower(name) = ?', [strtolower($user->name)])->value('id');
            }

            if (!$guardiaIdForGuardiaUser) {
                $emailLocal = explode('@', (string) $user->email)[0] ?? '';
                $emailLocal = str_replace('.', ' ', $emailLocal);
                $guardiaIdForGuardiaUser = Guardia::whereRaw('lower(name) = ?', [strtolower($emailLocal)])->value('id');
            }

            if (!$guardiaIdForGuardiaUser) {
                abort(403, 'Cuenta de guardia sin guardia asignada.');
            }

            $tz = $guardiaTz;
            $localNow = $now->copy()->setTimezone($tz);
            $dailyEndTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
            [$endH, $endM] = array_map('intval', explode(':', (string) $dailyEndTime));
            $endAt = $localNow->copy()->setTime($endH, $endM, 0);

            if ($localNow->greaterThanOrEqualTo($endAt)) {
                Bombero::query()
                    ->where('guardia_id', $guardiaIdForGuardiaUser)
                    ->where('es_titular', false)
                    ->get()
                    ->each(function (Bombero $b) {
                        $restoreGuardiaId = null;
                        if ((bool) ($b->es_refuerzo ?? false)) {
                            $restoreGuardiaId = $b->refuerzo_guardia_anterior_id;
                        }

                        $b->update([
                            'guardia_id' => $restoreGuardiaId,
                            'estado_asistencia' => 'constituye',
                            'es_jefe_guardia' => false,
                            'es_refuerzo' => false,
                            'refuerzo_guardia_anterior_id' => null,
                            'es_cambio' => false,
                            'es_sancion' => false,
                        ]);
                    });
            }

            $myGuardia = Guardia::find($guardiaIdForGuardiaUser);
            if (!$myGuardia) {
                abort(403, 'Cuenta de guardia con guardia inválida.');
            }

            $isMyGuardiaOnDuty = (bool) ($activeGuardia && (int) $activeGuardia->id === (int) $myGuardia->id);

            $hasAttendanceSavedToday = GuardiaAttendanceRecord::where('guardia_id', $myGuardia->id)
                ->whereDate('date', Carbon::today()->toDateString())
                ->exists();

            // Compat: si existen reemplazos legacy (users.job_replacement_id), los migramos a reemplazos_bomberos
            // para soportar UI y "deshacer" sin depender del esquema antiguo.
            $legacyReplacementUsers = User::query()
                ->whereNotNull('job_replacement_id')
                ->where('attendance_status', 'reemplazo')
                ->where(function ($q) use ($now) {
                    $q->whereNull('replacement_until')
                        ->orWhere('replacement_until', '>', $now);
                })
                ->get();

            if ($legacyReplacementUsers->isNotEmpty()) {
                foreach ($legacyReplacementUsers as $legacyReplacerUser) {
                    $legacyOriginalUserId = (int) $legacyReplacerUser->job_replacement_id;
                    if (!$legacyOriginalUserId) {
                        continue;
                    }

                    $replacementFirefighterId = MapaBomberoUsuarioLegacy::where('user_id', $legacyReplacerUser->id)->value('firefighter_id');
                    $originalFirefighterId = MapaBomberoUsuarioLegacy::where('user_id', $legacyOriginalUserId)->value('firefighter_id');

                    if (!$replacementFirefighterId || !$originalFirefighterId) {
                        continue;
                    }

                    $already = ReemplazoBombero::where('estado', 'activo')
                        ->where('bombero_titular_id', $originalFirefighterId)
                        ->exists();
                    if ($already) {
                        continue;
                    }

                    $replacementPrevGuardiaId = Bombero::where('id', $replacementFirefighterId)->value('guardia_id');

                    DB::transaction(function () use ($guardiaIdForGuardiaUser, $legacyReplacerUser, $replacementFirefighterId, $originalFirefighterId, $replacementPrevGuardiaId) {
                        ReemplazoBombero::create([
                            'guardia_id' => $guardiaIdForGuardiaUser,
                            'bombero_titular_id' => $originalFirefighterId,
                            'bombero_reemplazante_id' => $replacementFirefighterId,
                            'inicio' => $legacyReplacerUser->updated_at ?? now(),
                            'fin' => $legacyReplacerUser->replacement_until,
                            'estado' => 'activo',
                            'notas' => json_encode([
                                'replacement_previous_guardia_id' => $replacementPrevGuardiaId,
                            ]),
                        ]);
                    });
                }
            }

            $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
                ->where('estado', 'activo')
                ->where('guardia_id', $guardiaIdForGuardiaUser)
                ->get();
            $replacementByOriginal = $activeReplacements->keyBy(fn (ReemplazoBombero $r) => (int) $r->bombero_titular_id);
            $replacementByReplacement = $activeReplacements->keyBy(fn (ReemplazoBombero $r) => (int) $r->bombero_reemplazante_id);

            // Cargar personal de la guardia (excluyendo la propia cuenta de gestión)
            $myStaff = Bombero::where('guardia_id', $guardiaIdForGuardiaUser)
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get();

            $replacementCandidates = Bombero::query()
                ->where(function ($q) use ($guardiaIdForGuardiaUser) {
                    $q->whereNull('guardia_id')
                        ->orWhere('guardia_id', '!=', $guardiaIdForGuardiaUser);
                })
                ->where(function ($q) {
                    $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
                })
                ->whereNotIn('id', $activeReplacements->pluck('bombero_reemplazante_id')->values()->toArray())
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get()
                ->unique(function ($f) {
                    $rut = trim((string) ($f->rut ?? ''));
                    if ($rut !== '') {
                        return mb_strtolower($rut);
                    }

                    return (int) $f->id;
                })
                ->values();

            $academyLeaderIds = [];
            if ($currentShift) {
                $shiftUsers = ShiftUser::query()
                    ->where('shift_id', $currentShift->id)
                    ->whereNull('end_time')
                    ->get(['user_id', 'firefighter_id']);

                $directUserIds = $shiftUsers->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->values()->toArray();

                $firefighterIds = $shiftUsers->pluck('firefighter_id')
                    ->filter()
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $mappedUserIds = !empty($firefighterIds)
                    ? MapaBomberoUsuarioLegacy::query()
                        ->whereIn('firefighter_id', $firefighterIds)
                        ->pluck('user_id')
                        ->filter()
                        ->map(fn ($v) => (int) $v)
                        ->values()
                        ->toArray()
                    : [];

                $academyLeaderIds = array_values(array_unique(array_merge($directUserIds, $mappedUserIds)));

                $academyLeaderFirefighterIds = $shiftUsers->pluck('firefighter_id')
                    ->filter()
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $academyLeadersFirefighters = !empty($academyLeaderFirefighterIds)
                    ? Bombero::query()
                        ->where('guardia_id', $guardiaIdForGuardiaUser)
                        ->whereIn('id', $academyLeaderFirefighterIds)
                        ->where(function ($q) {
                            $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
                        })
                        ->orderBy('apellido_paterno')
                        ->orderBy('nombres')
                        ->get()
                    : collect();
            }

            if (($academyLeadersFirefighters ?? collect())->isEmpty()) {
                $academyLeadersFirefighters = Bombero::query()
                    ->where('guardia_id', $guardiaIdForGuardiaUser)
                    ->whereIn('estado_asistencia', ['constituye', 'reemplazo'])
                    ->where(function ($q) {
                        $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
                    })
                    ->orderBy('apellido_paterno')
                    ->orderBy('nombres')
                    ->get();
            }

            $academyLeaders = User::query()
                ->where('guardia_id', $guardiaIdForGuardiaUser)
                ->whereIn('role', ['bombero', 'jefe_guardia'])
                ->when(!empty($academyLeaderIds), function ($q) use ($academyLeaderIds) {
                    $q->whereIn('id', $academyLeaderIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->orderBy('last_name_paternal')
                ->orderBy('name')
                ->get();

            $guardiaNovelties = Novelty::with('user')->latest()->paginate(3);
            $academies = Novelty::with('user')->where('type', 'Academia')->latest()->take(5)->get();

            $birthdays = $computeUpcomingBirthdays(
                $myStaff->filter(function ($b) {
                    return (bool) ($b->fecha_nacimiento ?? null);
                })->values()
            );

            $birthdaysMonthCount = $myStaff
                ->filter(function ($b) {
                    return (bool) ($b->fecha_nacimiento ?? null);
                })
                ->filter(function ($b) {
                    return (int) $b->fecha_nacimiento->month === (int) now()->month;
                })
                ->count();

            $birthdaysThisMonth = $myStaff
                ->filter(function ($b) {
                    return (bool) ($b->fecha_nacimiento ?? null);
                })
                ->filter(function ($b) {
                    return (int) $b->fecha_nacimiento->month === (int) now()->month;
                })
                ->sortBy(function ($b) {
                    return (int) $b->fecha_nacimiento->day;
                })
                ->values();
        }

        return view('dashboard', compact(
            'totalBeds',
            'occupiedBeds',
            'availableBeds',
            'activeGuardia',
            'currentShift',
            'globalCurrentShift',
            'globalOnDutyUserIds',
            'globalOnDutyFirefighterIds',
            'novelties',
            'guardiaNovelties',
            'academies',
            'birthdays',
            'birthdaysMonthCount',
            'birthdaysThisMonth',
            'myGuardia',
            'myStaff',
            'replacementCandidates',
            'replacementByOriginal',
            'replacementByReplacement',
            'isMyGuardiaOnDuty',
            'hasAttendanceSavedToday',
            'academyLeaders',
            'academyLeadersFirefighters',
            'guardiaTz'
        ));
    }

    public function guardiaSnapshot(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        if ($user->role !== 'guardia') {
            return response()->json(['ok' => false], 403);
        }

        $guardiaId = $user->guardia_id;
        if (!$guardiaId) {
            $guardiaId = Guardia::whereRaw('lower(name) = ?', [strtolower($user->name)])->value('id');
        }

        if (!$guardiaId) {
            return response()->json(['ok' => false], 403);
        }

        $latestNovelty = Novelty::query()->latest('updated_at')->value('updated_at');
        $latestBombero = Bombero::query()->where('guardia_id', $guardiaId)->latest('updated_at')->value('updated_at');
        $latestReplacement = ReemplazoBombero::query()->latest('updated_at')->value('updated_at');
        $attendanceSavedAt = GuardiaAttendanceRecord::query()
            ->where('guardia_id', $guardiaId)
            ->whereDate('date', Carbon::today()->toDateString())
            ->value('saved_at');

        return response()->json([
            'ok' => true,
            'guardia_id' => (int) $guardiaId,
            'latest_novelty_at' => $latestNovelty?->toISOString(),
            'latest_bombero_at' => $latestBombero?->toISOString(),
            'latest_replacement_at' => $latestReplacement?->toISOString(),
            'attendance_saved_at' => $attendanceSavedAt?->toISOString(),
            'ts' => now()->toISOString(),
        ]);
    }

    public function camas()
    {
        $user = auth()->user();
        $now = now();

        ReplacementService::expire($now);
        $beds = Bed::with(['currentAssignment.firefighter', 'currentAssignment.user'])->get();
        
        // Auto-fix: Corregir camas marcadas como ocupadas pero sin asignación activa
        // Esto previene el error "Attempt to read property id on null" en la vista
        foreach ($beds as $bed) {
            if ($bed->status === 'occupied' && !$bed->currentAssignment) {
                $bed->update(['status' => 'available']);
                $bed->status = 'available'; // Actualizar en memoria para esta request
            }
        }
        
        $assignedFirefighterIds = \App\Models\BedAssignment::whereNull('released_at')
            ->whereNotNull('firefighter_id')
            ->pluck('firefighter_id')
            ->toArray();

        $usersQuery = Bombero::query()
            ->whereNotIn('id', $assignedFirefighterIds);

        // Si el usuario tiene guardia asignada, filtrar voluntarios de su guardia
        if ($user->guardia_id) {
            $usersQuery->where('guardia_id', $user->guardia_id);
        } else {
            // Si es Admin, filtrar por la Guardia Activa de la semana
            $activeGuardia = $this->resolveActiveGuardia($now);
            if ($activeGuardia) {
                $usersQuery->where('guardia_id', $activeGuardia->id);
            }
        }

        $users = $usersQuery->orderBy('nombres')->orderBy('apellido_paterno')->get();
        
        return view('camas', compact('beds', 'users'));
    }

    public function guardia()
    {
        $shift = Shift::with(['leader', 'users.user', 'users.firefighter', 'users.replacedFirefighter'])->where('status', 'active')->latest()->first();
        $now = now();
        $users = Bombero::query()
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get(); // Para asignar
        return view('guardia', compact('shift', 'users'));
    }
}
