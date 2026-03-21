<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $query = Bed::query()->with(['currentAssignment.volunteer']);

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        $beds = $query->orderBy('name')->get();

        // Obtener guardia activa para mostrar info
        $activeGuardia = \App\Models\Guardia::where('is_active_week', true)->first();

        return view('admin.beds.index', compact('beds', 'activeGuardia'));
    }

    public function create()
    {
        return view('admin.beds.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,mixed',
            'status' => 'required|in:available,occupied,maintenance,disabled',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        // Compatibilidad legacy: sincronizar campos antiguos
        $validated['number'] = $validated['name'];
        if (isset($validated['notes'])) {
            $validated['description'] = $validated['notes'];
        }

        Bed::create($validated);

        return redirect()->route('admin.beds.index')
            ->with('success', 'Cama creada exitosamente.');
    }

    public function edit(Bed $bed)
    {
        return view('admin.beds.edit', compact('bed'));
    }

    public function update(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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

    public function showQr(Bed $bed)
    {
        return view('admin.beds.qr', compact('bed'));
    }

    public function printQr(Bed $bed)
    {
        return view('admin.beds.qr-print', compact('bed'));
    }
}
