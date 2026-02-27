@php
	$data = $data ?? [];

	// Get custom item keys to avoid duplicates
	$customCabinaKeys = isset($customItems['cabina']) 
		? $customItems['cabina']->pluck('item_key')->toArray() 
		: [];
	$customTraumaKeys = isset($customItems['trauma']) 
		? $customItems['trauma']->pluck('item_key')->toArray() 
		: [];

	$cabinaChecklist = [
		'baston_tastik' => 'Bastón Tastik',
		'era_msa_g1' => 'ERA MSA G1',
		'chaquetillas_stex' => 'Chaquetillas STEX',
		'tablet_unidad_rx3_y_cargador' => 'Tablet unidad RX-3 y Cargador',
		'linternas_apasolo_3000' => 'Linternas APASO L3000',
		'demoledor_makita_y_accesorios' => 'Demoledor MAKITA y accesorios',
		'combi_lukas_e_draulik' => 'Combi Lukas E-Draulik GMBH',
		'cortadora_de_plasma' => 'Cortadora de Plasma',
		'dremel_y_accesorios' => 'Dremel y accesorios',
		'sierra_sable_inalambrica_makita' => 'Sierra sable inalámbrica MAKITA',
		'pistola_neumatica_airgun' => 'Pistola neumática AIRGUN',
		'taladro_inalambrico_makita' => 'Taladro Inalámbrico MAKITA',
		'esmeril_angular_125mm' => 'Esmeril angular 125 mm',
		'sierra_circular_7_1_4' => 'Sierra circular 7 1/4"',
		'rotomartillo_dewalt' => 'Rotomartillo DEWALT',
		'control_vetter_baja_presion' => 'Control VETTER baja presión',
		'grupo_electrogeno_30kva' => 'Grupo electrógeno 30 KVA',
		'motor_combustion_4t_weber' => 'Motor a Combustión 4T WEBER',
		'cizalla_expansor_ram_weber' => 'Cizalla, expansor y RAM WEBER',
		'cizalla_expansor_ram_lukas' => 'Cizalla, expansor y RAM LUKAS',
		'winche_unidad_rx3' => 'Winche de la unidad RX-3',
	];

	// Filter out items that exist in customItems to avoid duplicates
	$filteredCabinaChecklist = array_diff_key($cabinaChecklist, array_flip($customCabinaKeys));

	$traumaChecklist = [
		'collares_cervicales' => 'Collares cervicales',
		'dea' => 'DEA',
		'bolsos_oxigenoterapia_2' => '2 Bolsos oxigenoterapia',
		'chalecos_de_extraccion' => 'Chalecos de extracción',
		'maleta_primeros_auxilios_quemados' => 'Maleta Primeros Auxilios Quemados',
		'mochila_trauma' => 'Mochila Trauma',
		'cajas_de_guantes' => 'Cajas de guantes',
		'ferulas' => 'Férulas',
		'tablas_largas' => 'Tablas Largas',
		'tabla_corta' => 'Tabla corta',
		'laterales' => 'Laterales',
		'pulpos' => 'Pulpos',
	];

	// Filter out items that exist in customItems to avoid duplicates
	$filteredTraumaChecklist = array_diff_key($traumaChecklist, array_flip($customTraumaKeys));

	$cantidades = [
		'cilindros_para_cojines_de_levante' => 'Cilindros para cojines de levante',
		'focos_led_1000w_y_tripode' => 'Focos de 1000W y trípode',
		'cunas' => 'CUÑAS',
		'set_lona_cubre_pilares' => 'Set lona cubre pilares',
		'eslingas_naranjas' => 'Eslingas naranjas',
		'eslingas_azules' => 'Eslingas azules',
		'eslingas_ojo_a_ojo' => 'Eslingas ojo a ojo',
		'cadenas_weber' => 'Cadenas WEBER',
		'estabilizadores_paratech' => 'Estabilizadores PARATECH',
		'puntas' => 'Puntas',
		'plataforma_de_rescate' => 'Plataforma de Rescate',
		'colchon_vetter_baja_presion' => 'Colchón Vetter baja presión',
		'paquete_circular' => 'Paquete Circular',
		'manguera_de_alimentacion' => 'Manguera de alimentación',
		'maleta_sistema_paratech' => 'Maleta Sistema Paratech',
	];
@endphp

