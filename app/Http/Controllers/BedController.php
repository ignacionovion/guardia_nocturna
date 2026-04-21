<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $query = Bed::query()->with(['currentAssignment.volunteer']);

        // Búsqueda (sanitizada)
        $search = trim($request->get('search', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
  ->orWhereRaw('COALESCE(number, name) LIKE ?', ["%{$search}%"])
  ->orWhere('room', 'like', "%{$search}%");
            });
        }

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('room')) {
            $query->byRoom($request->room);
        }

        // Ordenamiento dinámico (validado)
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'number', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // ORDEN BASE: SIEMPRE room ASC (NULLS LAST), luego sort dinámico
        $query->orderByRaw('CASE WHEN room IS NULL THEN 1 ELSE 0 END, room ASC');
        $query->orderBy($sortBy, $sortDirection);
        
        // Ordenamiento terciario por nombre si no es el principal
        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $beds = $query->get();

        // Obtener lista de sectores/piezas para filtro
        $rooms = Bed::select('room')
            ->distinct()
            ->whereNotNull('room')
            ->orderBy('room')
            ->pluck('room');

        // Obtener guardia activa para mostrar info
        $activeGuardia = \App\Models\Guardia::where('is_active_week', true)->first();

        // Calcular estadísticas generales
        $allBeds = Bed::all();
        $stats = [
            'total' => $allBeds->count(),
            'available' => $allBeds->where('status', 'available')->count(),
            'occupied' => $allBeds->where('status', 'occupied')->count(),
            'maintenance' => $allBeds->where('status', 'maintenance')->count(),
            'disabled' => $allBeds->where('status', 'disabled')->count(),
            'occupancy_percentage' => $allBeds->count() > 0 
                ? round(($allBeds->where('status', 'occupied')->count() / $allBeds->count()) * 100, 1)
                : 0,
        ];

        // Estadísticas por género
        $statsByGender = [
            'male' => $allBeds->where('gender', 'male')->count(),
            'female' => $allBeds->where('gender', 'female')->count(),
            'mixed' => $allBeds->where('gender', 'mixed')->count(),
        ];

        // Estadísticas por room/sector
        $statsByRoom = $allBeds->groupBy('room')->map(function ($roomBeds, $room) {
            return [
                'room' => $room ?: 'Sin sector',
                'total' => $roomBeds->count(),
                'occupied' => $roomBeds->where('status', 'occupied')->count(),
                'available' => $roomBeds->where('status', 'available')->count(),
            ];
        })->sortBy('room')->values();

        return view('admin.beds.index', compact('beds', 'activeGuardia', 'rooms', 'stats', 'statsByGender', 'statsByRoom'));
    }

    public function create()
    {
        return view('admin.beds.create', [
            'beds_plan_usage' => PlanService::usageLabel('beds'),
        ]);
    }

    public function store(Request $request)
    {
        PlanService::assertCanIncrement('beds');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,mixed',
            'status' => 'required|in:available,occupied,maintenance,disabled',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        Bed::create($validated);

        return redirect()->route('admin.beds.index')
            ->with('success', 'Cama creada exitosamente.');
    }

    public function edit(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        return view('admin.beds.edit', compact('bed'));
    }

    public function update(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,mixed',
            'status' => 'required|in:available,occupied,maintenance,disabled',
            'notes' => 'nullable|string',
        ]);

        // Compatibilidad legacy: sincronizar campos antiguos
        $validated['number'] = $validated['name'];
        if (isset($validated['notes'])) {
            $validated['description'] = $validated['notes'];
        }

        $bed->update($validated);

        return redirect()->route('admin.beds.index')
            ->with('success', 'Cama actualizada exitosamente.');
    }

    public function changeStatus(Request $request, Bed $bed)
    {
        $bed_id = $request->route('bed');
        if ($bed_id && ! ($bed instanceof Bed)) {
            $bed = Bed::findOrFail($bed_id);
        }

        $validated = $request->validate([
            'status' => 'required|in:available,maintenance,disabled',
        ]);

        if ($bed->status === 'occupied') {
            return back()->with('error', 'No se puede cambiar el estado de una cama ocupada.');
        }

        $bed->update(['status' => $validated['status']]);

        $labels = ['available' => 'disponible', 'maintenance' => 'en mantención', 'disabled' => 'deshabilitada'];
        return back()->with('success', "Cama '{$bed->name}' marcada como {$labels[$validated['status']]}.");
    }

    public function showQr(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        return view('admin.beds.qr', compact('bed'));
    }

    public function printQr(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        return view('admin.beds.qr-print', compact('bed'));
    }
}
