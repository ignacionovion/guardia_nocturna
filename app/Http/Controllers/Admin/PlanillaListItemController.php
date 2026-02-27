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

        // For BR-3, B-3, RX-3: use static items from form definitions instead of DB
        if (in_array($unidad, ['BR-3', 'B-3', 'RX-3'], true)) {
            $items = $this->getStaticItems($unidad, $section);
        } else {
            $itemsQuery = PlanillaListItem::query()
                ->when($unidad, fn ($q) => $q->where('unidad', $unidad))
                ->when($section, fn ($q) => $q->where('section', $section))
                ->orderBy('unidad')
                ->orderBy('section')
                ->orderBy('sort_order')
                ->orderBy('label');
            $items = $itemsQuery->get();
        }

        return view('admin.planillas.listados.index', [
            'items' => $items,
            'unidades' => self::UNIDADES,
            'sections' => self::SECTIONS,
            'unidadSeleccionada' => $unidad,
            'sectionSeleccionada' => $section,
            'isStatic' => in_array($unidad, ['BR-3', 'B-3', 'RX-3'], true),
        ]);
    }

    /**
     * Get static items from form definitions for BR-3, B-3, RX-3
     */
    private function getStaticItems(string $unidad, string $section): array
    {
        $staticItems = [
            // BR-3 - Cabina
            'BR-3_cabina' => [
                ['id' => 'static_1', 'label' => 'Linterna NIGHTSTICK', 'item_key' => 'linterna_nightstick', 'sort_order' => 0, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'BR-3'],
                ['id' => 'static_2', 'label' => 'ERA SCOTT 4.5', 'item_key' => 'era_scott_4_5', 'sort_order' => 1, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'BR-3'],
                ['id' => 'static_3', 'label' => 'Chaquetillas STEX', 'item_key' => 'chaquetillas_stex', 'sort_order' => 2, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'BR-3'],
                ['id' => 'static_4', 'label' => 'Tablet unidad BR-3 y Cargador', 'item_key' => 'tablet_br3', 'sort_order' => 3, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'BR-3'],
            ],
            'BR-3_trauma' => [
                ['id' => 'static_1', 'label' => 'Collares cervicales', 'item_key' => 'collares_cervicales', 'sort_order' => 0, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_2', 'label' => 'DEA', 'item_key' => 'dea', 'sort_order' => 1, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_3', 'label' => 'Bolso Oxigenoterapia', 'item_key' => 'bolso_oxigenoterapia', 'sort_order' => 2, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_4', 'label' => 'Chalecos de extricación', 'item_key' => 'chalecos_extricacion', 'sort_order' => 3, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_5', 'label' => 'Tablas Largas', 'item_key' => 'tablas_largas', 'sort_order' => 4, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_6', 'label' => 'Tabla pediátrica', 'item_key' => 'tabla_pediatrica', 'sort_order' => 5, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_7', 'label' => 'Mochila Trauma', 'item_key' => 'mochila_trauma', 'sort_order' => 6, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_8', 'label' => 'Cajas de guantes', 'item_key' => 'cajas_guantes', 'sort_order' => 7, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_9', 'label' => 'Férulas', 'item_key' => 'ferulas', 'sort_order' => 8, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_10', 'label' => 'Tabla Scoop', 'item_key' => 'tabla_scoop', 'sort_order' => 9, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_11', 'label' => 'Laterales', 'item_key' => 'laterales', 'sort_order' => 10, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_12', 'label' => 'Pulpos', 'item_key' => 'pulpos', 'sort_order' => 11, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
                ['id' => 'static_13', 'label' => 'Bolso TRIAGE', 'item_key' => 'bolso_triage', 'sort_order' => 12, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'BR-3'],
            ],
            'BR-3_cantidades' => [
                ['id' => 'static_1', 'label' => 'MANGUERAS (38mm, 52mm, 75mm, LDH, Armada Base)', 'item_key' => 'mangueras', 'sort_order' => 0, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_2', 'label' => 'HERRADURAS', 'item_key' => 'herraduras', 'sort_order' => 1, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_3', 'label' => 'ATAQUES (52mm, 75mm, Manguera LDH)', 'item_key' => 'ataques', 'sort_order' => 2, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_4', 'label' => 'TRASPASOS', 'item_key' => 'traspasos', 'sort_order' => 3, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_5', 'label' => 'Protecciones duras para paciente / Cojines VETTER / Eslings', 'item_key' => 'protecciones', 'sort_order' => 4, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_6', 'label' => 'CUÑAS (Biseladas, Bloques, Escalonadas, Planas, Combos 2l)', 'item_key' => 'cunas', 'sort_order' => 5, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_7', 'label' => 'CAJONERAS LATERALES (Barretilla, Napoleón, Stab Fast XL, Jack)', 'item_key' => 'cajoneras', 'sort_order' => 6, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_8', 'label' => 'ESCALAS (2c 12m, 2c 8m, Plegable)', 'item_key' => 'escalas', 'sort_order' => 7, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
                ['id' => 'static_9', 'label' => 'Material del Techo (4 palas, 2 pasatiras, 2 McLeod, 3 rastrillos, 2 Chorizos, 1 Filtro)', 'item_key' => 'material_techo', 'sort_order' => 8, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'BR-3'],
            ],
            // B-3 - Cabina
            'B-3_cabina' => [
                ['id' => 'static_1', 'label' => 'ERA MSA G 1 (7 unidades + OBAC + Radios)', 'item_key' => 'era_msa_g1', 'sort_order' => 0, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'B-3'],
                ['id' => 'static_2', 'label' => 'Linterna NIGHTSTICK XPR-5568', 'item_key' => 'linterna_nightstick', 'sort_order' => 1, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'B-3'],
                ['id' => 'static_3', 'label' => 'Tablet unidad B-3 y Cargador', 'item_key' => 'tablet_b3', 'sort_order' => 2, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'B-3'],
                ['id' => 'static_4', 'label' => 'Maleta SCI', 'item_key' => 'maleta_sci', 'sort_order' => 3, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'B-3'],
            ],
            'B-3_cantidades' => [
                ['id' => 'static_1', 'label' => 'Mochila de Trauma y Cilindro O2', 'item_key' => 'mochila_trauma', 'sort_order' => 0, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_2', 'label' => 'MANGUERAS (52, 75, LDH, Armada Base)', 'item_key' => 'mangueras', 'sort_order' => 1, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_3', 'label' => 'Paquete Circular / Herraduras / Carretes', 'item_key' => 'paquete_circular', 'sort_order' => 2, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_4', 'label' => 'ATAQUES (52, 75, Cilindros MSA)', 'item_key' => 'ataques', 'sort_order' => 3, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_5', 'label' => 'TRASPASOS / Llaves de grifo', 'item_key' => 'traspasos', 'sort_order' => 4, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_6', 'label' => 'Escalas de Techo / Puntas Taladro / Napoleón 30"', 'item_key' => 'escalas_techo', 'sort_order' => 5, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_7', 'label' => 'Combo de 8 libras / Bidón Motosierra / Hacha bombero', 'item_key' => 'combo_8l', 'sort_order' => 6, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_8', 'label' => 'Bicheros / Barretilla / Halligan', 'item_key' => 'bicheros', 'sort_order' => 7, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_9', 'label' => 'Material Forestal (1 pala, 1 rozón, 2 rastrillos, 1 McLeod, 2 bombas)', 'item_key' => 'material_forestal', 'sort_order' => 8, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
                ['id' => 'static_10', 'label' => 'Bolso de altura (Tira 38mm 3m, Pitón POK, gemelo, reducción, cuerda)', 'item_key' => 'bolso_altura', 'sort_order' => 9, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'B-3'],
            ],
            // RX-3 - Cabina
            'RX-3_cabina' => [
                ['id' => 'static_1', 'label' => 'Bastón Tastik', 'item_key' => 'baston_tastik', 'sort_order' => 0, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'RX-3'],
                ['id' => 'static_2', 'label' => 'ERA MSA G1', 'item_key' => 'era_msa_g1', 'sort_order' => 1, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'RX-3'],
                ['id' => 'static_3', 'label' => 'Chaquetillas STEX', 'item_key' => 'chaquetillas_stex', 'sort_order' => 2, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'RX-3'],
                ['id' => 'static_4', 'label' => 'Tablet unidad RX-3 y Cargador', 'item_key' => 'tablet_rx3', 'sort_order' => 3, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'RX-3'],
                ['id' => 'static_5', 'label' => 'Linternas APASO L3000', 'item_key' => 'linternas_apaso', 'sort_order' => 4, 'is_active' => true, 'section' => 'cabina', 'unidad' => 'RX-3'],
            ],
            'RX-3_trauma' => [
                ['id' => 'static_1', 'label' => 'Collares cervicales', 'item_key' => 'collares_cervicales', 'sort_order' => 0, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_2', 'label' => 'DEA', 'item_key' => 'dea', 'sort_order' => 1, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_3', 'label' => '2 Bolsos Oxigenoterapia', 'item_key' => 'bolsos_oxigenoterapia', 'sort_order' => 2, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_4', 'label' => 'Chalecos de extricación / Bolso TRIAGE / Tabla corta', 'item_key' => 'chalecos_extricacion', 'sort_order' => 3, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_5', 'label' => 'Maleta Primeros Auxilios Quemados', 'item_key' => 'maleta_quemados', 'sort_order' => 4, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_6', 'label' => 'Mochila Trauma', 'item_key' => 'mochila_trauma', 'sort_order' => 5, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_7', 'label' => 'Cajas de guantes', 'item_key' => 'cajas_guantes', 'sort_order' => 6, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_8', 'label' => 'Férulas', 'item_key' => 'ferulas', 'sort_order' => 7, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_9', 'label' => 'Tablas Largas', 'item_key' => 'tablas_largas', 'sort_order' => 8, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_10', 'label' => 'Laterales', 'item_key' => 'laterales', 'sort_order' => 9, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
                ['id' => 'static_11', 'label' => 'Pulpos', 'item_key' => 'pulpos', 'sort_order' => 10, 'is_active' => true, 'section' => 'trauma', 'unidad' => 'RX-3'],
            ],
            'RX-3_cantidades' => [
                ['id' => 'static_1', 'label' => 'Cilindros para cojines de levante / Cojines Paratech / Tirfor', 'item_key' => 'cilindros_cojines', 'sort_order' => 0, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_2', 'label' => 'Focos de 1000W y trípode / Caja Herramientas / Cubre Airbag', 'item_key' => 'focos_1000w', 'sort_order' => 1, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_3', 'label' => 'CUÑAS (Biseladas, Bloques, Escalonadas, Planas, Combos 2l)', 'item_key' => 'cunas', 'sort_order' => 2, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_4', 'label' => 'Set lona cubre pilares / Force / Combo 8l', 'item_key' => 'lona_pilares', 'sort_order' => 3, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_5', 'label' => 'Eslings Naranjas / Barretilla / Halligan', 'item_key' => 'eslings_naranjas', 'sort_order' => 4, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_6', 'label' => 'Eslings Azules / Napoleón 24" / TNT', 'item_key' => 'eslings_azules', 'sort_order' => 5, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_7', 'label' => 'Eslings Ojo a ojo / Hacha bombero / Estacas fierro', 'item_key' => 'eslings_ojo', 'sort_order' => 6, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_8', 'label' => 'Cadenas WEBER / Soporte RAM / Muela RAM Lukas', 'item_key' => 'cadenas_weber', 'sort_order' => 7, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_9', 'label' => 'Estabilizadores PARATECH / Extensiones / Bases', 'item_key' => 'estabilizadores_paratech', 'sort_order' => 8, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_10', 'label' => 'Plataforma de Rescate / Escalas / Conos', 'item_key' => 'plataforma_rescate', 'sort_order' => 9, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_11', 'label' => 'Maleta Sistema Paratech (mando dual, regulador, mangueras 10m, válvulas)', 'item_key' => 'maleta_paratech', 'sort_order' => 10, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
                ['id' => 'static_12', 'label' => 'Material de cuerdas (Jumar, mosquetones, poleas, ascensores Buddy, freno, plato, descendedor)', 'item_key' => 'material_cuerdas', 'sort_order' => 11, 'is_active' => true, 'section' => 'cantidades', 'unidad' => 'RX-3'],
            ],
        ];

        $key = $unidad . '_' . $section;
        return $staticItems[$key] ?? [];
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

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['integer', 'exists:planilla_list_items,id'],
        ]);

        $ids = $validated['items'];
        
        // Update sort_order based on array position
        foreach ($ids as $index => $id) {
            PlanillaListItem::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
    }

    /**
     * Reset items to default state from seeder for a specific unit and section
     */
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'unidad' => ['required', 'string', 'max:20', 'in:' . implode(',', self::UNIDADES)],
            'section' => ['required', 'string', 'max:50', 'in:' . implode(',', array_keys(self::SECTIONS))],
        ]);

        $unidad = $validated['unidad'];
        $section = $validated['section'];

        // Default items from seeder
        $defaultItems = [
            // BR-3 - Cabina
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'linterna_nightstick', 'label' => 'Linterna NIGHTSTICK', 'sort_order' => 0],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'era_scott_4_5', 'label' => 'ERA SCOTT 4.5', 'sort_order' => 1],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'chaquetillas_stex', 'label' => 'Chaquetillas STEX', 'sort_order' => 2],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'tablet_br3_y_cargador', 'label' => 'Tablet unidad BR-3 y Cargador', 'sort_order' => 3],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'ripper_corta_parabrisas', 'label' => 'RIPPER (corta parabrisas)', 'sort_order' => 4],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'baston_tastik', 'label' => 'Bastón Tastik', 'sort_order' => 5],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'generador_electrico_honda', 'label' => 'Generador eléctrico Honda', 'sort_order' => 6],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'motosierra_stihl_ms170', 'label' => 'Motosierra Stihl MS170', 'sort_order' => 7],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'motor_holmatro_y_herramientas', 'label' => 'Motor HOLMATRO y herramientas', 'sort_order' => 8],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'combi_lukas_e_draulik', 'label' => 'Combi Lukas E-Draulik GMBH', 'sort_order' => 9],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'motoamoladora_makita', 'label' => 'Motoamoladora MAKITA', 'sort_order' => 10],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'sierra_sable_hilti', 'label' => 'Sierra sable HILTI', 'sort_order' => 11],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'dremel_y_accesorios', 'label' => 'Dremel y accesorios', 'sort_order' => 12],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'martillo_neumatico', 'label' => 'Martillo neumático', 'sort_order' => 13],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'taladro_inalambrico_makita', 'label' => 'Taladro inalámbrico MAKITA', 'sort_order' => 14],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'control_cojines_vetter', 'label' => 'Control cojines VETTER', 'sort_order' => 15],
            ['unidad' => 'BR-3', 'section' => 'cabina', 'item_key' => 'esmeril_angular', 'label' => 'Esmeril angular', 'sort_order' => 16],

            // BR-3 - Trauma
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'collares_cervicales', 'label' => 'Collares cervicales', 'sort_order' => 0],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'dea', 'label' => 'DEA', 'sort_order' => 1],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'bolso_oxigenoterapia', 'label' => 'Bolso Oxigenoterapia', 'sort_order' => 2],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'chalecos_extraccion', 'label' => 'Chalecos de extracción', 'sort_order' => 3],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'tablas_largas', 'label' => 'Tablas largas', 'sort_order' => 4],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'tabla_pediatrica', 'label' => 'Tabla pediátrica', 'sort_order' => 5],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'mochila_trauma', 'label' => 'Mochila Trauma', 'sort_order' => 6],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'cajas_guantes', 'label' => 'Cajas de guantes', 'sort_order' => 7],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'ferulas', 'label' => 'Férulas', 'sort_order' => 8],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'tabla_scoop', 'label' => 'Tabla Scoop', 'sort_order' => 9],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'laterales', 'label' => 'Laterales', 'sort_order' => 10],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'pulpos', 'label' => 'Pulpos', 'sort_order' => 11],
            ['unidad' => 'BR-3', 'section' => 'trauma', 'item_key' => 'bolso_triage', 'label' => 'Bolso TRIAGE', 'sort_order' => 12],

            // BR-3 - Cantidades
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'mangueras_38mm', 'label' => 'Mangueras 38mm', 'sort_order' => 0],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'mangueras_52mm', 'label' => 'Mangueras 52mm', 'sort_order' => 1],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'mangueras_75mm', 'label' => 'Mangueras 75mm', 'sort_order' => 2],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'herraduras', 'label' => 'HERRADURAS', 'sort_order' => 3],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'ataques_52mm', 'label' => 'Ataques 52mm', 'sort_order' => 4],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'ataques_75mm', 'label' => 'Ataques 75mm', 'sort_order' => 5],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'traspasos', 'label' => 'Traspasos', 'sort_order' => 6],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'protecciones_duras_paciente', 'label' => 'Protecciones duras para paciente', 'sort_order' => 7],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'tecle_cadena_2000kg', 'label' => 'Tecle para cadena 2000kg', 'sort_order' => 8],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'cunas', 'label' => 'CUÑAS', 'sort_order' => 9],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'cajoneras_laterales', 'label' => 'Cajoneras laterales', 'sort_order' => 10],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'escalas', 'label' => 'ESCALAS', 'sort_order' => 11],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'set_lona_cubre_pilares', 'label' => 'Set lona cubre pilares', 'sort_order' => 12],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'bicheros', 'label' => 'Bicheros', 'sort_order' => 13],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'focos_led_tripode', 'label' => 'Focos LED con cable y trípode', 'sort_order' => 14],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'carrete_cable_electrico', 'label' => 'Carrete cable eléctrico', 'sort_order' => 15],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'cadenas_y_puntas_holmatro', 'label' => 'Cadenas y puntas Holmatro', 'sort_order' => 16],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'corta_parabrisas_manual', 'label' => 'Corta parabrisas manual', 'sort_order' => 17],
            ['unidad' => 'BR-3', 'section' => 'cantidades', 'item_key' => 'material_techo', 'label' => 'Material del techo', 'sort_order' => 18],

            // B-3 - Cabina
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'era_msa_g1', 'label' => 'ERA MSA G 1', 'sort_order' => 0],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'linterna_nightstick_xpp_5568', 'label' => 'Linterna NIGHTSTICK XPP-5568', 'sort_order' => 1],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'tablet_unidad_b3_y_cargador', 'label' => 'Tablet unidad B-3 y Cargador', 'sort_order' => 2],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'camara_termal', 'label' => 'Cámara Termal', 'sort_order' => 3],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'baston_tastik', 'label' => 'Bastón Tastik', 'sort_order' => 4],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'detector_gas_tif8900', 'label' => 'Detector de Gas TIF8900', 'sort_order' => 5],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'motosierra_cutter_edge', 'label' => 'Motosierra "CUTTER EDGE"', 'sort_order' => 6],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'taladro_inalambrico_makita', 'label' => 'Taladro Inalámbrico MAKITA', 'sort_order' => 7],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'motobomba_rosenbauer', 'label' => 'Motobomba Rosenbauer', 'sort_order' => 8],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'aspirador_nautilus_8_1', 'label' => 'Aspirador NAUTILUS 8/1', 'sort_order' => 9],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'motoamoladora_makita_m14', 'label' => 'Motoamoladora M14 MAKITA', 'sort_order' => 10],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'motosierra_stihl', 'label' => 'Motosierra STIHL', 'sort_order' => 11],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'motor_electrogeno_rs14', 'label' => 'Motor electrógeno RS 14', 'sort_order' => 12],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'focos_led_1000w', 'label' => 'Focos LED 1000 watt', 'sort_order' => 13],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'foco_inalambrico_makita_tripode', 'label' => 'Foco inalámbrico Makita & trípode', 'sort_order' => 14],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'winche_unidad_b3', 'label' => 'Winche unidad B-3', 'sort_order' => 15],
            ['unidad' => 'B-3', 'section' => 'cabina', 'item_key' => 'ventilador_rosenbauer', 'label' => 'Ventilador Rosenbauer', 'sort_order' => 16],

            // B-3 - Cantidades
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'mangueras', 'label' => 'Mangueras', 'sort_order' => 0],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'paquete_circular', 'label' => 'Paquete Circular', 'sort_order' => 1],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'ataques', 'label' => 'Ataques', 'sort_order' => 2],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'traspasos', 'label' => 'Traspasos', 'sort_order' => 3],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'llaves_de_copla', 'label' => 'Llaves de copla', 'sort_order' => 4],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'escalas_de_techo', 'label' => 'Escalas de techo', 'sort_order' => 5],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'combo_8_libras', 'label' => 'Combo de 8 libras', 'sort_order' => 6],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'bicheros', 'label' => 'Bicheros', 'sort_order' => 7],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'caja_de_herramientas', 'label' => 'Caja de Herramientas', 'sort_order' => 8],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'grembio', 'label' => 'Grembio', 'sort_order' => 9],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'filtro_de_aspiracion', 'label' => 'Filtro de Aspiración', 'sort_order' => 10],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'kit_quemados_asistente_trauma', 'label' => 'Kit quemados / Asistente de Trauma', 'sort_order' => 11],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'bidon_emergencia_verde_10l', 'label' => 'Bidón de emergencia Verde 10L', 'sort_order' => 12],
            ['unidad' => 'B-3', 'section' => 'cantidades', 'item_key' => 'extintor_de_agua', 'label' => 'Extintor de agua', 'sort_order' => 13],

            // RX-3 - Cabina
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'baston_tastik', 'label' => 'Bastón Tastik', 'sort_order' => 0],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'era_msa_g1', 'label' => 'ERA MSA G1', 'sort_order' => 1],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'chaquetillas_stex', 'label' => 'Chaquetillas STEX', 'sort_order' => 2],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'tablet_unidad_rx3_y_cargador', 'label' => 'Tablet unidad RX-3 y Cargador', 'sort_order' => 3],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'linternas_apasolo_3000', 'label' => 'Linternas APASO L3000', 'sort_order' => 4],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'demoledor_makita_y_accesorios', 'label' => 'Demoledor MAKITA y accesorios', 'sort_order' => 5],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'combi_lukas_e_draulik', 'label' => 'Combi Lukas E-Draulik GMBH', 'sort_order' => 6],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'cortadora_de_plasma', 'label' => 'Cortadora de Plasma', 'sort_order' => 7],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'dremel_y_accesorios', 'label' => 'Dremel y accesorios', 'sort_order' => 8],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'sierra_sable_inalambrica_makita', 'label' => 'Sierra sable inalámbrica MAKITA', 'sort_order' => 9],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'pistola_neumatica_airgun', 'label' => 'Pistola neumática AIRGUN', 'sort_order' => 10],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'taladro_inalambrico_makita', 'label' => 'Taladro Inalámbrico MAKITA', 'sort_order' => 11],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'esmeril_angular_125mm', 'label' => 'Esmeril angular 125 mm', 'sort_order' => 12],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'sierra_circular_7_1_4', 'label' => 'Sierra circular 7 1/4"', 'sort_order' => 13],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'rotomartillo_dewalt', 'label' => 'Rotomartillo DEWALT', 'sort_order' => 14],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'control_vetter_baja_presion', 'label' => 'Control VETTER baja presión', 'sort_order' => 15],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'grupo_electrogeno_30kva', 'label' => 'Grupo electrógeno 30 KVA', 'sort_order' => 16],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'motor_combustion_4t_weber', 'label' => 'Motor a Combustión 4T WEBER', 'sort_order' => 17],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'cizalla_expansor_ram_weber', 'label' => 'Cizalla, expansor y RAM WEBER', 'sort_order' => 18],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'cizalla_expansor_ram_lukas', 'label' => 'Cizalla, expansor y RAM LUKAS', 'sort_order' => 19],
            ['unidad' => 'RX-3', 'section' => 'cabina', 'item_key' => 'winche_unidad_rx3', 'label' => 'Winche de la unidad RX-3', 'sort_order' => 20],

            // RX-3 - Trauma
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'collares_cervicales', 'label' => 'Collares cervicales', 'sort_order' => 0],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'dea', 'label' => 'DEA', 'sort_order' => 1],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'bolsos_oxigenoterapia_2', 'label' => '2 Bolsos oxigenoterapia', 'sort_order' => 2],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'chalecos_de_extraccion', 'label' => 'Chalecos de extracción', 'sort_order' => 3],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'maleta_primeros_auxilios_quemados', 'label' => 'Maleta Primeros Auxilios Quemados', 'sort_order' => 4],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'mochila_trauma', 'label' => 'Mochila Trauma', 'sort_order' => 5],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'cajas_de_guantes', 'label' => 'Cajas de guantes', 'sort_order' => 6],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'ferulas', 'label' => 'Férulas', 'sort_order' => 7],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'tablas_largas', 'label' => 'Tablas Largas', 'sort_order' => 8],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'tabla_corta', 'label' => 'Tabla corta', 'sort_order' => 9],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'laterales', 'label' => 'Laterales', 'sort_order' => 10],
            ['unidad' => 'RX-3', 'section' => 'trauma', 'item_key' => 'pulpos', 'label' => 'Pulpos', 'sort_order' => 11],

            // RX-3 - Cantidades
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'cilindros_para_cojines_de_levante', 'label' => 'Cilindros para cojines de levante', 'sort_order' => 0],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'focos_led_1000w_y_tripode', 'label' => 'Focos de 1000W y trípode', 'sort_order' => 1],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'cunas', 'label' => 'CUÑAS', 'sort_order' => 2],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'set_lona_cubre_pilares', 'label' => 'Set lona cubre pilares', 'sort_order' => 3],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'eslingas_naranjas', 'label' => 'Eslingas naranjas', 'sort_order' => 4],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'eslingas_azules', 'label' => 'Eslingas azules', 'sort_order' => 5],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'eslingas_ojo_a_ojo', 'label' => 'Eslingas ojo a ojo', 'sort_order' => 6],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'cadenas_weber', 'label' => 'Cadenas WEBER', 'sort_order' => 7],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'estabilizadores_paratech', 'label' => 'Estabilizadores PARATECH', 'sort_order' => 8],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'puntas', 'label' => 'Puntas', 'sort_order' => 9],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'plataforma_de_rescate', 'label' => 'Plataforma de Rescate', 'sort_order' => 10],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'colchon_vetter_baja_presion', 'label' => 'Colchón Vetter baja presión', 'sort_order' => 11],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'paquete_circular', 'label' => 'Paquete Circular', 'sort_order' => 12],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'manguera_de_alimentacion', 'label' => 'Manguera de alimentación', 'sort_order' => 13],
            ['unidad' => 'RX-3', 'section' => 'cantidades', 'item_key' => 'maleta_sistema_paratech', 'label' => 'Maleta Sistema Paratech', 'sort_order' => 14],
        ];

        // Filter items for the selected unit and section
        $itemsToReset = array_filter($defaultItems, function ($item) use ($unidad, $section) {
            return $item['unidad'] === $unidad && $item['section'] === $section;
        });

        // Delete existing items for this unit/section that are NOT in the default list
        $defaultKeys = array_map(fn ($item) => $item['item_key'], $itemsToReset);
        PlanillaListItem::where('unidad', $unidad)
            ->where('section', $section)
            ->whereNotIn('item_key', $defaultKeys)
            ->delete();

        // Reset/Create default items
        foreach ($itemsToReset as $item) {
            PlanillaListItem::updateOrCreate(
                [
                    'unidad' => $item['unidad'],
                    'section' => $item['section'],
                    'item_key' => $item['item_key'],
                ],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        return redirect()->route('admin.planillas.listados.index', [
            'unidad' => $unidad,
            'section' => $section,
        ])->with('success', 'Ítems restaurados al estado estándar.');
    }
}
