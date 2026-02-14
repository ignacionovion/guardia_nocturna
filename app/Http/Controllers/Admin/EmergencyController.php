<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\EmergencyKey;
use App\Models\EmergencyUnit;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use App\Models\Bombero;
use App\Models\MapaBomberoUsuarioLegacy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EmergencyController extends Controller
{
    private function resolveActiveGuardia(Carbon $now): ?Guardia
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

    private function resolveActiveShiftForUser(User $user): ?Shift
    {
        $guardiaId = $user->guardia_id;

        if (!$guardiaId && $user->role === 'super_admin') {
            $activeGuardia = $this->resolveActiveGuardia(Carbon::now());
            $guardiaId = $activeGuardia?->id;
        }

        $query = Shift::with(['leader'])->where('status', 'active');

        // Preferimos filtrar por guardia via el líder del turno.
        // Hay datos legacy donde shift_users.guardia_id puede venir null y rompe el filtro.
        if ($guardiaId) {
            $query->whereHas('leader', function ($q) use ($guardiaId) {
                $q->where('guardia_id', $guardiaId);
            });
        }

        $shift = $query->latest()->first();

        // Fallback: en algunos datos legacy el líder del turno puede no tener guardia_id,
        // pero los ShiftUser sí tienen firefighter_id. Buscamos turnos donde haya personal activo
        // cuya guardia coincida con la del usuario.
        if (!$shift && $guardiaId) {
            $shift = Shift::with(['leader'])
                ->where('status', 'active')
                ->whereHas('users', function ($q) use ($guardiaId) {
                    $q->where(function ($q2) {
                        $q2->whereNull('end_time')
                            ->orWhere('end_time', '>', now());
                    })
                    ->whereHas('firefighter', function ($q3) use ($guardiaId) {
                        $q3->where('guardia_id', $guardiaId);
                    });
                })
                ->latest()
                ->first();
        }

        if (!$shift) {
            $shift = Shift::with(['leader'])->where('status', 'active')->latest()->first();
        }

        return $shift;
    }

    private function resolveOnDutyUsers(?Shift $shift, ?User $authUser)
    {
        if (!$shift) {
            return collect();
        }

        $guardiaId = $authUser?->guardia_id;
        if (!$guardiaId && $authUser?->role === 'super_admin') {
            $guardiaId = $this->resolveActiveGuardia(Carbon::now())?->id;
        }

        $shiftUsers = ShiftUser::with(['firefighter', 'user'])
            ->where('shift_id', $shift->id)
            ->where(function ($q) {
                $q->whereNull('end_time')
                    ->orWhere('end_time', '>', now());
            })
            ->get();

        if ($shiftUsers->isEmpty() && $shift->status === 'active') {
            $shiftUsers = ShiftUser::with(['firefighter', 'user'])
                ->where('shift_id', $shift->id)
                ->where('present', true)
                ->get();
        }

        $firefighters = $shiftUsers
            ->filter(fn ($su) => (bool) $su->firefighter)
            ->map(fn ($su) => $su->firefighter);

        // Fallback: en datos legacy puede no existir firefighter_id en shift_users.
        // En ese caso, ofrecemos una lista razonable para "A cargo" basada en la dotación
        // marcada como presente en la guardia del usuario.
        if ($firefighters->isEmpty() && $guardiaId) {
            return Bombero::query()
                ->where('guardia_id', $guardiaId)
                ->whereIn('estado_asistencia', ['constituye', 'reemplazo'])
                ->where(function ($q) {
                    $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
                })
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get();
        }

        if ($guardiaId) {
            $firefighters = $firefighters->where('guardia_id', $guardiaId);
        } elseif ($shift->leader?->guardia_id) {
            $firefighters = $firefighters->where('guardia_id', $shift->leader->guardia_id);
        }

        if ($firefighters->isEmpty() && $guardiaId) {
            return Bombero::query()
                ->where('guardia_id', $guardiaId)
                ->whereIn('estado_asistencia', ['constituye', 'reemplazo'])
                ->where(function ($q) {
                    $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
                })
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get();
        }

        $firefighters = $firefighters->reject(function ($f) {
            return (bool) ($f->fuera_de_servicio ?? false);
        });

        return $firefighters->sortBy('apellido_paterno')->values();
    }

    public function index(Request $request)
    {
        $query = Emergency::with(['key', 'units', 'guardia', 'officerInCharge', 'officerInChargeFirefighter'])
            ->orderByDesc('dispatched_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('key', function ($k) use ($search) {
                    $k->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->orWhere('details', 'like', "%{$search}%");
            });
        }

        $emergencies = $query->paginate(20);

        return view('admin.emergencies.index', compact('emergencies'));
    }

    public function show(string $id)
    {
        return redirect()->route('admin.emergencies.edit', $id);
    }

    public function create(Request $request)
    {
        $keys = EmergencyKey::orderBy('code')->get();
        $units = EmergencyUnit::orderBy('name')->get();

        $authUser = $request->user();
        $shift = $authUser ? $this->resolveActiveShiftForUser($authUser) : null;
        $onDutyUsers = $this->resolveOnDutyUsers($shift, $authUser);

        return view('admin.emergencies.create', compact('keys', 'units', 'shift', 'onDutyUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'emergency_key_id' => 'required|exists:emergency_keys,id',
            'dispatched_at' => 'required|date_format:Y-m-d\TH:i',
            'arrived_at' => 'nullable|date_format:Y-m-d\TH:i',
            'details' => 'nullable|string',
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'exists:emergency_units,id',
            'officer_in_charge_firefighter_id' => 'nullable|exists:bomberos,id',
        ]);

        $dispatchedAt = Carbon::createFromFormat('Y-m-d\TH:i', $validated['dispatched_at']);
        $arrivedAt = isset($validated['arrived_at']) && $validated['arrived_at'] !== null && $validated['arrived_at'] !== ''
            ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['arrived_at'])
            : null;

        if ($arrivedAt && $arrivedAt->lt($dispatchedAt)) {
            return back()
                ->withErrors(['arrived_at' => 'La hora de llegada debe ser igual o posterior a la hora de salida.'])
                ->withInput();
        }

        $authUser = $request->user();
        $shift = $authUser ? $this->resolveActiveShiftForUser($authUser) : null;

        $guardiaId = $authUser?->guardia_id;
        if (!$guardiaId && $authUser?->role === 'super_admin') {
            $guardiaId = $this->resolveActiveGuardia(Carbon::now())?->id;
        }
        if (!$guardiaId && $shift) {
            $guardiaId = ShiftUser::where('shift_id', $shift->id)
                ->whereNotNull('guardia_id')
                ->value('guardia_id');
        }

        $emergency = Emergency::create([
            'emergency_key_id' => $validated['emergency_key_id'],
            'dispatched_at' => $dispatchedAt,
            'arrived_at' => $arrivedAt,
            'details' => $validated['details'] ?? null,
            'shift_id' => $shift?->id,
            'guardia_id' => $guardiaId,
            'officer_in_charge_firefighter_id' => $validated['officer_in_charge_firefighter_id'] ?? null,
            'officer_in_charge_user_id' => ($validated['officer_in_charge_firefighter_id'] ?? null)
                ? MapaBomberoUsuarioLegacy::where('firefighter_id', (int) $validated['officer_in_charge_firefighter_id'])->value('user_id')
                : null,
            'created_by' => $authUser?->id,
        ]);

        $emergency->units()->sync($validated['unit_ids'] ?? []);

        return redirect()->route('admin.emergencies.index')->with('success', 'Emergencia registrada correctamente.');
    }

    public function edit(string $id, Request $request)
    {
        $emergency = Emergency::with(['units'])->findOrFail($id);

        $keys = EmergencyKey::orderBy('code')->get();
        $units = EmergencyUnit::orderBy('name')->get();

        $authUser = $request->user();
        $shift = $authUser ? $this->resolveActiveShiftForUser($authUser) : null;
        $onDutyUsers = $this->resolveOnDutyUsers($shift, $authUser);

        return view('admin.emergencies.edit', compact('emergency', 'keys', 'units', 'shift', 'onDutyUsers'));
    }

    public function update(Request $request, string $id)
    {
        $emergency = Emergency::findOrFail($id);

        $validated = $request->validate([
            'emergency_key_id' => 'required|exists:emergency_keys,id',
            'dispatched_at' => 'required|date_format:Y-m-d\TH:i',
            'arrived_at' => 'nullable|date_format:Y-m-d\TH:i',
            'details' => 'nullable|string',
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'exists:emergency_units,id',
            'officer_in_charge_firefighter_id' => 'nullable|exists:bomberos,id',
        ]);

        $dispatchedAt = Carbon::createFromFormat('Y-m-d\TH:i', $validated['dispatched_at']);
        $arrivedAt = isset($validated['arrived_at']) && $validated['arrived_at'] !== null && $validated['arrived_at'] !== ''
            ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['arrived_at'])
            : null;

        if ($arrivedAt && $arrivedAt->lt($dispatchedAt)) {
            return back()
                ->withErrors(['arrived_at' => 'La hora de llegada debe ser igual o posterior a la hora de salida.'])
                ->withInput();
        }

        $emergency->update([
            'emergency_key_id' => $validated['emergency_key_id'],
            'dispatched_at' => $dispatchedAt,
            'arrived_at' => $arrivedAt,
            'details' => $validated['details'] ?? null,
            'officer_in_charge_firefighter_id' => $validated['officer_in_charge_firefighter_id'] ?? null,
            'officer_in_charge_user_id' => ($validated['officer_in_charge_firefighter_id'] ?? null)
                ? MapaBomberoUsuarioLegacy::where('firefighter_id', (int) $validated['officer_in_charge_firefighter_id'])->value('user_id')
                : null,
        ]);

        $emergency->units()->sync($validated['unit_ids'] ?? []);

        return redirect()->route('admin.emergencies.index')->with('success', 'Emergencia actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $emergency = Emergency::findOrFail($id);
        $emergency->delete();

        return redirect()->route('admin.emergencies.index')->with('success', 'Emergencia eliminada correctamente.');
    }
}
