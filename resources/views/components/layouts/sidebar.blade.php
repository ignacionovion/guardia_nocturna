@props([
    'mobile' => false,
])

@php
$user = Auth::user();
$role = $user->role ?? 'guardia';

$menuItems = [
    'super_admin' => [
        ['route' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard', 'match' => 'dashboard'],
        ['divider' => true, 'label' => 'Centro de Comando'],
        ['route' => 'guardia.now', 'icon' => 'fas fa-satellite-dish', 'label' => 'Guardia en Vivo', 'match' => 'guardia.now*', 'feature' => 'now', 'badge' => 'live'],
        ['route' => 'admin.guardias', 'icon' => 'fas fa-shield-halved', 'label' => 'Guardias', 'match' => 'admin.guardias*', 'feature' => 'guardia'],
        ['route' => 'camas', 'icon' => 'fas fa-bed', 'label' => 'Camas', 'match' => 'camas*', 'feature' => 'camas'],
        ['route' => 'admin.calendario', 'icon' => 'fas fa-calendar-days', 'label' => 'Calendario', 'match' => 'admin.calendario*', 'feature' => 'calendario'],
        ['divider' => true, 'label' => 'Personal'],
        ['route' => 'admin.volunteers.index', 'icon' => 'fas fa-user-group', 'label' => 'Voluntarios', 'match' => 'admin.volunteers*', 'feature' => 'voluntarios'],
        ['route' => 'admin.emergencies.index', 'icon' => 'fas fa-truck-medical', 'label' => 'Emergencias', 'match' => 'admin.emergencies*', 'feature' => 'emergencias'],
        ['route' => 'admin.dotaciones', 'icon' => 'fas fa-users-gear', 'label' => 'Dotaciones', 'match' => 'admin.dotaciones*', 'feature' => 'dotaciones'],
        ['divider' => true, 'label' => 'Módulos'],
        ['route' => 'admin.preventivas.index', 'icon' => 'fas fa-clipboard-check', 'label' => 'Preventivas', 'match' => 'admin.preventivas*', 'feature' => 'preventiva'],
        ['route' => 'forms.execution.index', 'icon' => 'fas fa-clipboard-list', 'label' => 'Formularios', 'match' => 'forms*'],
        ['route' => 'admin.reports.index', 'icon' => 'fas fa-chart-line', 'label' => 'Reportes', 'match' => 'admin.reports*', 'feature' => 'reportes'],
        ['divider' => true, 'label' => 'Configuración'],
        ['divider' => true, 'label' => 'Sistema'],
        ['route' => 'admin.users.index', 'icon' => 'fas fa-user-shield', 'label' => 'Usuarios', 'match' => 'admin.users*'],
        ['route' => 'admin.system.index', 'icon' => 'fas fa-sliders', 'label' => 'Configuración', 'match' => 'admin.system*'],
    ],
    'guardia' => [
        ['route' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Inicio', 'match' => 'dashboard'],
        ['route' => 'camas', 'icon' => 'fas fa-bed', 'label' => 'Camas', 'match' => 'camas*'],
        ['divider' => true, 'label' => 'Módulos'],
        ['route' => 'forms.execution.index', 'icon' => 'fas fa-clipboard-list', 'label' => 'Formularios', 'match' => 'forms*'],
    ],
];

// capitan (y roles heredados super_admin/capitania) → menú completo de administración
$normalizedRole = in_array($role, ['capitan', 'super_admin', 'capitania'], true) ? 'super_admin' : $role;
$items = $menuItems[$normalizedRole] ?? $menuItems['guardia'];
$expanded = $mobile ? true : null;
@endphp

<aside id="sidebar" 
       class="{{ $mobile ? 'h-full w-72 max-w-[85vw]' : 'fixed inset-y-0 left-0 z-40' }} flex flex-col bg-[#0f172a] border-r border-[#1e293b] shadow-xl transition-all duration-300"
       :class="{{ $mobile ? "'w-72'" : "sidebarCollapsed ? 'w-[72px]' : 'w-64'" }}">
    
    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-[#1e293b] justify-between">
        <a href="{{ tenancy()->initialized ? route('dashboard') : route('central.dashboard') }}" class="flex items-center gap-3 group min-w-0">
            @if(branding()->logo)
                <img src="{{ branding()->logo }}" alt="{{ branding()->nombre_empresa }}" class="h-9 w-auto">
            @else
                <div class="w-9 h-9 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/20 group-hover:shadow-red-500/40 transition-shadow">
                    <i class="fas fa-helmet-safety text-white text-sm"></i>
                </div>
            @endif
            <div x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-100" class="flex flex-col min-w-0">
                <span class="font-bold text-white text-base leading-tight">{{ branding()->nombre_empresa }}</span>
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Centro de Comando</span>
            </div>
        </a>
        @if($mobile)
            <button type="button"
                    @click="mobileSidebarOpen = false"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 hover:bg-[#1e293b] hover:text-white transition"
                    aria-label="Cerrar menú">
                <i class="fas fa-xmark"></i>
            </button>
        @endif
    </div>
    
    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2">
        <ul class="space-y-0.5">
            @foreach($items as $item)
                @if(isset($item['divider']))
                    <li class="pt-6 pb-3">
                        <span x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" class="px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">
                            {{ $item['label'] }}
                        </span>
                        <div x-show="{{ $mobile ? 'false' : 'sidebarCollapsed' }}" class="h-px bg-[#1e293b] mx-3 my-1"></div>
                    </li>
                @else
                    @if(!isset($item['feature']) || feature($item['feature']))
                    @if(isset($item['children']))
                    <li>
                        <div class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium text-slate-300"
                             :class="{{ $mobile ? "''" : "sidebarCollapsed ? 'justify-center px-0' : ''" }}"
                             title="{{ $item['label'] }}">
                            <span class="w-5 flex items-center justify-center shrink-0">
                                <i class="{{ $item['icon'] }}"></i>
                            </span>
                            <span x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" x-transition class="truncate">{{ $item['label'] }}</span>
                        </div>

                        <ul x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" class="ml-8 mt-1 space-y-1">
                            @foreach($item['children'] as $child)
                            <li>
                                <a href="{{ route($child['route']) }}"
                                   class="block px-3 py-2 rounded-lg text-[12px] font-medium transition-all duration-150
                                          {{ request()->routeIs($child['match'])
                                              ? 'bg-[#2563eb] text-white'
                                              : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                    {{ $child['label'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @else
                    <li>
                        <a href="{{ route($item['route']) }}" 
                           class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                                  {{ request()->routeIs($item['match']) 
                                      ? 'bg-[#2563eb] text-white' 
                                      : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                           :class="{{ $mobile ? "''" : "sidebarCollapsed ? 'justify-center px-0' : ''" }}"
                           title="{{ $item['label'] }}">
                            <span class="w-5 flex items-center justify-center shrink-0">
                                <i class="{{ $item['icon'] }} {{ request()->routeIs($item['match']) ? '' : 'group-hover:scale-110 transition-transform' }}"></i>
                            </span>
                            <span x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" x-transition class="truncate">{{ $item['label'] }}</span>
                            @if(isset($item['badge']) && $item['badge'] === 'live')
                            <span x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" class="ml-auto flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-500 text-white">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Live
                            </span>
                            @endif
                        </a>
                    </li>
                    @endif
                    @endif
                @endif
            @endforeach
        </ul>
    </nav>
    
    @if(in_array($role, ['capitan', 'super_admin', 'capitania'], true))
    <div x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" x-transition class="mx-3 mb-2 px-1">
        <p class="text-[10px] text-slate-500 truncate leading-tight">Plan actual: <span class="text-slate-300 font-medium">{{ tenant_plan_label() }}</span></p>
        <a href="{{ route('tenant.upgrade') }}" class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
            <i class="fas fa-layer-group text-[10px]"></i>
            Planes y módulos
        </a>
    </div>
    @endif

    {{-- Status Card --}}
    <div x-show="{{ $mobile ? 'true' : '!sidebarCollapsed' }}" x-transition class="mx-3 mb-3">
        <div class="p-3 rounded-xl bg-[#1e293b] border border-[#334155]">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 status-pulse"></span>
                <span class="text-xs font-semibold text-white">Cuartel Operativo</span>
            </div>
            <p class="text-[10px] text-slate-400">Sistema funcionando correctamente</p>
        </div>
    </div>
    
    {{-- Collapse Button --}}
    @if(!$mobile)
    <div class="p-2 border-t border-[#1e293b] hidden lg:block">
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:bg-[#1e293b] hover:text-white transition-colors">
            <i class="fas text-[10px]" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
            <span x-show="!sidebarCollapsed" x-transition>Colapsar menú</span>
        </button>
    </div>
    @endif
</aside>
