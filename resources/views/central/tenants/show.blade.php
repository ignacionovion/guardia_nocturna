@extends('central.layouts.app')

@section('title', $tenant->nombre)

@section('content')
    @php
        $planSlug = $tenant->planRelation?->slug;
        $planName = $tenant->planRelation?->nombre ?? 'Sin plan';
        $planBadgeClass = $planSlug ? 'bg-blue-50 text-blue-700' : 'bg-white text-slate-600';
    @endphp

    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a compañías</span>
        </a>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center">
                        <span class="text-xl font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white
                        {{ $health['overall'] === 'ok' ? 'bg-emerald-400' : ($health['overall'] === 'warning' ? 'bg-amber-400' : 'bg-red-400') }}">
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $tenant->nombre }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">
                        <code class="text-xs bg-white px-1.5 py-0.5 rounded">{{ $tenant->id }}</code>
                        &middot;
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tenant->estadoBadgeClass() }}">
                            {{ $tenant->estadoLabel() }}
                        </span>
                        &middot;
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $planBadgeClass }}">
                            {{ $planName }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($tenant->domains->first())
                    <a href="https://{{ $tenant->domains->first()->domain }}" target="_blank"
                       class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Abrir</span>
                    </a>
                @endif
                <a href="{{ route('central.tenants.edit', $tenant->id) }}"
                   class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-white transition">
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
                            {{ $planBadgeClass }}">
                            {{ $planName }}
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
                    <dd><code class="text-xs bg-white px-2 py-1 rounded text-slate-600">{{ $tenant->database()->getName() }}</code></dd>
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
                                <code class="text-xs bg-white px-2 py-1 rounded text-slate-600">{{ $domain->domain }}</code>
                                <a href="https://{{ $domain->domain }}" target="_blank" class="text-xs text-blue-600 hover:underline">Abrir</a>
                            </div>
                        @empty
                            <span class="text-xs text-red-500 font-medium">Sin dominios configurados</span>
                        @endforelse
                    </dd>
                </div>
            </dl>

            {{-- Plan Usage --}}
            @if($usageInfo)
            <div class="mt-5 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Uso del Plan</h3>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full
                        {{ $planBadgeClass }}">
                        {{ $planName }}
                    </span>
                </div>
                <div class="space-y-2">
                    @foreach($usageInfo as $type => $info)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600">{{ match($type) { 'users' => 'Usuarios', 'guardias' => 'Guardias', 'beds' => 'Camas', 'storage' => 'Almacenamiento', default => $type } }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-24 h-2 bg-white rounded-full overflow-hidden">
                                @if($info['unlimited'])
                                    <div class="h-full bg-emerald-500 w-full"></div>
                                @elseif($info['percentage'] !== null)
                                    <div class="h-full {{ $info['percentage'] >= 90 ? 'bg-red-500' : ($info['percentage'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $info['percentage']) }}%"></div>
                                @endif
                            </div>
                            <span class="text-slate-500 w-20 text-right">
                                @if($info['unlimited'])
                                    {{ $info['current'] }} / ∞
                                @else
                                    {{ $info['current'] }} / {{ $info['limit'] }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                {{-- Change Plan Form --}}
                @if(isset($availablePlans) && $availablePlans->count() > 0)
                <form method="POST" action="{{ route('central.tenants.change-plan', $tenant) }}" class="mt-4 pt-3 border-t border-slate-100">
                    @csrf
                    <div class="flex items-end space-x-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-medium text-slate-500 mb-1 uppercase">Cambiar Plan</label>
                            <select name="plan_id" class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                @foreach($availablePlans as $planOption)
                                    <option value="{{ $planOption->id }}" {{ $tenant->plan_id == $planOption->id ? 'selected' : '' }}>
                                        {{ $planOption->nombre }} ({{ $planOption->precio_mensual > 0 ? '$' . $planOption->precio_mensual . '/mes' : 'Gratis' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" onclick="return confirm('¿Cambiar el plan de {{ $tenant->nombre }}?')"
                                class="px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition">
                            Cambiar
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @endif

            {{-- Auto-provisioning info --}}
            <div class="mt-5 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Auto-provisioning</h3>
                <div class="bg-white rounded-lg p-3 space-y-1.5">
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
                <span class="text-[10px] text-slate-400 font-medium">Los valores por defecto provienen del plan {{ $planName }}</span>
            </div>
            <form method="POST" action="{{ route('central.tenants.features', $tenant->id) }}">
                @csrf
                @php
                    $featureService = app(\App\Services\FeatureFlagService::class);
                    $modules = $featureService->allModules($tenant);
                    $addons = $featureService->allAddons($tenant);
                    $moduleLabels = \App\Services\FeatureFlagService::moduleLabels();
                    $addonLabels = \App\Services\FeatureFlagService::addonLabels();
                    $overrides = $tenant->features ?? [];
                @endphp
                
                {{-- Módulos del Sistema --}}
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Módulos del Sistema
                    </h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($modules as $module => $value)
                            <label class="flex items-center justify-between py-2 px-3 rounded-lg {{ $value ? 'bg-emerald-50 border border-emerald-200' : 'bg-white border border-slate-200' }} cursor-pointer hover:bg-white transition">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-slate-700 font-medium">{{ $moduleLabels[$module] ?? $module }}</span>
                                    @if(array_key_exists($module, $overrides))
                                        <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-1 py-0.5 rounded">CUSTOM</span>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input type="hidden" name="features[{{ $module }}]" value="0">
                                    <input type="checkbox" name="features[{{ $module }}]" value="1"
                                           {{ $value ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 rounded-full peer-checked:bg-emerald-500 transition"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full peer-checked:translate-x-4 transition shadow-sm"></div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Addons SaaS --}}
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Addons SaaS / Comerciales
                    </h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($addons as $addon => $value)
                            <label class="flex items-center justify-between py-2 px-3 rounded-lg {{ $value ? 'bg-purple-50 border border-purple-200' : 'bg-white border border-slate-200' }} cursor-pointer hover:bg-white transition">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-slate-700 font-medium">{{ $addonLabels[$addon] ?? $addon }}</span>
                                    @if(array_key_exists($addon, $overrides))
                                        <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-1 py-0.5 rounded">CUSTOM</span>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input type="hidden" name="features[{{ $addon }}]" value="0">
                                    <input type="checkbox" name="features[{{ $addon }}]" value="1"
                                           {{ $value ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 rounded-full peer-checked:bg-purple-500 transition"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full peer-checked:translate-x-4 transition shadow-sm"></div>
                                </div>
                            </label>
                        @endforeach
                    </div>
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
                            class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-white transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        <span>Correr Migraciones</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('central.tenants.run-seed', $tenant->id) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('¿Ejecutar seeders para {{ $tenant->nombre }}? Esto puede crear datos duplicados.')"
                            class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-white transition">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Correr Seeders</span>
                    </button>
                </form>

                @if($tenant->domains->first())
                    <a href="https://{{ $tenant->domains->first()->domain }}" target="_blank"
                       class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-white transition">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Abrir App del Tenant</span>
                    </a>
                @endif

                <a href="{{ route('central.tenants.timeline', $tenant) }}"
                   class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-white transition">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Ver Timeline</span>
                </a>

                <a href="{{ route('central.tenants.admin', $tenant) }}"
                   class="w-full flex items-center space-x-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 hover:bg-white transition">
                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Administración Técnica</span>
                </a>
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

    {{-- Impersonation --}}
    @if(!empty($tenantUsers))
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-semibold text-slate-900 text-sm">Impersonar Usuario</h2>
                <p class="text-xs text-slate-400 mt-0.5">Accede al sistema como si fueras un usuario de este tenant</p>
            </div>
            <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-1 rounded font-medium">⚠️ Se registra en auditoría</span>
        </div>
        <form method="POST" action="{{ route('central.tenants.impersonate', $tenant) }}" class="flex items-end space-x-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-500 mb-1">Seleccionar usuario</label>
                <select name="user_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="">— Seleccionar —</option>
                    @foreach($tenantUsers as $user)
                        <option value="{{ $user['id'] }}">
                            {{ $user['name'] }} ({{ $user['role'] }}) — {{ $user['email'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" onclick="return confirm('¿Impersonar este usuario? La acción quedará registrada en auditoría.')"
                    class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition flex items-center space-x-1.5">
                <span>🎭</span>
                <span>Impersonar</span>
            </button>
        </form>
    </div>
    @endif
@endsection
