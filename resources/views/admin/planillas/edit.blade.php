@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">Continuar planilla</div>
            <div class="text-sm text-slate-600 mt-1">Puedes guardar y seguir después.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.planillas.show', $planilla) }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Ver detalle</a>
            <a href="{{ route('admin.planillas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 text-amber-900">
            <div class="text-sm font-extrabold">{{ session('warning') }}</div>
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-sky-100 border-b border-teal-900/20">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-900 mt-1">{{ $planilla->unidad }}</div>
            <div class="mt-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-amber-100 text-amber-900 border border-amber-200">En edición</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.planillas.update', $planilla) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Unidad</div>
                    <select name="unidad" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" required>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ old('unidad', $planilla->unidad) === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Fecha y hora de revisión</div>
                    <input type="text" id="fechaRevisionDisplay" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" value="" disabled>
                    <input type="hidden" id="fechaRevisionHidden" name="fecha_revision" value="{{ old('fecha_revision', optional($planilla->fecha_revision)->format('Y-m-d\\TH:i')) }}" required>
                    <div class="text-xs text-slate-500 mt-2 font-semibold">La fecha/hora no es editable.</div>
                </div>
            </div>

            <div class="mt-6">
                @php($data = old('data', $planilla->data ?? []))

                @if(old('unidad', $planilla->unidad) === 'BR-3')
                    @include('admin.planillas.forms.br3', ['data' => $data])
                @elseif(old('unidad', $planilla->unidad) === 'B-3')
                    @include('admin.planillas.forms.b3', ['data' => $data])
                @elseif(old('unidad', $planilla->unidad) === 'RX-3')
                    @include('admin.planillas.forms.rx3', ['data' => $data])
                @else
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-700 font-semibold">
                        Esta unidad aún no está disponible.
                    </div>
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
    </div>
</div>
@endsection

<script>
    (function () {
        const display = document.getElementById('fechaRevisionDisplay');
        const hidden = document.getElementById('fechaRevisionHidden');
        if (!display || !hidden) return;
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
