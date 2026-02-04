<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bed;
use App\Models\Shift;
use App\Models\User;
use App\Models\Novelty;
use App\Models\BedAssignment;
use App\Models\Firefighter;
use App\Models\FirefighterReplacement;
use App\Models\FirefighterUserLegacyMap;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\GuardiaAttendanceRecord;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
        $isMyGuardiaOnDuty = false;
        $hasAttendanceSavedToday = false;

        // Próximos cumpleaños (Lógica mejorada: próximos 5 sin importar si cambia de mes)
        // Se obtienen todos, se calcula el día del año y se ordena. 
        // Para simplificar en SQL:
        $birthdays = User::whereNotNull('birthdate')
            ->get()
            ->filter(function($user) {
                return (bool) $user->birthdate;
            })
            ->map(function($user) {
                $birthdayThisYear = $user->birthdate->copy()->year(now()->year);
                if ($birthdayThisYear->isPast() && !$birthdayThisYear->isToday()) {
                    $birthdayThisYear->addYear();
                }
                $user->next_birthday = $birthdayThisYear;
                return $user;
            })
            ->sortBy('next_birthday')
            ->take(5);

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

            $myGuardia = Guardia::find($guardiaIdForGuardiaUser);
            if (!$myGuardia) {
                abort(403, 'Cuenta de guardia con guardia inválida.');
            }

            $isMyGuardiaOnDuty = (bool) ($activeGuardia && (int) $activeGuardia->id === (int) $myGuardia->id);

            $hasAttendanceSavedToday = GuardiaAttendanceRecord::where('guardia_id', $myGuardia->id)
                ->whereDate('date', Carbon::today()->toDateString())
                ->exists();

            // Compat: si existen reemplazos legacy (users.job_replacement_id), los migramos a firefighter_replacements
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

                    $replacementFirefighterId = (int) FirefighterUserLegacyMap::where('user_id', $legacyReplacerUser->id)->value('firefighter_id');
                    $originalFirefighterId = (int) FirefighterUserLegacyMap::where('user_id', $legacyOriginalUserId)->value('firefighter_id');

                    if (!$replacementFirefighterId || !$originalFirefighterId) {
                        continue;
                    }

                    $already = FirefighterReplacement::where('status', 'active')
                        ->where('original_firefighter_id', $originalFirefighterId)
                        ->exists();
                    if ($already) {
                        continue;
                    }

                    $replacementPrevGuardiaId = Firefighter::where('id', $replacementFirefighterId)->value('guardia_id');

                    DB::transaction(function () use ($guardiaIdForGuardiaUser, $legacyReplacerUser, $replacementFirefighterId, $originalFirefighterId, $replacementPrevGuardiaId) {
                        FirefighterReplacement::create([
                            'guardia_id' => $guardiaIdForGuardiaUser,
                            'original_firefighter_id' => $originalFirefighterId,
                            'replacement_firefighter_id' => $replacementFirefighterId,
                            'starts_at' => $legacyReplacerUser->updated_at ?? now(),
                            'ends_at' => $legacyReplacerUser->replacement_until,
                            'status' => 'active',
                            'notes' => json_encode([
                                'replacement_previous_guardia_id' => $replacementPrevGuardiaId,
                            ]),
                        ]);
                    });
                }
            }

            $activeReplacements = FirefighterReplacement::with(['originalFirefighter', 'replacementFirefighter'])
                ->where('status', 'active')
                ->get();
            $replacementByOriginal = $activeReplacements->keyBy('original_firefighter_id');
            $replacementByReplacement = $activeReplacements->keyBy('replacement_firefighter_id');

            // Cargar personal de la guardia (excluyendo la propia cuenta de gestión)
            $myStaff = Firefighter::where('guardia_id', $guardiaIdForGuardiaUser)
                ->orderBy('last_name_paternal')
                ->orderBy('name')
                ->get();

            $replacementCandidates = Firefighter::query()
                ->where(function ($q) use ($guardiaIdForGuardiaUser) {
                    $q->whereNull('guardia_id')
                        ->orWhere('guardia_id', '!=', $guardiaIdForGuardiaUser);
                })
                ->whereNotIn('id', $activeReplacements->pluck('replacement_firefighter_id')->values()->toArray())
                ->when(!empty($globalOnDutyFirefighterIds), function ($q) use ($globalOnDutyFirefighterIds) {
                    $q->whereNotIn('id', $globalOnDutyFirefighterIds);
                })
                ->orderBy('last_name_paternal')
                ->orderBy('name')
                ->get();

            $academyLeaders = User::where('guardia_id', $guardiaIdForGuardiaUser)
                ->whereIn('role', ['bombero', 'jefe_guardia'])
                ->orderBy('last_name_paternal')
                ->orderBy('name')
                ->get();

            $guardiaNovelties = Novelty::with('user')->latest()->paginate(3);
            $academies = Novelty::with('user')->where('type', 'Academia')->latest()->take(5)->get();

            $birthdays = $myStaff
                ->filter(function($u) {
                    return (bool) $u->birthdate;
                })
                ->map(function($u) {
                    $birthdayThisYear = $u->birthdate->copy()->year(now()->year);
                    if ($birthdayThisYear->isPast() && !$birthdayThisYear->isToday()) {
                        $birthdayThisYear->addYear();
                    }
                    $u->next_birthday = $birthdayThisYear;
                    return $u;
                })
                ->sortBy('next_birthday')
                ->take(5);
        }

        return view('dashboard', compact(
            'totalBeds', 
            'occupiedBeds', 
            'availableBeds', 
            'currentShift', 
            'novelties', 
            'guardiaNovelties',
            'academies',
            'academyLeaders',
            'isMyGuardiaOnDuty',
            'hasAttendanceSavedToday',
            'birthdays',
            'myGuardia',
            'myStaff',
            'replacementCandidates',
            'replacementByOriginal',
            'replacementByReplacement',
            'activeGuardia'
        ));
    }

    public function camas()
    {
        $user = auth()->user();
        $now = now();

        ReplacementService::expire($now);
        $beds = Bed::with('currentAssignment.user')->get();
        
        // Auto-fix: Corregir camas marcadas como ocupadas pero sin asignación activa
        // Esto previene el error "Attempt to read property id on null" en la vista
        foreach ($beds as $bed) {
            if ($bed->status === 'occupied' && !$bed->currentAssignment) {
                $bed->update(['status' => 'available']);
                $bed->status = 'available'; // Actualizar en memoria para esta request
            }
        }
        
        // Obtener IDs de usuarios que YA tienen una cama asignada
        $assignedUserIds = \App\Models\BedAssignment::whereNull('released_at')->pluck('user_id')->toArray();
        
        $usersQuery = User::where('role', '!=', 'guardia')
                          ->whereNotIn('id', $assignedUserIds)
                          ->where(function ($q) use ($now) {
                              $q->whereNull('replacement_until')
                                ->orWhere('replacement_until', '>', $now);
                          });

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

        $users = $usersQuery->orderBy('name')->get();
        
        return view('camas', compact('beds', 'users'));
    }

    public function guardia()
    {
        $shift = Shift::with(['leader', 'users.user'])->where('status', 'active')->latest()->first();
        $now = now();
        $users = User::where('role', '!=', 'guardia')
            ->where(function ($q) use ($now) {
                $q->whereNull('replacement_until')
                  ->orWhere('replacement_until', '>', $now);
            })
            ->orderBy('name')
            ->get(); // Para asignar
        return view('guardia', compact('shift', 'users'));
    }
}
