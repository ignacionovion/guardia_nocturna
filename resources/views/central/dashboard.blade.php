@extends('central.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Vista general de la plataforma</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Compañías</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $tenantsCount }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-emerald-600 font-medium mt-3">{{ $activeTenantsCount }} activas</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Cuerpos de Bomberos</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $bodiesCount }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Acciones Rápidas</p>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <a href="{{ route('central.tenants.create') }}"
                   class="block w-full text-center bg-slate-900 text-white text-sm font-medium py-2 px-4 rounded-xl hover:bg-slate-800 transition">
                    + Nueva Compañía
                </a>
                <a href="{{ route('central.bodies.create') }}"
                   class="block w-full text-center bg-white text-slate-700 text-sm font-medium py-2 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    + Nuevo Cuerpo
                </a>
            </div>
        </div>
    </div>

    {{-- Recent tenants --}}
    <div class="bg-white rounded-2xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Compañías Recientes</h2>
            <a href="{{ route('central.tenants.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todas</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentTenants as $tenant)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                            <span class="text-sm font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ $tenant->nombre }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $tenant->domains->first()?->domain ?? $tenant->id }}.dev-app.cl
                                @if($tenant->body) &middot; {{ $tenant->body->nombre }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $tenant->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                            {{ ucfirst($tenant->plan) }}
                        </span>
                        <a href="{{ route('central.tenants.show', $tenant) }}" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-400 text-sm">
                    No hay compañías registradas aún.
                </div>
            @endforelse
        </div>
    </div>
@endsection
