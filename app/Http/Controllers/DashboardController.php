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
use App\Services\ReplacementService;
use Carbon\Carbon;

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

        $novelties = Novelty::with('user')->latest()->take(5)->get();

        // Próximos cumpleaños (Lógica mejorada: próximos 5 sin importar si cambia de mes)
        // Se obtienen todos, se calcula el día del año y se ordena. 
        // Para simplificar en SQL:
        $birthdays = User::whereNotNull('birthdate')
            ->get()
            ->filter(function($user) {
                // Filtrar solo los que tienen fecha válida
                return $user->birthdate; 
            })
            ->map(function($user) {
                // Calcular fecha de cumpleaños este año
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

        if ($user->role === 'guardia' && $user->guardia_id) {
            $myGuardia = $user->guardia;
            // Cargar personal de la guardia (excluyendo la propia cuenta de gestión)
            $myStaff = User::where('guardia_id', $user->guardia_id)
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($now) {
                    $q->whereNull('replacement_until')
                      ->orWhere('replacement_until', '>', $now);
                })
                ->with(['replacedBy']) // Cargar relación para filtrar conteo
                ->get();
            
            // Filtrar novedades: Solo mostrar novedades creadas por miembros de esta guardia
            $staffIds = $myStaff->pluck('id');
            $staffIds->push($user->id);
            
            $novelties = Novelty::whereIn('user_id', $staffIds)->latest()->take(5)->get();
        }

        return view('dashboard', compact(
            'totalBeds', 
            'occupiedBeds', 
            'availableBeds', 
            'currentShift', 
            'novelties', 
            'birthdays',
            'myGuardia',
            'myStaff',
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
