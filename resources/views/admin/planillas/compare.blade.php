@extends('layouts.modern')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-ui.page-header title="{{ $planilla->unidad }}" subtitle="Comparación de Planillas · {{ $planilla->fecha_revision->format('d/m/Y H:i') }}" icon="fas fa-exchange-alt" iconVariant="violet">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.planillas.show', $planilla) }}">
            Volver
        </x-ui.button>
    </x-ui.page-header>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
        <div class="text-sm font-extrabold text-slate-900 dark:text-white mb-4">Seleccionar planilla para comparar</div>
        <form method="GET" action="{{ route('admin.planillas.compare', $planilla) }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="form-label">Planilla anterior</label>
                <select name="comparar_con" class="form-input appearance-none">
                    <option value="">Seleccionar planilla...</option>
                    @foreach($historial as $p)
                        <option value="{{ $p->id }}" {{ request('comparar_con') == $p->id ? 'selected' : '' }}>
                            {{ $p->fecha_revision->format('d/m/Y H:i') }} - {{ $p->estado }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-exchange-alt">
                Comparar
            </x-ui.button>
        </form>
    </div>

    @if($compararCon)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
            <div class="text-sm font-extrabold text-slate-900">Resultados de la comparación</div>
            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                Comparando con planilla del {{ $compararCon->fecha_revision->format('d/m/Y H:i') }}
            </div>
        </div>
        
        @if(empty($diferencias))
            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                <i class="fas fa-check-circle text-emerald-500 text-4xl mb-4"></i>
                <div class="text-sm font-semibold">No se encontraron diferencias</div>
                <div class="text-xs mt-2">Las planillas son idénticas en su contenido</div>
            </div>
        @else
            <div class="p-4">
                @foreach($diferencias as $seccion => $items)
                    <div class="mb-6">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3">
                            {{ $seccion }}
                        </div>
                        <div class="space-y-2">
                            @foreach($items as $key => $cambios)
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4">
                                    <div class="text-sm font-bold text-slate-900 mb-3">{{ $key }}</div>
                                    
                                    @if(isset($cambios['funciona']))
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Funciona:</div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $cambios['funciona']['antes'] === 'si' ? 'bg-emerald-100 text-emerald-800' : ($cambios['funciona']['antes'] === 'no' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white') }}">
                                                    {{ $cambios['funciona']['antes'] === 'si' ? 'Sí' : ($cambios['funciona']['antes'] === 'no' ? 'No' : $cambios['funciona']['antes']) }}
                                                </span>
                                                <i class="fas fa-arrow-right text-slate-400"></i>
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $cambios['funciona']['despues'] === 'si' ? 'bg-emerald-100 text-emerald-800' : ($cambios['funciona']['despues'] === 'no' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white') }}">
                                                    {{ $cambios['funciona']['despues'] === 'si' ? 'Sí' : ($cambios['funciona']['despues'] === 'no' ? 'No' : $cambios['funciona']['despues']) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['cantidad']))
                                        <div class="flex items-center gap-4 mb-2">
                                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Cantidad:</div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white">{{ $cambios['cantidad']['antes'] }}</span>
                                                <i class="fas fa-arrow-right text-slate-400"></i>
                                                <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800">{{ $cambios['cantidad']['despues'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['novedades']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Novedades:</div>
                                            <div class="flex-1">
                                                <div class="text-xs text-slate-500 dark:text-slate-400 line-through">{{ $cambios['novedades']['antes'] }}</div>
                                                <div class="text-xs text-slate-900 font-semibold">{{ $cambios['novedades']['despues'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['nuevo']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Estado:</div>
                                            <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-plus mr-1"></i> Ítem nuevo
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($cambios['eliminado']))
                                        <div class="flex items-center gap-4">
                                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Estado:</div>
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
