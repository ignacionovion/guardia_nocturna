@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="{{ $planilla->unidad }} · {{ $planilla->fecha_revision?->format('d-m-Y H:i') }}" subtitle="Registrada por: {{ $planilla->creador?->name ?? trim((string)($planilla->bombero?->nombres ?? '') . ' ' . (string)($planilla->bombero?->apellido_paterno ?? '')) ?: '—' }}" icon="fas fa-clipboard-list" iconVariant="cyan">
        <div class="flex flex-wrap items-center gap-2">
            @if(($planilla->estado ?? '') !== 'finalizado')
                <x-ui.button variant="secondary" size="sm" icon="fas fa-pen" href="{{ route('admin.planillas.edit', $planilla) }}">
                    Continuar
                </x-ui.button>
            @endif
            <x-ui.button variant="secondary" size="sm" icon="fas fa-exchange-alt" href="{{ route('admin.planillas.compare', $planilla) }}">
                Comparar
            </x-ui.button>
            <form method="POST" action="{{ route('admin.planillas.destroy', $planilla) }}" class="inline" onsubmit="return confirm('¿Eliminar esta planilla? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash">
                    Eliminar
                </x-ui.button>
            </form>
            <x-ui.button variant="secondary" size="sm" icon="fas fa-arrow-left" href="{{ route('admin.planillas.index') }}">
                Volver
            </x-ui.button>
            <x-ui.button variant="primary" size="sm" icon="fas fa-plus" href="{{ route('admin.planillas.create', ['unidad' => $planilla->unidad]) }}">
                Nueva planilla
            </x-ui.button>
        </div>
    </x-ui.page-header>

    <div class="mb-4">
        @if(($planilla->estado ?? '') === 'finalizado')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-100 text-emerald-900 border border-emerald-200">Finalizado</span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-amber-100 text-amber-900 border border-amber-200">En edición</span>
        @endif
    </div>

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
        </div>
        <div class="p-6">
            @php($data = $planilla->data ?? [])
            @php($readonly = true)
            
            @if($planilla->unidad === 'BR-3')
                @include('admin.planillas.forms.br3', ['data' => $data, 'readonly' => true])
            @elseif($planilla->unidad === 'B-3')
                @include('admin.planillas.forms.b3', ['data' => $data, 'readonly' => true])
            @elseif($planilla->unidad === 'RX-3')
                @include('admin.planillas.forms.rx3', ['data' => $data, 'readonly' => true])
            @else
                <div class="text-slate-600 dark:text-slate-400 font-semibold">Detalle no disponible.</div>
            @endif
        </div>
    </div>
</div>
@endsection
