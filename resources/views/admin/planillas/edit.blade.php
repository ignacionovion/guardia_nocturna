@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="Continuar planilla" subtitle="Puedes guardar y seguir después." icon="fas fa-clipboard-list" iconVariant="cyan">
        <x-ui.button variant="secondary" size="md" icon="fas fa-eye" href="{{ route('admin.planillas.show', $planilla) }}">
            Ver detalle
        </x-ui.button>
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.planillas.index') }}">
            Volver
        </x-ui.button>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif
    @if(session('warning'))
        <x-ui.alert type="warning" icon="fas fa-exclamation-triangle" class="mb-6">
            {{ session('warning') }}
        </x-ui.alert>
    @endif

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
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
                    <label class="form-label">Unidad</label>
                    <select name="unidad" class="form-input appearance-none" required>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ old('unidad', $planilla->unidad) === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Fecha y hora de revisión</label>
                    <input type="text" id="fechaRevisionDisplay" class="form-input" value="" disabled>
                    <input type="hidden" id="fechaRevisionHidden" name="fecha_revision" value="{{ old('fecha_revision', optional($planilla->fecha_revision)->format('Y-m-d\\TH:i')) }}" required>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-semibold">La fecha/hora no es editable.</div>
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
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6 text-slate-700 dark:text-slate-300 font-semibold">
                        Esta unidad aún no está disponible.
                    </div>
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
