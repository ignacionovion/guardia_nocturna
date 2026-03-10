<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use Illuminate\Http\Request;

class BodyController extends Controller
{
    public function index()
    {
        $bodies = Body::withCount('tenants')->latest()->paginate(20);
        return view('central.bodies.index', compact('bodies'));
    }

    public function create()
    {
        return view('central.bodies.form', ['body' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        Body::create($validated);

        return redirect()->route('central.bodies.index')->with('success', 'Cuerpo creado correctamente.');
    }

    public function edit(Body $body)
    {
        return view('central.bodies.form', compact('body'));
    }

    public function update(Request $request, Body $body)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        $body->update($validated);

        return redirect()->route('central.bodies.index')->with('success', 'Cuerpo actualizado correctamente.');
    }

    public function destroy(Body $body)
    {
        if ($body->tenants()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un cuerpo con compañías asociadas.');
        }

        $body->delete();
        return redirect()->route('central.bodies.index')->with('success', 'Cuerpo eliminado.');
    }
}
