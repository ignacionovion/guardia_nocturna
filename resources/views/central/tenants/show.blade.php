@extends('central.layouts.app')

@section('title', $tenant->nombre)

@section('content')
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a compañías</span>
        </a>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center">
                        <span class="text-xl font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white
                        {{ $health['overall'] === 'ok' ? 'bg-emerald-400' : ($health['overall'] === 'warning' ? 'bg-amber-400' : 'bg-red-400') }}">
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $tenant->nombre }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">
                        <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $tenant->id }}</code>
                        &middot;
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tenant->estadoBadgeClass() }}">
                            {{ $tenant->estadoLabel() }}
                        </span>
                        &middot;
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $tenant->plan === 'enterprise' ? 'bg-purple-50 text-purple-700' : ($tenant->plan === 'profesional' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($tenant->plan) }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($tenant->domains->first())
                    <a href="http://{{ $tenant->domains->first()->domain }}.{{ env('CENTRAL_DOMAIN', 'localhost') }}" target="_blank"
                       class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Abrir</span>
                    </a>
                @endif
                <a href="{{ route('central.tenants.edit', $tenant->id) }}"
                   class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Editar
                </a>
                <form method="POST" action="{{ route('central.tenants.destroy', $tenant->id) }}"
                      onsubmit="return confirm('¿Eliminar esta compañía y TODA su base de datos? Esta acción es irreversible.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500 font-medium">Base de datos</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $metrics['db_size'] }}</p>
            <p class="text-[10px] text-slate-400">{{ $metrics['table_count'] }} tablas</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500 font-medium">Usuarios</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $metrics['users_count'] }}</p>
            <p class="text-[10px] text-emerald-600 font-medium">{{ $metrics['active_users'] }} activos</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500 font-medium">Sesiones</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $metrics['sessions_count'] }}</p>
            <p class="text-[10px] text-slate-400">activas</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500 font-medium">Archivos</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $metrics['storage_size'] }}</p>
            <p class="text-[10px] text-slate-400">almacenamiento</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500 font-medium">Migraciones</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $metrics['migrations'] }}</p>
            <p class="text-[10px] text-slate-400">ejecutadas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Health Checks --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900 text-sm">Estado de Salud</h2>
                @php
                    $overallColors = ['ok' => 'bg-emerald-50 text-emerald-700', 'warning' => 'bg-amber-50 text-amber-700', 'error' => 'bg-red-50 text-red-700'];
                    $overallLabels = ['ok' => 'Saludable', 'warning' => 'Advertencia', 'error' => 'Error'];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $overallColors[$health['overall']] }}">
                    {{ $overallLabels[$health['overall']] }}
                </span>
            </div>
            <div class="space-y-3">
                @foreach($health['checks'] as $check)
                    <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                        <div class="flex items-center space-x-2.5">
                            @if($check['status'] === 'ok')
                                <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @elseif($check['status'] === 'warning')
                                <div class="w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01"/></svg>
                                </div>
                            @else
                                <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                            @endif
                            <span class="text-sm text-slate-700 font-medium">{{ $check['name'] }}</span>
                        </div>
                        <span class="text-xs text-slate-500">{{ $check['detail'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Info --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm mb-4">Información</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Nombre</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->nombre }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Número</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->numero ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Cuerpo</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->body?->nombre ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Plan</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $tenant->plan === 'enterprise' ? 'bg-purple-50 text-purple-700' : ($tenant->plan === 'profesional' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($tenant->plan) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Vencimiento</dt>
                    <dd class="text-sm font-medium {{ $tenant->fecha_vencimiento?->isPast() ? 'text-red-600' : 'text-slate-900' }}">
                        {{ $tenant->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                        @if($tenant->fecha_vencimiento?->isPast())
                            <span class="text-[10px] text-red-500 font-bold ml-1">VENCIDA</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Creada</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($metrics['last_login'])
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-500">Último login</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ $metrics['last_login'] }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Technical --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm mb-4">Datos Técnicos</h2>
            <dl class="space-y-3">
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-slate-500">Base de datos</dt>
                    <dd><code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">{{ $tenant->database()->getName() }}</code></dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-slate-500">DB existe</dt>
                    <dd>
                        @if($metrics['db_exists'])
                            <span class="text-xs text-emerald-600 font-medium">Si</span>
                        @else
                            <span class="text-xs text-red-600 font-medium">No</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 mb-2">Dominios</dt>
                    <dd class="space-y-1.5">
                        @forelse($tenant->domains as $domain)
                            <div class="flex items-center space-x-2">
                                <code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">{{ $domain->domain }}</code>
                                <a href="http://{{ $domain->domain }}.{{ env('CENTRAL_DOMAIN', 'localhost') }}" target="_blank" class="text-xs text-blue-600 hover:underline">Abrir</a>
                            </div>
                        @empty
                            <span class="text-xs text-red-500 font-medium">Sin dominios configurados</span>
                        @endforelse
                    </dd>
                </div>
            </dl>

            {{-- Auto-provisioning info --}}
            <div class="mt-5 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Auto-provisioning</h3>
                <div class="bg-slate-50 rounded-lg p-3 space-y-1.5">
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="w-4 h-4 rounded-full {{ $metrics['db_exists'] ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center text-[10px] font-bold">{{ $metrics['db_exists'] ? '1' : '!' }}</span>
                        <span class="text-slate-600">Crear DB</span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="w-4 h-4 rounded-full {{ $metrics['migrations'] > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center text-[10px] font-bold">{{ $metrics['migrations'] > 0 ? '2' : '!' }}</span>
                        <span class="text-slate-600">Correr migraciones</span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="w-4 h-4 rounded-full {{ $metrics['users_count'] > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-[10px] font-bold">{{ $metrics['users_count'] > 0 ? '3' : '—' }}</span>
                        <span class="text-slate-600">Correr seed</span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="w-4 h-4 rounded-full {{ $metrics['users_count'] > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-[10px] font-bold">{{ $metrics['users_count'] > 0 ? '4' : '—' }}</span>
                        <span class="text-slate-600">Crear admin</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Feature Flags + Manual Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Feature Flags --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900 text-sm">Feature Flags</h2>
                <span class="text-[10px] text-slate-400 font-medium">Los valores por defecto provienen del plan {{ ucfirst($tenant->plan) }}</span>
            </div>
            <form method="POST" action="{{ route('central.tenants.features', $tenant->id) }}">
                @csrf
                @php
                    $featureService = app(\App\Services\FeatureFlagService::class);
                    $allFeatures = $featureService->all($tenant);
                    $labels = \App\Services\FeatureFlagService::featureLabels();
                    $planDefaults = \App\Services\FeatureFlagService::planDefaults($tenant->plan);
                    $overrides = $tenant->features ?? [];
                @endphp
                <div class="grid grid-cols-2 gap-3 mb-4">
                    @foreach($allFeatures as $feature => $value)
                        @if($feature === 'max_users')
                            <div class="col-span-2 flex items-center justify-between py-2 px-3 rounded-lg bg-slate-50">
                                <span class="text-sm text-slate-700 font-medium">{{ $labels[$feature] ?? $feature }}</span>
                                <div class="flex items-center space-x-2">
                                    <input type="number" name="features[{{ $feature }}]" value="{{ $value }}"
                                           class="w-20 text-sm text-center border border-slate-200 rounded-lg py-1 px-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           min="-1" placeholder="-1=∞">
                                    <span class="text-[10px] text-slate-400">(-1 = ilimitado)</span>
                                </div>
                            </div>
                        @else
                            <label class="flex items-center justify-between py-2 px-3 rounded-lg {{ $value ? 'bg-emerald-50' : 'bg-slate-50' }} cursor-pointer hover:bg-slate-100 transition">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-slate-700 font-medium">{{ $labels[$feature] ?? $feature }}</span>
                                    @if(array_key_exists($feature, $overrides))
                                        <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-1 py-0.5 rounded">CUSTOM</span>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input type="hidden" name="features[{{ $feature }}]" value="0">
                                    <input type="checkbox" name="features[{{ $feature }}]" value="1"
                                           {{ $value ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 rounded-full peer-checked:bg-emerald-500 transition"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full peer-checked:translate-x-4 transition shadow-sm"></div>
                                </div>
                            </label>
                        @endif
                    @endforeach
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white text-sm font-medium py-2.5 rounded-xl hover:bg-slate-800 transition">
                    Guardar Features
                </button>
            </form>
        </div>

        {{-- Manual Actions --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm mb-4">Acciones Manuales</h2>
            <div class="space-y-3">
                <form method="POST" action="{{ route('central.tenants.run-migrations', $tenant->id) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('¿Ejecutar migraciones para {{ $tenant->nombre }}?')"
                            class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        <span>Correr Migraciones</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('central.tenants.run-seed', $tenant->id) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('¿Ejecutar seeders para {{ $tenant->nombre }}? Esto puede crear datos duplicados.')"
                            class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Correr Seeders</span>
                    </button>
                </form>

                @if($tenant->domains->first())
                    <a href="http://{{ $tenant->domains->first()->domain }}.{{ env('CENTRAL_DOMAIN', 'localhost') }}" target="_blank"
                       class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Abrir App del Tenant</span>
                    </a>
                @endif
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Peligro</h3>
                <form method="POST" action="{{ route('central.tenants.destroy', $tenant->id) }}"
                      onsubmit="return confirm('¿ELIMINAR {{ $tenant->nombre }} y TODA su base de datos? Esta acción es IRREVERSIBLE.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full flex items-center space-x-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl py-3 px-4 hover:bg-red-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Eliminar Compañía</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
