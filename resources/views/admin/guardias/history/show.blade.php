@extends('layouts.modern')

@section('content')
    <div class="max-w-6xl mx-auto py-10">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight flex items-center uppercase">
                    <i class="fas fa-folder-open mr-3 text-red-700"></i> Archivo - {{ $guardia->name }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $archive->archived_at?->format('Y-m-d H:i') }}{{ $archive->label ? ' - ' . $archive->label : '' }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.guardias.history.index', $guardia->id) }}" class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-2 uppercase text-xs tracking-widest">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <div class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Filtros</div>
            </div>

            <form method="GET" action="{{ route('admin.guardias.history.show', [$guardia->id, $archive->id]) }}" class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Bombero</label>
                    <select name="firefighter_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-semibold">
                        <option value="">Todos</option>
                        @foreach(($firefighters ?? collect()) as $ff)
                            <option value="{{ $ff->id }}" @if((string)($filters['firefighter_id'] ?? '') === (string)$ff->id) selected @endif>
                                {{ trim(($ff->apellido_paterno ?? '') . ' ' . ($ff->apellido_materno ?? '') . ', ' . ($ff->nombres ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Tipo</label>
                    <select name="entity_type" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-semibold">
                        <option value="">Todos</option>
                        @foreach(($entityTypes ?? collect()) as $t)
                            <option value="{{ $t }}" @if((string)($filters['entity_type'] ?? '') === (string)$t) selected @endif>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                        <i class="fas fa-filter"></i>
                        Aplicar
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <div class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Detalle</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cada item es una captura JSON del estado al momento del cierre semanal.</div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($items as $it)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $it->entity_type }}</div>
                                <div class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $it->payload['title'] ?? ($it->payload['summary'] ?? ('ID ' . ($it->entity_id ?? ''))) }}</div>
                                @if(!empty($it->payload['date']))
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">{{ $it->payload['date'] }}</div>
                                @endif
                            </div>
                            <div class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 shrink-0">
                                #{{ $it->id }}
                            </div>
                        </div>
                        <pre class="mt-3 text-xs bg-slate-950 text-slate-100 rounded-xl p-4 overflow-auto border border-slate-800">{{ json_encode($it->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-slate-500 dark:text-slate-400 font-semibold">
                        No hay items en este archivo.
                    </div>
                @endforelse
            </div>

            @if(method_exists($items, 'links'))
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
