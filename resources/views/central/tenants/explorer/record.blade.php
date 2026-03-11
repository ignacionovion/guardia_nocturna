@extends('central.layouts.app')

@section('title', 'Registro - ' . $table)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.tenants.explorer.table', [$tenant, $table]) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a {{ $table }}</span>
        </a>
    </div>

    <h1 class="text-2xl font-bold text-slate-900 mb-6">Detalle del Registro</h1>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="font-semibold text-slate-900">Tabla: {{ $table }}</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($columns as $column)
                <div class="px-6 py-4 flex items-start space-x-4">
                    <div class="w-32 flex-shrink-0">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $column }}</span>
                    </div>
                    <div class="flex-1">
                        @php
                            $value = $record->$column ?? null;
                            if (is_null($value)) {
                                echo '<span class="text-slate-300 italic">NULL</span>';
                            } elseif (is_bool($value)) {
                                echo $value
                                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">true</span>'
                                    : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">false</span>';
                            } elseif (is_array($value) || is_object($value)) {
                                echo '<pre class="text-xs bg-slate-50 p-2 rounded overflow-x-auto">' . json_encode($value, JSON_PRETTY_PRINT) . '</pre>';
                            } elseif (strtotime($value) !== false && strlen($value) > 10) {
                                // Likely a datetime
                                echo '<span class="text-slate-700">' . e($value) . '</span>';
                                echo '<span class="text-xs text-slate-400 ml-2">(' . \Carbon\Carbon::parse($value)->diffForHumans() . ')</span>';
                            } else {
                                echo '<span class="text-slate-700 font-mono text-sm">' . e($value) . '</span>';
                            }
                        @endphp
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
