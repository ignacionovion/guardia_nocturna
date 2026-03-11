@extends('central.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Vista general de la plataforma SaaS</p>
    </div>

    {{-- Main Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                @if($globalMetrics['inactive_tenants'] > 0)
                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full">{{ $globalMetrics['inactive_tenants'] }} inactivas</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-slate-900">{{ $tenantsCount }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Compañías <span class="text-emerald-600 font-medium">({{ $activeTenantsCount }} activas)</span></p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">{{ $bodiesCount }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Cuerpos de Bomberos</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">{{ $globalMetrics['total_users'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Usuarios totales</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">{{ $globalMetrics['total_db_size'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Uso total de BD</p>
        </div>
    </div>

    {{-- Health + Quick Actions Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- System Health --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider mb-4">Salud del Sistema</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $globalMetrics['healthy'] }}</p>
                    <p class="text-xs text-emerald-600 font-medium mt-1">Saludables</p>
                </div>
                <div class="text-center p-4 rounded-xl {{ $globalMetrics['warnings'] > 0 ? 'bg-amber-50 border border-amber-100' : 'bg-slate-50 border border-slate-100' }}">
                    <p class="text-2xl font-bold {{ $globalMetrics['warnings'] > 0 ? 'text-amber-700' : 'text-slate-400' }}">{{ $globalMetrics['warnings'] }}</p>
                    <p class="text-xs {{ $globalMetrics['warnings'] > 0 ? 'text-amber-600' : 'text-slate-400' }} font-medium mt-1">Advertencias</p>
                </div>
                <div class="text-center p-4 rounded-xl {{ $globalMetrics['errors'] > 0 ? 'bg-red-50 border border-red-100' : 'bg-slate-50 border border-slate-100' }}">
                    <p class="text-2xl font-bold {{ $globalMetrics['errors'] > 0 ? 'text-red-700' : 'text-slate-400' }}">{{ $globalMetrics['errors'] }}</p>
                    <p class="text-xs {{ $globalMetrics['errors'] > 0 ? 'text-red-600' : 'text-slate-400' }} font-medium mt-1">Errores</p>
                </div>
            </div>
            @if($globalMetrics['expiring_soon'] > 0)
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <span><strong>{{ $globalMetrics['expiring_soon'] }}</strong> {{ $globalMetrics['expiring_soon'] === 1 ? 'compañía vence' : 'compañías vencen' }} en los próximos 30 días</span>
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider mb-4">Acciones Rápidas</h2>
            <div class="space-y-2">
                <a href="{{ route('central.tenants.create') }}"
                   class="flex items-center space-x-3 w-full bg-slate-900 text-white text-sm font-medium py-3 px-4 rounded-xl hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Compañía</span>
                </a>
                <a href="{{ route('central.bodies.create') }}"
                   class="flex items-center space-x-3 w-full bg-white text-slate-700 text-sm font-medium py-3 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Cuerpo</span>
                </a>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Resumen</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Almacenamiento</dt>
                        <dd class="font-medium text-slate-900">{{ $globalMetrics['total_storage'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Base de datos</dt>
                        <dd class="font-medium text-slate-900">{{ $globalMetrics['total_db_size'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Recent tenants with health --}}
    <div class="bg-white rounded-2xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Compañías</h2>
            <a href="{{ route('central.tenants.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todas</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentTenants as $tenant)
                @php $health = $tenantHealthMap[$tenant->id] ?? ['overall' => 'ok']; @endphp
                <a href="{{ route('central.tenants.show', $tenant->id) }}" class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition block">
                    <div class="flex items-center space-x-4">
                        {{-- Health indicator --}}
                        <div class="relative">
                            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                                <span class="text-sm font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                            </div>
                            <div class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white
                                {{ $health['overall'] === 'ok' ? 'bg-emerald-400' : ($health['overall'] === 'warning' ? 'bg-amber-400' : 'bg-red-400') }}">
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ $tenant->nombre }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $tenant->domains->first()?->domain ?? $tenant->id }}
                                @if($tenant->body) &middot; {{ $tenant->body->nombre }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $tenant->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $tenant->plan === 'enterprise' ? 'bg-purple-50 text-purple-700' : ($tenant->plan === 'profesional' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($tenant->plan) }}
                        </span>
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <p class="text-slate-500 text-sm mb-3">No hay compañías registradas</p>
                    <a href="{{ route('central.tenants.create') }}" class="text-sm text-blue-600 hover:underline font-medium">Crear la primera compañía</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
