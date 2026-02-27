@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Comparación de Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $planilla->unidad }}</div>
            <div class="text-sm text-slate-600 mt-1">Fecha: {{ $planilla->fecha_revision->format('d/m/Y H:i') }}</div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.planillas.show', $planilla) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="text-sm font-extrabold text-slate-900 mb-4">Seleccionar planilla para comparar</div>
        <form method="GET" action="{{ route('admin.planillas.compare', $planilla) }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-600 mb-2">Planilla anterior</label>
                <select name="comparar_con" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold">
                    <option value="">Seleccionar planilla...</option>
                    @foreach($historial as $p)
                        <option value="{{ $p->id }}" {{ request('comparar_con') == $p->id ? 'selected' : '' }}>
                            {{ $p->fecha_revision->format('d/m/Y H:i') }} - {{ $p->estado }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-teal-900/20 bg-sky-50 hover:bg-sky-100 text-slate-900 font-extrabold text-xs">
                <i class="fas fa-exchange-alt"></i>
                Comparar
            </button>
        </form>
    </div>

    @if($compararCon)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <div class="text-sm font-extrabold text-slate-900">Resultados de la comparación</div>
            <div class="text-xs text-slate-600 mt-1">
                Comparando con planilla del {{ $compararCon->fecha_revision->format('d/m/Y H:i') }}
            </div>
        </div>
        
        @if(empty($diferencias))
            <div class="p-8 text-center text-slate-500">
                <i class="fas fa-check-circle text-emerald-500 text-4xl mb-4"></i>
                <div class="text-sm font-semibold">No se encontraron diferencias</div>
                <div class="text-xs mt-2">Las planillas son idénticas en su contenido</div>
            </div>
        @else
            <div class="p-4">
                @foreach($diferencias as $seccion => $items)
                    <div class="mb-6">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-3">
                            {{ $seccion }}
                        </div>
                        <div class="space-y-2">
                            @foreach($items as $key => $cambios)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-bold text-slate-900 mb-3">{{ $key }}</div>
                                    
                                    @if(isset($cambios['funciona']))
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="text-xs font-semibold text-slate-500 w-24">Funciona:</div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $cambios['funciona']['antes'] === 'si' ? 'bg-emerald-100 text-emerald-800' : ($cambios['funciona']['antes'] === 'no' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800') }}">
                                                    {{ $cambios['funciona']['antes'] === 'si' ? 'Sí' : ($cambios['funciona']['antes'] === 'no' ? 'No' : $cambios['funciona']['antes']) }}
                                                </span>
                                                <i class="fas fa-arrow-right text-slate-400"></i>
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $cambios['funciona']['despues'] === 'si' ? 'bg-emerald-100 text-emerald-800' : ($cambios['funciona']['despues'] === 'no' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800') }}">
                                                    {{ $cambios['funciona']['despues'] === 'si' ? 'Sí' : ($cambios['funciona']['despues'] === 'no' ? 'No' : $cambios['funciona']['despues']) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['cantidad']))
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="text-xs font-semibold text-slate-500 w-24">Cantidad:</div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-800">{{ $cambios['cantidad']['antes'] }}</span>
                                                <i class="fas fa-arrow-right text-slate-400"></i>
                                                <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">{{ $cambios['cantidad']['despues'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['novedades']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 w-24">Novedades:</div>
                                            <div class="flex-1">
                                                <div class="text-xs text-slate-500 line-through">{{ $cambios['novedades']['antes'] }}</div>
                                                <div class="text-xs text-slate-900 font-semibold">{{ $cambios['novedades']['despues'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['nuevo']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 w-24">Estado:</div>
                                            <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-plus mr-1"></i> Ítem nuevo
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['eliminado']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 w-24">Estado:</div>
                                            <span class="px-2 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800">
                                                <i class="fas fa-trash mr-1"></i> Ítem eliminado
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
