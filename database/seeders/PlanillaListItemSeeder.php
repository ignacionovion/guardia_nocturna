<?php

namespace Database\Seeders;

use App\Models\PlanillaListItem;
use Illuminate\Database\Seeder;

class PlanillaListItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
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

        foreach ($items as $item) {
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
    }
}
