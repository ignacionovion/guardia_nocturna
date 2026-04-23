@extends('central.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-1">Vista general de la plataforma SaaS</p>
    </div>

    @php
        $opsOverall = $operationalHealth['overall'] ?? 'ok';
        // Prioriza nombre de ruta de negocio y mantiene compatibilidad con naming legacy.
        $newCompanyCreateRouteName = collect(['central.companies.create', 'central.tenants.create'])
            ->first(fn (string $routeName): bool => \Illuminate\Support\Facades\Route::has($routeName));
        $newCompanyRoute = $newCompanyCreateRouteName ? route($newCompanyCreateRouteName) : null;
        $newBodyRoute = \Illuminate\Support\Facades\Route::has('central.bodies.create') ? route('central.bodies.create') : null;
        $opsWrap = match ($opsOverall) {
            'critical' => 'border-rose-200 bg-rose-50',
            'warning' => 'border-amber-200 bg-amber-50',
            default => 'border-emerald-200 bg-emerald-50',
        };
        $opsLabel = match ($opsOverall) {
            'critical' => 'Crítico',
            'warning' => 'Atención',
            default => 'OK',
        };
    @endphp
    <div class="mb-6 rounded-2xl border {{ $opsWrap }} px-5 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-600">Salud operativa (cron / backups / billing)</p>
                <p class="text-lg font-bold text-slate-900 mt-1">Estado general: {{ $opsLabel }}</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                @foreach (['scheduler' => 'Scheduler', 'backup' => 'Backups', 'billing' => 'Billing'] as $k => $label)
                    @php $lvl = $operationalHealth[$k]['level'] ?? 'ok'; @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 font-medium bg-white/80 border border-slate-200/80">
                        <span class="w-2 h-2 rounded-full @if($lvl === 'critical') bg-rose-500 @elseif($lvl === 'warning') bg-amber-500 @else bg-emerald-500 @endif"></span>
                        {{ $label }}: {{ strtoupper($lvl) }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-700">
            <div class="rounded-xl bg-white/70 border border-slate-200/60 p-3">
                <p class="text-[10px] font-semibold uppercase text-slate-500">Scheduler</p>
                <p class="mt-1">{{ $operationalHealth['scheduler']['message'] ?? '' }}</p>
                @if(!empty($operationalHealth['scheduler']['last_at']))
                    <p class="text-xs text-slate-500 mt-1">Última señal: {{ \Illuminate\Support\Carbon::parse($operationalHealth['scheduler']['last_at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div class="rounded-xl bg-white/70 border border-slate-200/60 p-3">
                <p class="text-[10px] font-semibold uppercase text-slate-500">Backups</p>
                <p class="mt-1">{{ $operationalHealth['backup']['message'] ?? '' }}</p>
                @if(!empty($operationalHealth['backup']['payload']['at']))
                    <p class="text-xs text-slate-500 mt-1">Último job: {{ \Illuminate\Support\Carbon::parse($operationalHealth['backup']['payload']['at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div class="rounded-xl bg-white/70 border border-slate-200/60 p-3">
                <p class="text-[10px] font-semibold uppercase text-slate-500">Billing</p>
                <p class="mt-1">{{ $operationalHealth['billing']['message'] ?? '' }}</p>
                @if(!empty($operationalHealth['billing']['payload']['at']))
                    <p class="text-xs text-slate-500 mt-1">Última corrida: {{ \Illuminate\Support\Carbon::parse($operationalHealth['billing']['payload']['at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
        @if(!empty($operationalHealth['tenant_runs']))
            <details class="mt-4 text-sm">
                <summary class="cursor-pointer font-medium text-slate-800">Comandos tenant:run (guardia)</summary>
                <ul class="mt-2 space-y-1 text-xs text-slate-600">
                    @foreach($operationalHealth['tenant_runs'] as $tr)
                        <li class="flex flex-wrap gap-2">
                            <span class="font-mono text-[11px]">{{ $tr['label'] }}</span>
                            <span class="@if($tr['level'] === 'critical') text-rose-700 @elseif($tr['level'] === 'warning') text-amber-700 @else text-emerald-700 @endif">{{ strtoupper($tr['level']) }}</span>
                            <span>— {{ $tr['message'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>

    <div id="operational-alerts" class="mb-6 rounded-2xl border px-5 py-4 @if($operationalAlertsOpenCount > 0) border-rose-200 bg-rose-50/50 @else border-slate-200 bg-slate-50/80 @endif">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-600">Alertas operativas</p>
                <p class="text-lg font-bold text-slate-900 mt-0.5">
                    @if($operationalAlertsOpenCount > 0)
                        {{ $operationalAlertsOpenCount }} incidente(s) abierto(s)
                    @else
                        Sin alertas abiertas
                    @endif
                </p>
            </div>
            @if($operationalAlertsOpenCount > 0)
                <span class="inline-flex items-center rounded-full bg-rose-600 text-white text-xs font-bold px-3 py-1">{{ $operationalAlertsOpenCount }}</span>
            @endif
        </div>

        @forelse($operationalAlertsOpen as $oa)
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 py-3 border-t border-slate-200/80 first:border-t-0 first:pt-0">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-mono text-slate-500">{{ $oa->alert_key }}</span>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $oa->severity === 'critical' ? 'bg-rose-200 text-rose-900' : 'bg-amber-200 text-amber-900' }}">{{ $oa->severity }}</span>
                        <span class="text-[10px] uppercase text-slate-500">{{ $oa->source }}</span>
                    </div>
                    <p class="font-semibold text-slate-900 mt-1">{{ $oa->title }}</p>
                    <p class="text-sm text-slate-600 mt-0.5">{{ $oa->message }}</p>
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap sm:text-right">
                    @if($oa->last_triggered_at)
                        Última señal<br>
                        <span class="font-medium text-slate-700">{{ $oa->last_triggered_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-600">
                No hay incidentes abiertos. Las notificaciones por correo se envían solo ante incidentes nuevos, escalación a crítico o resolución (según configuración).
            </p>
        @endforelse
    </div>

    {{-- Main Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
            <p class="text-xs text-slate-500 mt-0.5">Compañías <span class="text-emerald-600 font-medium">({{ $activeTenantsCount }} operativas)</span></p>
            <p class="text-[11px] text-slate-400 mt-1">Tenants sin problema operativo inmediato.</p>
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
            <p class="text-[11px] text-slate-400 mt-1">Organizaciones centrales registradas.</p>
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
            <p class="text-[11px] text-slate-400 mt-1">Usuarios activos en ecosistema SaaS.</p>
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
            <p class="text-[11px] text-slate-400 mt-1">Capacidad consumida en entorno central.</p>
        </div>
    </div>

    {{-- Health + Quick Actions Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- System Health --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider mb-4">Salud del Sistema</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="text-center p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $globalMetrics['healthy'] }}</p>
                    <p class="text-xs text-emerald-600 font-medium mt-1">Saludables</p>
                    <p class="text-[10px] text-emerald-700 mt-1">Tenants sin incidentes.</p>
                </div>
                <div class="text-center p-4 rounded-xl {{ $globalMetrics['warnings'] > 0 ? 'bg-amber-50 border border-amber-100' : 'bg-white border border-slate-100' }}">
                    <p class="text-2xl font-bold {{ $globalMetrics['warnings'] > 0 ? 'text-amber-700' : 'text-slate-400' }}">{{ $globalMetrics['warnings'] }}</p>
                    <p class="text-xs {{ $globalMetrics['warnings'] > 0 ? 'text-amber-600' : 'text-slate-400' }} font-medium mt-1">Advertencias</p>
                    <p class="text-[10px] {{ $globalMetrics['warnings'] > 0 ? 'text-amber-700' : 'text-slate-400' }} mt-1">Clientes con riesgo comercial.</p>
                </div>
                <div class="text-center p-4 rounded-xl {{ $globalMetrics['errors'] > 0 ? 'bg-red-50 border border-red-100' : 'bg-white border border-slate-100' }}">
                    <p class="text-2xl font-bold {{ $globalMetrics['errors'] > 0 ? 'text-red-700' : 'text-slate-400' }}">{{ $globalMetrics['errors'] }}</p>
                    <p class="text-xs {{ $globalMetrics['errors'] > 0 ? 'text-red-600' : 'text-slate-400' }} font-medium mt-1">Errores</p>
                    <p class="text-[10px] {{ $globalMetrics['errors'] > 0 ? 'text-red-700' : 'text-slate-400' }} mt-1">Pagos aún no regularizados.</p>
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
                @if($newCompanyRoute)
                    <a href="{{ $newCompanyRoute }}"
                       class="flex items-center space-x-3 w-full bg-slate-900 text-white text-sm font-medium py-3 px-4 rounded-xl hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Nueva Compañía</span>
                    </a>
                @endif
                @if($newBodyRoute)
                    <a href="{{ $newBodyRoute }}"
                       class="flex items-center space-x-3 w-full bg-white text-slate-700 text-sm font-medium py-3 px-4 rounded-xl border border-slate-200 hover:bg-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Nuevo Cuerpo</span>
                    </a>
                @endif
                @if(!$newCompanyRoute && !$newBodyRoute)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                        No hay acciones de creación disponibles para este entorno.
                    </div>
                @endif
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

    {{-- Distribution + Backups + Expiring Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Tenants by Plan --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Por Plan</h3>
            <div class="space-y-2">
                @foreach($tenantsByPlan as $plan)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $plan['label'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 h-2 bg-white rounded-full overflow-hidden">
                                <div class="h-full {{ $loop->odd ? 'bg-blue-500' : 'bg-purple-500' }}" style="width: {{ $plan['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-slate-900 w-6 text-right">{{ $plan['count'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tenants by Estado --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Por Estado</h3>
            <div class="space-y-2">
                @foreach(['activo' => ['Activo', 'emerald'], 'trial' => ['Trial', 'blue'], 'vencido' => ['Vencido', 'red'], 'suspendido' => ['Suspendido', 'amber'], 'cancelado' => ['Cancelado', 'slate']] as $estado => $info)
                    @php $count = $tenantsByEstado[$estado] ?? 0; @endphp
                    @if($count > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $info[0] }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $info[1] }}-50 text-{{ $info[1] }}-700">{{ $count }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Backups --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Backups</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Total backups</span>
                    <span class="text-sm font-medium text-slate-900">{{ $backupCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Tamaño total</span>
                    <span class="text-sm font-medium text-slate-900">{{ $backupSize > 0 ? round($backupSize / 1024 / 1024, 1) . ' MB' : '0 B' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Último backup</span>
                    <span class="text-sm font-medium text-slate-900">{{ $lastBackup ?? '—' }}</span>
                </div>
            </div>
            <a href="{{ route('central.backups.index') }}" class="mt-3 block text-center text-xs text-blue-600 hover:underline">Ver todos →</a>
        </div>
    </div>

    {{-- Expiring Soon Warning --}}
    @if($expiringSoon->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-800">Compañías por vencer (próximos 7 días)</h3>
                <div class="mt-2 space-y-1">
                    @foreach($expiringSoon as $t)
                        <a href="{{ route('central.tenants.show', $t) }}" class="flex items-center justify-between text-sm hover:bg-amber-100 rounded px-2 py-1 -mx-2 transition">
                            <span class="text-amber-900">{{ $t->nombre }}</span>
                            <span class="text-amber-700 text-xs">{{ $t->fecha_vencimiento->format('d/m/Y') }} ({{ $t->fecha_vencimiento->diffForHumans() }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Recent tenants with health --}}
        <div class="bg-white rounded-2xl border border-slate-200">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-900">Compañías Recientes</h2>
                <a href="{{ route('central.tenants.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todas</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentTenants as $tenant)
                    @php $health = $tenantHealthMap[$tenant->id] ?? ['overall' => 'ok']; @endphp
                    <a href="{{ route('central.tenants.show', $tenant->id) }}" class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3 hover:bg-white transition block">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="relative">
                                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                    <span class="text-xs font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                                </div>
                                <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white
                                    {{ $health['overall'] === 'ok' ? 'bg-emerald-400' : ($health['overall'] === 'warning' ? 'bg-amber-400' : 'bg-red-400') }}">
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $tenant->nombre }}</p>
                                <p class="text-[10px] text-slate-400">{{ $tenant->domains->first()?->domain ?? $tenant->id }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $tenant->estadoBadgeClass() }}">
                            {{ $tenant->estadoLabel() }}
                        </span>
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400 text-sm">
                        No hay compañías registradas
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Audit Logs --}}
        <div class="bg-white rounded-2xl border border-slate-200">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-900">Actividad Reciente</h2>
                <a href="{{ route('central.audit.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todo</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAuditLogs as $log)
                    <div class="px-4 sm:px-6 py-3 flex items-start space-x-3">
                        <span class="text-lg">{{ $log->actionIcon() }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-900 truncate">{{ $log->description }}</p>
                            <p class="text-[10px] text-slate-400">{{ $log->admin_name }} · {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400 text-sm">
                        No hay actividad registrada
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
