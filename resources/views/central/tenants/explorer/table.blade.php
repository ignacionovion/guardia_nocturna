@extends('central.layouts.app')

@section('title', $table . ' - ' . $tenant->nombre)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.tenants.explorer', $tenant) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver al explorador</span>
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tabla: {{ $table }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $total }} registros totales</p>
        </div>
        <form method="GET" action="{{ route('central.tenants.explorer.table', [$tenant, $table]) }}" class="flex items-center space-x-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar..."
                   class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64">
            <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800 transition">
                Buscar
            </button>
            @if($search)
                <a href="{{ route('central.tenants.explorer.table', [$tenant, $table]) }}" class="text-sm text-slate-500 hover:text-slate-700 px-2">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    @if($records->isEmpty())
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No hay registros{{ $search ? ' que coincidan con la búsqueda' : '' }}.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            @foreach($columns as $column)
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $column }}
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($records as $record)
                            <tr class="hover:bg-slate-50 transition">
                                @foreach($columns as $column)
                                    <td class="px-4 py-3 text-slate-700 whitespace-nowrap max-w-xs truncate" title="{{ is_string($record->$column) ? $record->$column : json_encode($record->$column) }}">
                                        @php
                                            $value = $record->$column;
                                            if (is_null($value)) {
                                                echo '<span class="text-slate-300 italic">NULL</span>';
                                            } elseif (is_bool($value)) {
                                                echo $value ? '<span class="text-emerald-600">true</span>' : '<span class="text-red-600">false</span>';
                                            } elseif (is_array($value) || is_object($value)) {
                                                echo '<code class="text-xs bg-slate-100 px-1 rounded">' . json_encode($value) . '</code>';
                                            } else {
                                                echo e(Str::limit($value, 50));
                                            }
                                        @endphp
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-right">
                                    @php
                                        $primaryKey = $columns[0] ?? 'id';
                                        $id = $record->$primaryKey;
                                    @endphp
                                    <a href="{{ route('central.tenants.explorer.record', [$tenant, $table, $id]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