<div class="space-y-6">
	<div class="rounded-2xl border border-teal-900/30 bg-sky-100 p-4">
		<div class="text-sm font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
		<div class="text-sm font-black uppercase tracking-widest text-slate-900 mt-1">RX-3</div>
		<div class="text-xs text-slate-700 mt-2 font-semibold">Marca funcionamiento (Sí/No), agrega novedades y completa cantidades cuando aplique.</div>
	</div>

	<div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
		<button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCabinaRX3')">
			<div class="text-xs font-black uppercase tracking-widest text-white">CABINA</div>
			<i class="fas fa-chevron-down text-white/80"></i>
		</button>
		<div id="secCabinaRX3" class="p-4 bg-sky-50">
			<div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
				<div class="text-xs font-black uppercase tracking-widest text-slate-900">Check list herramientas</div>
			</div>

			<div class="grid grid-cols-1 gap-3">
				@foreach($filteredCabinaChecklist as $key => $label)
					@php($row = $data['cabina'][$key] ?? [])
					<div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
						<div class="col-span-12 md:col-span-5 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
							<div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
						</div>
						<div class="col-span-6 md:col-span-2">
							<select name="data[cabina][{{ $key }}][funciona]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
								<option value="" {{ ($row['funciona'] ?? '') === '' ? 'selected' : '' }}>¿Funciona?</option>
								<option value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
								<option value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
							</select>
						</div>
						<div class="col-span-6 md:col-span-2">
							<input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cant.">
						</div>
						<div class="col-span-12 md:col-span-3">
							<input type="text" name="data[cabina][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedades">
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>

	<div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
		<button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secTraumaRX3')">
			<div class="text-xs font-black uppercase tracking-widest text-white">TRAUMA</div>
			<i class="fas fa-chevron-down text-white/80"></i>
		</button>
		<div id="secTraumaRX3" class="p-4 hidden bg-sky-50">
			<div class="grid grid-cols-1 gap-3">
				@foreach($filteredTraumaChecklist as $key => $label)
					@php($row = $data['trauma'][$key] ?? [])
					<div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
						<div class="col-span-12 md:col-span-5 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
							<div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
						</div>
						<div class="col-span-6 md:col-span-2">
							<select name="data[trauma][{{ $key }}][funciona]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
								<option value="" {{ ($row['funciona'] ?? '') === '' ? 'selected' : '' }}>¿Funciona?</option>
								<option value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
								<option value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
							</select>
						</div>
						<div class="col-span-6 md:col-span-2">
							<input type="text" name="data[trauma][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cant.">
						</div>
						<div class="col-span-12 md:col-span-3">
							<input type="text" name="data[trauma][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedades">
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>

	<div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
		<button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCantidadesRX3')">
			<div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
			<i class="fas fa-chevron-down text-white/80"></i>
		</button>
		<div id="secCantidadesRX3" class="p-4 hidden bg-sky-50">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
				@foreach($cantidades as $key => $label)
					<div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
						<div class="col-span-7 rounded-lg bg-sky-100 px-3 py-2 border border-sky-200">
							<div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
						</div>
						<div class="col-span-5">
							<input type="text" name="data[cantidades][{{ $key }}]" value="{{ $data['cantidades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cantidad">
						</div>
					</div>
				@endforeach
			</div>

			<div class="mt-4">
				<div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Observaciones generales</div>
				<textarea name="data[observaciones_generales]" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Observaciones...">{{ $data['observaciones_generales'] ?? '' }}</textarea>
			</div>
		</div>
	</div>
</div>

<script>
	function toggleSection(id) {
		const el = document.getElementById(id);
		if (!el) return;
		el.classList.toggle('hidden');
	}

	document.addEventListener('DOMContentLoaded', function () {
		function syncCantidadNovedad(key) {
			const cantidad = document.querySelector('[data-cantidad-item="' + key + '"]');
			const row = document.querySelector('[data-cantidad-novedad-row="' + key + '"]');
			if (!cantidad || !row) return;

			const v = String(cantidad.value ?? '').trim();
			if (v === '0') {
				row.classList.remove('hidden');
			} else {
				row.classList.add('hidden');
				const input = row.querySelector('input');
				if (input) input.value = '';
			}
		}

		document.querySelectorAll('[data-cantidad-item]').forEach(function (input) {
			const key = input.getAttribute('data-cantidad-item');
			if (!key) return;
			syncCantidadNovedad(key);
			input.addEventListener('input', function () {
				syncCantidadNovedad(key);
			});
		});
	});
</script>
