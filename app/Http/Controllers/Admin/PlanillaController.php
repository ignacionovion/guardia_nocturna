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

        $query = Planilla::query()->with(['creador', 'bombero'])->latest('fecha_revision');
        if ($unidad && in_array($unidad, self::UNIDADES, true)) {
            $query->where('unidad', $unidad);
        }

        $planillas = $query->paginate(20)->withQueryString();

        // Historial de cambios de guardias - mostrar solo cambios de estado reales
        $shiftUsers = \App\Models\ShiftUser::query()
            ->with(['firefighter.guardia', 'shift'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('firefighter_id')
            ->orderBy('created_at')
            ->get();

        // Detectar cambios reales (estado anterior diferente al actual)
        $guardiaChanges = collect();
        $groupedByFirefighter = $shiftUsers->groupBy('firefighter_id');

        foreach ($groupedByFirefighter as $firefighterId => $records) {
            $previousStatus = null;
            foreach ($records as $record) {
                $currentStatus = $record->attendance_status ?? 'constituye';
                if ($previousStatus !== null && $previousStatus !== $currentStatus) {
                    $guardiaChanges->push([
                        'firefighter' => $record->firefighter,
                        'guardia' => $record->firefighter?->guardia,
                        'fecha' => $record->created_at,
                        'estado_anterior' => $previousStatus,
                        'estado_nuevo' => $currentStatus,
                        'shift' => $record->shift,
                    ]);
                }
                $previousStatus = $currentStatus;
            }
        }

        $guardiaChanges = $guardiaChanges->sortByDesc('fecha')->take(50);

        // Bitácora - SOLO elementos relacionados con Planillas (últimos 7 días)
        // Nuevas planillas creadas
        $nuevasPlanillas = Planilla::query()
            ->with('creador')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($p) => [
                'tipo' => 'Planilla',
                'descripcion' => "Nueva planilla {$p->unidad} - {$p->fecha_revision?->format('d/m/Y')}",
                'usuario' => $p->creador?->name ?? 'Sistema',
                'fecha' => $p->created_at,
                'link' => route('admin.planillas.show', $p),
            ]);

        // Planillas finalizadas (cambio de estado a finalizado)
        $planillasFinalizadas = Planilla::query()
            ->with('creador')
            ->where('estado', self::ESTADO_FINALIZADO)
            ->where('updated_at', '>=', now()->subDays(7))
            ->whereColumn('updated_at', '!=', 'created_at') // Solo si hubo cambio real
            ->latest('updated_at')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'tipo' => 'Finalizado',
                'descripcion' => "Planilla {$p->unidad} finalizada - {$p->fecha_revision?->format('d/m/Y')}",
                'usuario' => $p->creador?->name ?? 'Sistema',
                'fecha' => $p->updated_at,
                'link' => route('admin.planillas.show', $p),
            ]);

        $bitacora = $nuevasPlanillas
            ->concat($planillasFinalizadas)
            ->sortByDesc('fecha')
            ->take(50)
            ->values();

        return view('admin.planillas.index', [
            'planillas' => $planillas,
            'unidades' => self::UNIDADES,
            'unidadSeleccionada' => $unidad,
            'guardiaChanges' => $guardiaChanges,
            'bitacora' => $bitacora,
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

        $customItems = [];
        if ($unidad) {
            $customItems = \App\Models\PlanillaListItem::where('unidad', $unidad)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('section');
        }

        return view('admin.planillas.create', [
            'unidad' => $unidad,
            'unidades' => self::UNIDADES,
            'customItems' => $customItems,
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

        $customItems = \App\Models\PlanillaListItem::where('unidad', $planilla->unidad)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('admin.planillas.edit', [
            'planilla' => $planilla,
            'unidades' => self::UNIDADES,
            'customItems' => $customItems,
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
        $planilla->load(['creador', 'bombero']);

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

    public function pdf(Planilla $planilla)
    {
        $planilla->load(['creador', 'bombero']);
        
        // Load custom items for the PDF to display checklist data properly
        $customItems = \App\Models\PlanillaListItem::where('unidad', $planilla->unidad)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');
        
        $pdf = \PDF::loadView('admin.planillas.pdf', [
            'planilla' => $planilla,
            'customItems' => $customItems,
        ]);
        
        return $pdf->download('planilla-' . $planilla->unidad . '-' . $planilla->fecha_revision->format('Y-m-d') . '.pdf');
    }

    public function email(Planilla $planilla)
    {
        $planilla->load(['creador', 'bombero']);
        
        // Generate PDF
        $pdf = \PDF::loadView('admin.planillas.pdf', [
            'planilla' => $planilla,
        ]);
        
        // Send email with PDF attachment
        $subject = 'Planilla de revisión ' . $planilla->unidad . ' - ' . $planilla->fecha_revision->format('d/m/Y');
        $lines = [
            'Planilla de revisión de niveles',
            'Unidad: ' . $planilla->unidad,
            'Fecha: ' . $planilla->fecha_revision->format('d/m/Y H:i'),
            'Registrada por: ' . ($planilla->creador?->name ?? trim((string)($planilla->bombero?->nombres ?? '') . ' ' . (string)($planilla->bombero?->apellido_paterno ?? '')) ?: '—'),
        ];
        
        if (class_exists(\App\Services\SystemEmailService::class)) {
            \App\Services\SystemEmailService::send(
                type: 'planilla',
                subject: $subject,
                lines: $lines,
                sourceLabel: 'Planillas'
            );
        }
        
        return redirect()->back()->with('success', 'Planilla enviada por email correctamente.');
    }

    public function compare(Request $request, Planilla $planilla)
    {
        $planilla->load(['creador', 'bombero']);
        
        // Obtener planillas anteriores de la misma unidad para comparar
        $historial = Planilla::query()
            ->where('unidad', $planilla->unidad)
            ->where('id', '!=', $planilla->id)
            ->where('estado', self::ESTADO_FINALIZADO)
            ->latest('fecha_revision')
            ->limit(5)
            ->get();
        
        // Si se seleccionó una planilla para comparar
        $compararCon = null;
        $diferencias = [];
        
        if ($request->has('comparar_con')) {
            $compararCon = Planilla::query()
                ->where('id', $request->input('comparar_con'))
                ->where('unidad', $planilla->unidad)
                ->first();
            
            if ($compararCon) {
                $diferencias = $this->calcularDiferencias($planilla->data ?? [], $compararCon->data ?? []);
            }
        }
        
        return view('admin.planillas.compare', [
            'planilla' => $planilla,
            'historial' => $historial,
            'compararCon' => $compararCon,
            'diferencias' => $diferencias,
        ]);
    }
    
    private function calcularDiferencias(array $actual, array $anterior): array
    {
        $diferencias = [];
        
        foreach (['cabina', 'trauma', 'cantidades'] as $seccion) {
            if (!isset($actual[$seccion]) && !isset($anterior[$seccion])) {
                continue;
            }
            
            $actualItems = $actual[$seccion] ?? [];
            $anteriorItems = $anterior[$seccion] ?? [];
            
            foreach ($actualItems as $key => $item) {
                $itemAnterior = $anteriorItems[$key] ?? [];
                
                // Detectar cambios en funcionamiento
                if (($item['funciona'] ?? '') !== ($itemAnterior['funciona'] ?? '')) {
                    $diferencias[$seccion][$key]['funciona'] = [
                        'antes' => $itemAnterior['funciona'] ?? '—',
                        'despues' => $item['funciona'] ?? '—',
                    ];
                }
                
                // Detectar cambios en cantidad
                if (($item['cantidad'] ?? '') !== ($itemAnterior['cantidad'] ?? '')) {
                    $diferencias[$seccion][$key]['cantidad'] = [
                        'antes' => $itemAnterior['cantidad'] ?? '—',
                        'despues' => $item['cantidad'] ?? '—',
                    ];
                }
                
                // Detectar cambios en novedades
                if (($item['novedades'] ?? '') !== ($itemAnterior['novedades'] ?? '')) {
                    $diferencias[$seccion][$key]['novedades'] = [
                        'antes' => $itemAnterior['novedades'] ?? '—',
                        'despues' => $item['novedades'] ?? '—',
                    ];
                }
            }
            
            // Detectar ítems eliminados
            foreach ($anteriorItems as $key => $item) {
                if (!isset($actualItems[$key])) {
                    $diferencias[$seccion][$key]['eliminado'] = true;
                }
            }
            
            // Detectar ítems nuevos
            foreach ($actualItems as $key => $item) {
                if (!isset($anteriorItems[$key])) {
                    $diferencias[$seccion][$key]['nuevo'] = true;
                }
            }
        }
        
        return $diferencias;
    }
}
