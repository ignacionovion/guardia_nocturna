@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $planilla->unidad }} · {{ $planilla->fecha_revision?->format('d-m-Y H:i') }}</div>
            <div class="text-sm text-slate-600 mt-1">Registrada por: {{ $planilla->creador?->name ?? trim((string)($planilla->bombero?->nombres ?? '') . ' ' . (string)($planilla->bombero?->apellido_paterno ?? '')) ?: '—' }}</div>
            <div class="mt-2">
                @if(($planilla->estado ?? '') === 'finalizado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-emerald-100 text-emerald-900 border border-emerald-200">Finalizado</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-amber-100 text-amber-900 border border-amber-200">En edición</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(($planilla->estado ?? '') !== 'finalizado')
                <a href="{{ route('admin.planillas.edit', $planilla) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-teal-900/20 bg-sky-50 hover:bg-sky-100 text-slate-900 font-extrabold text-xs">
                    <i class="fas fa-pen"></i>
                    Continuar
                </a>
            @endif
            <form method="POST" action="{{ route('admin.planillas.destroy', $planilla) }}" class="inline" onsubmit="return confirm('¿Eliminar esta planilla? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-xs">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </form>
            <a href="{{ route('admin.planillas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
            <a href="{{ route('admin.planillas.create', ['unidad' => $planilla->unidad]) }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                <i class="fas fa-plus"></i>
                Nueva planilla
            </a>
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
        </div>
        <div class="p-6">
            @if($planilla->unidad === 'BR-3')
                @include('admin.planillas.forms.br3_readonly', ['data' => $planilla->data ?? []])
            @elseif($planilla->unidad === 'B-3')
                @include('admin.planillas.forms.b3_readonly', ['data' => $planilla->data ?? []])
            @elseif($planilla->unidad === 'RX-3')
                @include('admin.planillas.forms.rx3_readonly', ['data' => $planilla->data ?? []])
            @else
                <div class="text-slate-600 font-semibold">Detalle no disponible.</div>
            @endif
        </div>
    </div>
</div>
@endsection
