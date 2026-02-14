@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">Nueva planilla</div>
            <div class="text-sm text-slate-600 mt-1">Completa la revisión semanal de la unidad.</div>
        </div>

        <a href="{{ route('admin.planillas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-sky-100 border-b border-teal-900/20">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-900 mt-1">{{ $unidad ?? 'Selecciona una unidad' }}</div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Unidad</div>
                    <select id="unidadSelector" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold">
                        <option value="">Seleccionar…</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ ($unidad ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                    <div class="text-xs text-slate-500 mt-2 font-semibold">Al seleccionar una unidad, se cargará el formulario.</div>
                </div>
            </div>

            @if($unidad)
                <form method="POST" action="{{ route('admin.planillas.store') }}" class="mt-6">
                    @csrf

                    <input type="hidden" name="unidad" value="{{ $unidad }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Fecha y hora de revisión</div>
                            <input type="text" id="fechaRevisionDisplay" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" value="{{ old('fecha_revision_display', '') }}" disabled>
                            <input type="hidden" id="fechaRevisionHidden" name="fecha_revision" value="{{ old('fecha_revision', '') }}" required>
                            <div class="text-xs text-slate-500 mt-2 font-semibold">Se toma automáticamente la hora actual del sistema.</div>
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
                        <button type="submit" name="guardar_continuar" value="1" class="inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-pause"></i>
                            Guardar y continuar después
                        </button>

                        <button type="submit" name="guardar_finalizar" value="1" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-check"></i>
                            Guardar y finalizar
                        </button>
                    </div>
                </form>
            @else
                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-700 font-semibold">
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
