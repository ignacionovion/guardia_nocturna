<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planilla;
use Illuminate\Http\Request;

class PlanillaController extends Controller
{
    private const UNIDADES = ['BR-3', 'B-3', 'RX-3'];
    private const ESTADO_EN_EDICION = 'en_edicion';
    private const ESTADO_FINALIZADO = 'finalizado';

    public function index(Request $request)
    {
        $unidad = $request->string('unidad')->toString();
        $unidad = $unidad !== '' ? $unidad : null;

        $query = Planilla::query()->with('creador')->latest('fecha_revision');
        if ($unidad && in_array($unidad, self::UNIDADES, true)) {
            $query->where('unidad', $unidad);
        }

        $planillas = $query->paginate(20)->withQueryString();

        return view('admin.planillas.index', [
            'planillas' => $planillas,
            'unidades' => self::UNIDADES,
            'unidadSeleccionada' => $unidad,
        ]);
    }

    public function create(Request $request)
    {
        $unidad = $request->string('unidad')->toString();
        if ($unidad === '') {
            $unidad = null;
        }
        if ($unidad !== null && !in_array($unidad, self::UNIDADES, true)) {
            $unidad = self::UNIDADES[0];
        }

        return view('admin.planillas.create', [
            'unidad' => $unidad,
            'unidades' => self::UNIDADES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', self::UNIDADES)],
            'fecha_revision' => ['required', 'date'],
            'data' => ['nullable', 'array'],
        ]);

        $estado = $request->has('guardar_finalizar') ? self::ESTADO_FINALIZADO : self::ESTADO_EN_EDICION;

        $planilla = Planilla::create([
            'unidad' => $validated['unidad'],
            'fecha_revision' => $validated['fecha_revision'],
            'created_by' => (int) $request->user()->id,
            'data' => $validated['data'] ?? [],
            'estado' => $estado,
        ]);

        if ($request->has('guardar_continuar')) {
            return redirect()->route('admin.planillas.edit', $planilla)->with('success', 'Planilla guardada. Puedes continuar después.');
        }

        if ($request->has('guardar_finalizar')) {
            return redirect()->route('admin.planillas.show', $planilla)->with('success', 'Planilla guardada y finalizada.');
        }

        return redirect()->route('admin.planillas.show', $planilla)->with('success', 'Planilla guardada correctamente.');
    }

    public function edit(Planilla $planilla)
    {
        if ($planilla->estado === self::ESTADO_FINALIZADO) {
            return redirect()->route('admin.planillas.show', $planilla)->with('warning', 'Esta planilla está finalizada y no se puede editar.');
        }

        $planilla->load('creador');

        return view('admin.planillas.edit', [
            'planilla' => $planilla,
            'unidades' => self::UNIDADES,
        ]);
    }

    public function update(Request $request, Planilla $planilla)
    {
        if ($planilla->estado === self::ESTADO_FINALIZADO) {
            return redirect()->route('admin.planillas.show', $planilla)->with('warning', 'Esta planilla está finalizada y no se puede editar.');
        }

        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', self::UNIDADES)],
            'fecha_revision' => ['required', 'date'],
            'data' => ['nullable', 'array'],
        ]);

        $estado = $request->has('guardar_finalizar') ? self::ESTADO_FINALIZADO : self::ESTADO_EN_EDICION;

        $planilla->update([
            'unidad' => $validated['unidad'],
            'fecha_revision' => $validated['fecha_revision'],
            'data' => $validated['data'] ?? [],
            'estado' => $estado,
        ]);

        if ($request->has('guardar_continuar')) {
            return redirect()->route('admin.planillas.edit', $planilla)->with('success', 'Planilla guardada. Puedes continuar después.');
        }

        if ($request->has('guardar_finalizar')) {
            return redirect()->route('admin.planillas.show', $planilla)->with('success', 'Planilla guardada y finalizada.');
        }

        return redirect()->route('admin.planillas.show', $planilla)->with('success', 'Planilla actualizada correctamente.');
    }

    public function show(Planilla $planilla)
    {
        $planilla->load('creador');

        return view('admin.planillas.show', [
            'planilla' => $planilla,
        ]);
    }

    public function updateEstado(Request $request, Planilla $planilla)
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', 'in:' . implode(',', [self::ESTADO_EN_EDICION, self::ESTADO_FINALIZADO])],
        ]);

        $planilla->update([
            'estado' => $validated['estado'],
        ]);

        return redirect()->back()->with('success', 'Estado actualizado.');
    }

    public function destroy(Request $request, Planilla $planilla)
    {
        $planilla->delete();

        return redirect()->route('admin.planillas.index')->with('success', 'Planilla eliminada.');
    }
}
