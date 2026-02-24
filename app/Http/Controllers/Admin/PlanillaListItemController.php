<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanillaListItem;
use Illuminate\Http\Request;

class PlanillaListItemController extends Controller
{
    private const UNIDADES = ['BR-3', 'B-3', 'RX-3'];

    private const SECTIONS = [
        'cabina' => 'Cabina',
        'trauma' => 'Trauma',
        'cantidades' => 'Cantidades',
    ];

    public function index(Request $request)
    {
        $unidad = $request->string('unidad')->toString();
        $unidad = $unidad !== '' ? $unidad : null;
        if ($unidad !== null && !in_array($unidad, self::UNIDADES, true)) {
            $unidad = self::UNIDADES[0];
        }

        $section = $request->string('section')->toString();
        $section = $section !== '' ? $section : null;
        if ($section !== null && !array_key_exists($section, self::SECTIONS)) {
            $section = array_key_first(self::SECTIONS);
        }

        $itemsQuery = PlanillaListItem::query()
            ->when($unidad, fn ($q) => $q->where('unidad', $unidad))
            ->when($section, fn ($q) => $q->where('section', $section))
            ->orderBy('unidad')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('label');

        $items = $itemsQuery->get();

        return view('admin.planillas.listados.index', [
            'items' => $items,
            'unidades' => self::UNIDADES,
            'sections' => self::SECTIONS,
            'unidadSeleccionada' => $unidad,
            'sectionSeleccionada' => $section,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', self::UNIDADES)],
            'section' => ['required', 'string', 'max:50', 'in:' . implode(',', array_keys(self::SECTIONS))],
            'item_key' => ['required', 'string', 'max:120'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $itemKey = trim((string) $validated['item_key']);

        PlanillaListItem::updateOrCreate([
            'unidad' => $validated['unidad'],
            'section' => $validated['section'],
            'item_key' => $itemKey,
        ], [
            'label' => $validated['label'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        return redirect()->route('admin.planillas.listados.index', [
            'unidad' => $validated['unidad'],
            'section' => $validated['section'],
        ])->with('success', 'Ítem agregado/actualizado.');
    }

    public function update(Request $request, PlanillaListItem $item)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'label' => $validated['label'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->back()->with('success', 'Ítem actualizado.');
    }

    public function destroy(Request $request, PlanillaListItem $item)
    {
        $item->delete();

        return redirect()->back()->with('success', 'Ítem eliminado.');
    }
}
