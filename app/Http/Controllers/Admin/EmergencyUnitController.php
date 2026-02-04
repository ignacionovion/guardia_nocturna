<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmergencyUnitController extends Controller
{
    public function index(Request $request)
    {
        $query = EmergencyUnit::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $units = $query->paginate(30);

        return view('admin.emergency_units.index', compact('units'));
    }

    public function show(string $id)
    {
        return redirect()->route('admin.emergency-units.edit', $id);
    }

    public function create()
    {
        return view('admin.emergency_units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:emergency_units,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        EmergencyUnit::create($validated);

        return redirect()->route('admin.emergency-units.index')->with('success', 'Unidad creada correctamente.');
    }

    public function edit(string $id)
    {
        $unit = EmergencyUnit::findOrFail($id);
        return view('admin.emergency_units.edit', compact('unit'));
    }

    public function update(Request $request, string $id)
    {
        $unit = EmergencyUnit::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('emergency_units', 'name')->ignore($unit->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $unit->update($validated);

        return redirect()->route('admin.emergency-units.index')->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $unit = EmergencyUnit::findOrFail($id);
        $unit->delete();

        return redirect()->route('admin.emergency-units.index')->with('success', 'Unidad eliminada correctamente.');
    }
}
