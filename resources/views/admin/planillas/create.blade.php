@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="Nueva planilla" subtitle="Completa la revisión semanal de la unidad." icon="fas fa-clipboard-list" iconVariant="cyan">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.planillas.index') }}">
            Volver
        </x-ui.button>
    </x-ui.page-header>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-sky-100 border-b border-teal-900/20">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-900 mt-1">{{ $unidad ?? 'Selecciona una unidad' }}</div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Unidad</label>
                    <select id="unidadSelector" class="form-input appearance-none">
                        <option value="">Seleccionar…</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ ($unidad ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-semibold">Al seleccionar una unidad, se cargará el formulario.</div>
                </div>
            </div>

            @if($unidad)
                <form method="POST" action="{{ route('admin.planillas.store') }}" class="mt-6">
                    @csrf

                    <input type="hidden" name="unidad" value="{{ $unidad }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Fecha y hora de revisión</label>
                            <input type="text" id="fechaRevisionDisplay" class="form-input" value="{{ old('fecha_revision_display', '') }}" disabled>
                            <input type="hidden" id="fechaRevisionHidden" name="fecha_revision" value="{{ old('fecha_revision', '') }}" required>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-semibold">Se toma automáticamente la hora actual del sistema.</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        @php($data = old('data', []))

                        @if($unidad === 'BR-3')
                            @include('admin.planillas.forms.br3', ['data' => $data])
                        @elseif($unidad === 'B-3')
                            @include('admin.planillas.forms.b3', ['data' => $data])
                        @elseif($unidad === 'RX-3')
                            @include('admin.planillas.forms.rx3', ['data' => $data])
                        @endif
                    </div>

                    <div class="mt-6 flex flex-col md:flex-row items-stretch md:items-center justify-end gap-3">
                        <x-ui.button type="submit" name="guardar_continuar" value="1" variant="secondary" size="md" icon="fas fa-pause">
                            Guardar y continuar después
                        </x-ui.button>

                        <x-ui.button type="submit" name="guardar_finalizar" value="1" variant="success" size="md" icon="fas fa-check">
                            Guardar y finalizar
                        </x-ui.button>
                    </div>
                </form>
            @else
                <div class="mt-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6 text-slate-700 dark:text-slate-300 font-semibold">
                    Selecciona una unidad para comenzar.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        const sel = document.getElementById('unidadSelector');
        if (!sel) return;
        sel.addEventListener('change', function () {
            const u = sel.value;
            const url = new URL(window.location.href);
            if (u) {
                url.searchParams.set('unidad', u);
            } else {
                url.searchParams.delete('unidad');
            }
            window.location.href = url.toString();
        });
    })();

    // DEBUG: Interceptar submit para ver qué datos se envían
    (function () {
        const form = document.querySelector('form[action*="planillas"]');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            console.log('=== FORM SUBMIT DEBUG ===');
            
            // Check all selects with cabina in name
            const allCabinaSelects = form.querySelectorAll('select[name*="cabina"]');
            console.log('Total cabina selects found:', allCabinaSelects.length);
            allCabinaSelects.forEach((sel, idx) => {
                console.log(`Select ${idx}: name="${sel.name}", value="${sel.value}", selectedIndex=${sel.selectedIndex}`);
            });
            
            // Check for duplicate names
            const allSelects = form.querySelectorAll('select');
            const nameCounts = {};
            allSelects.forEach(sel => {
                nameCounts[sel.name] = (nameCounts[sel.name] || 0) + 1;
            });
            const duplicates = Object.entries(nameCounts).filter(([name, count]) => count > 1);
            if (duplicates.length > 0) {
                console.log('DUPLICATE NAMES FOUND:', duplicates);
            }
            
            const fd = new FormData(form);
            const cabinaData = {};
            for (const [key, value] of fd.entries()) {
                if (key.includes('cabina') && key.includes('funciona')) {
                    cabinaData[key] = value;
                }
            }
            console.log('Cabina funciona in FormData:', cabinaData);
        });
    })();

    (function () {
        const display = document.getElementById('fechaRevisionDisplay');
        const hidden = document.getElementById('fechaRevisionHidden');
        if (!display || !hidden) return;

        if (!hidden.value) {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const v = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            hidden.value = v;
        }
        try {
            const d2 = new Date(hidden.value);
            if (!isNaN(d2.getTime())) {
                display.value = d2.toLocaleString();
            } else {
                display.value = hidden.value;
            }
        } catch (e) {
            display.value = hidden.value;
        }
    })();
</script>
@endsection
