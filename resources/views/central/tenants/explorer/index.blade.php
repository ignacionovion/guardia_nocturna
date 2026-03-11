@extends('central.layouts.app')

@section('title', 'Explorador de Datos - ' . $tenant->nombre)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.tenants.show', $tenant) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a {{ $tenant->nombre }}</span>
        </a>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Explorador de Datos</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $tenant->nombre }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $tenant->estadoBadgeClass() }}">
            {{ $tenant->estadoLabel() }}
        </span>
    </div>

    @if(empty($tables))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <svg class="w-12 h-12 mx-auto text-amber-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-amber-800 font-medium">La base de datos aún no tiene tablas.</p>
            <p class="text-amber-600 text-sm mt-1">Ejecuta las migraciones primero.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Tablas disponibles ({{ count($tables) }})</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($tables as $table)
                    <a href="{{ route('central.tenants.explorer.table', [$tenant, $table]) }}" class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-slate-900">{{ $table }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-slate-400">Ver datos</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection
