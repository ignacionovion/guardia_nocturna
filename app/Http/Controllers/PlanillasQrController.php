<?php

namespace App\Http\Controllers;

use App\Models\Bombero;
use App\Models\InventoryQrLink;
use App\Models\Planilla;
use Illuminate\Http\Request;

class PlanillasQrController extends Controller
{
    public function show(Request $request, string $token)
    {
        if (!$request->session()->get('planillas_qr_bombero_id')) {
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token]);
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        // Buscar planillas en edición de este bombero
        $planillasEnEdicion = Planilla::query()
            ->where('bombero_id', $bomberoId)
            ->where('estado', 'en_edicion')
            ->latest('created_at')
            ->get();

        // Si hay planillas en edición, mostrar selector
        if ($planillasEnEdicion->isNotEmpty()) {
            return view('admin.planillas.qr_selector', [
                'token' => $token,
                'planillas' => $planillasEnEdicion,
            ]);
        }

        // Si no hay, ir directo a crear nueva
        return redirect()->route('planillas.qr.create.form', ['token' => $token]);
    }

    public function identificarForm(Request $request, string $token)
    {
        return view('admin.planillas.identificar', [
            'token' => $token,
        ]);
    }

    public function identificarStore(Request $request, string $token)
    {
        $validated = $request->validate([
            'rut' => ['required', 'string', 'max:20', 'regex:/^\d{7,8}-[0-9kK]$/'],
        ], [
            'rut.regex' => 'Formato inválido. Debe ser como 18485962-9.',
        ]);

        $rut = mb_strtolower(trim((string) $validated['rut']));

        $bombero = Bombero::query()
            ->whereRaw('lower(rut) = ?', [$rut])
            ->first();

        if (!$bombero) {
            return back()->withInput()->withErrors([
                'rut' => 'Bombero no existe en nuestra base de datos.',
            ]);
        }

        $request->session()->put('planillas_qr_bombero_id', (int) $bombero->id);

        return redirect()->route('planillas.qr.show', ['token' => $token]);
    }

    public function createForm(Request $request, string $token)
    {
        $bomberoId = $request->session()->get('planillas_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token]);
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        $unidad = $request->string('unidad')->toString();
        if ($unidad === '') {
            $unidad = null;
        }

        $unidades = ['B-3', 'BR-3', 'RX-3'];
        if ($unidad !== null && !in_array($unidad, $unidades, true)) {
            $unidad = $unidades[0];
        }

        $customItems = [];
        if ($unidad) {
            $customItems = \App\Models\PlanillaListItem::where('unidad', $unidad)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('section');
        }

        return view('admin.planillas.create_public', [
            'token' => $token,
            'unidad' => $unidad,
            'unidades' => $unidades,
            'customItems' => $customItems,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $bomberoId = $request->session()->get('planillas_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token]);
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        $unidades = ['B-3', 'BR-3', 'RX-3'];

        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', $unidades)],
            'fecha_revision' => ['required', 'date'],
            'data' => ['nullable', 'array'],
        ]);

        $estado = $request->has('guardar_finalizar') ? 'finalizado' : 'en_edicion';

        Planilla::create([
            'unidad' => $validated['unidad'],
            'fecha_revision' => $validated['fecha_revision'],
            'created_by' => null,
            'bombero_id' => (int) $bomberoId,
            'data' => $validated['data'] ?? [],
            'estado' => $estado,
        ]);

        $request->session()->forget('planillas_qr_bombero_id');

        return redirect()->route('planillas.qr.identificar.form', ['token' => $token])
            ->with('success', 'Planilla guardada correctamente.');
    }

    public function editForm(Request $request, string $token, Planilla $planilla)
    {
        $bomberoId = $request->session()->get('planillas_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token]);
        }

        // Verificar que la planilla pertenezca al bombero y esté en edición
        if ($planilla->bombero_id !== $bomberoId || $planilla->estado !== 'en_edicion') {
            return redirect()->route('planillas.qr.show', ['token' => $token])
                ->with('error', 'No puedes editar esta planilla.');
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        $customItems = \App\Models\PlanillaListItem::where('unidad', $planilla->unidad)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('admin.planillas.edit_public', [
            'token' => $token,
            'planilla' => $planilla,
            'unidad' => $planilla->unidad,
            'unidades' => ['B-3', 'BR-3', 'RX-3'],
            'customItems' => $customItems,
        ]);
    }

    public function update(Request $request, string $token, Planilla $planilla)
    {
        $bomberoId = $request->session()->get('planillas_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token]);
        }

        // Verificar que la planilla pertenezca al bombero y esté en edición
        if ($planilla->bombero_id !== $bomberoId || $planilla->estado !== 'en_edicion') {
            return redirect()->route('planillas.qr.show', ['token' => $token])
                ->with('error', 'No puedes editar esta planilla.');
        }

        $link = InventoryQrLink::query()
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        if ($link->tipo !== 'planillas') {
            abort(404);
        }

        $unidades = ['B-3', 'BR-3', 'RX-3'];

        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', $unidades)],
            'fecha_revision' => ['required', 'date'],
            'data' => ['nullable', 'array'],
        ]);

        $estado = $request->has('guardar_finalizar') ? 'finalizado' : 'en_edicion';

        $planilla->update([
            'unidad' => $validated['unidad'],
            'fecha_revision' => $validated['fecha_revision'],
            'data' => $validated['data'] ?? [],
            'estado' => $estado,
        ]);

        if ($estado === 'finalizado') {
            $request->session()->forget('planillas_qr_bombero_id');
            return redirect()->route('planillas.qr.identificar.form', ['token' => $token])
                ->with('success', 'Planilla finalizada correctamente.');
        }

        return redirect()->route('planillas.qr.edit.form', ['token' => $token, 'planilla' => $planilla])
            ->with('success', 'Cambios guardados. Puedes continuar editando.');
    }
}
