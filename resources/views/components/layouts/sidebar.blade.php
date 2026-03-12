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
        ['route' => 'admin.planillas.index', 'icon' => 'fas fa-table-columns', 'label' => 'Planillas', 'match' => 'admin.planillas*', 'feature' => 'planilla'],
        ['route' => 'inventario.index', 'icon' => 'fas fa-warehouse', 'label' => 'Inventario', 'match' => 'inventario*', 'feature' => 'inventario'],
        ['route' => 'admin.reports.index', 'icon' => 'fas fa-chart-line', 'label' => 'Reportes', 'match' => 'admin.reports*', 'feature' => 'reportes'],
        ['divider' => true, 'label' => 'Sistema'],
        ['route' => 'admin.users.index', 'icon' => 'fas fa-user-shield', 'label' => 'Usuarios', 'match' => 'admin.users*'],
        ['route' => 'admin.system.index', 'icon' => 'fas fa-sliders', 'label' => 'Configuración', 'match' => 'admin.system*'],
    ],
    'capitania' => [
        ['route' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard', 'match' => 'dashboard'],
        ['divider' => true, 'label' => 'Centro de Comando'],
        ['route' => 'admin.guardias', 'icon' => 'fas fa-shield-halved', 'label' => 'Guardias', 'match' => 'admin.guardias*', 'feature' => 'guardia'],
        ['route' => 'camas', 'icon' => 'fas fa-bed', 'label' => 'Camas', 'match' => 'camas*', 'feature' => 'camas'],
        ['route' => 'admin.calendario', 'icon' => 'fas fa-calendar-days', 'label' => 'Calendario', 'match' => 'admin.calendario*', 'feature' => 'calendario'],
        ['divider' => true, 'label' => 'Personal'],
        ['route' => 'admin.volunteers.index', 'icon' => 'fas fa-user-group', 'label' => 'Voluntarios', 'match' => 'admin.volunteers*', 'feature' => 'voluntarios'],
        ['route' => 'admin.emergencies.index', 'icon' => 'fas fa-truck-medical', 'label' => 'Emergencias', 'match' => 'admin.emergencies*', 'feature' => 'emergencias'],
        ['route' => 'admin.users.index', 'icon' => 'fas fa-user-shield', 'label' => 'Usuarios', 'match' => 'admin.users*'],
        ['route' => 'admin.reports.index', 'icon' => 'fas fa-chart-line', 'label' => 'Reportes', 'match' => 'admin.reports*', 'feature' => 'reportes'],
    ],
    'guardia' => [
        ['route' => 'dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Inicio', 'match' => 'dashboard'],
        ['route' => 'camas', 'icon' => 'fas fa-bed', 'label' => 'Camas', 'match' => 'camas*'],
    ],
    'inventario' => [
        ['route' => 'inventario.index', 'icon' => 'fas fa-warehouse', 'label' => 'Inventario', 'match' => 'inventario*'],
        ['route' => 'admin.volunteers.index', 'icon' => 'fas fa-user-group', 'label' => 'Voluntarios', 'match' => 'admin.volunteers*'],
        ['route' => 'admin.emergencies.index', 'icon' => 'fas fa-truck-medical', 'label' => 'Emergencias', 'match' => 'admin.emergencies*'],
    ],
    'ayudante' => [
        ['route' => 'admin.preventivas.index', 'icon' => 'fas fa-clipboard-check', 'label' => 'Preventivas', 'match' => 'admin.preventivas*'],
    ],
];

$items = $menuItems[$role] ?? $menuItems['guardia'];
@endphp

<aside id="sidebar" 
       class="fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 shadow-sm"
       :class="sidebarCollapsed ? 'w-[72px]' : 'w-64'">
    
    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            @if(branding()->logo)
                <img src="{{ branding()->logo }}" alt="{{ branding()->nombre_empresa }}" class="h-9 w-auto">
            @else
                <div class="w-9 h-9 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/20 group-hover:shadow-red-500/40 transition-shadow">
                    <i class="fas fa-helmet-safety text-white text-sm"></i>
                </div>
            @endif
            <div x-show="!sidebarCollapsed" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-100" class="flex flex-col">
                <span class="font-bold text-slate-900 dark:text-white text-base leading-tight">{{ branding()->nombre_empresa }}</span>
                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wider">Centro de Comando</span>
            </div>
        </a>
    </div>
    
    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2">
        <ul class="space-y-0.5">
            @foreach($items as $item)
                @if(isset($item['divider']))
                    <li class="pt-5 pb-2">
                        <span x-show="!sidebarCollapsed" class="px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                            {{ $item['label'] }}
                        </span>
                        <div x-show="sidebarCollapsed" class="h-px bg-slate-200 dark:bg-slate-800 mx-3 my-1"></div>
                    </li>
                @else
                    @if(!isset($item['feature']) || feature($item['feature']))
                    <li>
                        <a href="{{ route($item['route']) }}" 
                           class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-150
                                  {{ request()->routeIs($item['match']) 
                                      ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-lg shadow-slate-900/10 dark:shadow-white/10' 
                                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="{{ $item['label'] }}">
                            <span class="w-5 flex items-center justify-center shrink-0">
                                <i class="{{ $item['icon'] }} {{ request()->routeIs($item['match']) ? '' : 'group-hover:scale-110 transition-transform' }}"></i>
                            </span>
                            <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $item['label'] }}</span>
                            @if(isset($item['badge']) && $item['badge'] === 'live')
                            <span x-show="!sidebarCollapsed" class="ml-auto flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-500 text-white">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Live
                            </span>
                            @endif
                        </a>
                    </li>
                    @endif
                @endif
            @endforeach
        </ul>
    </nav>
    
    {{-- Status Card --}}
    <div x-show="!sidebarCollapsed" x-transition class="mx-3 mb-3">
        <div class="p-3 rounded-xl bg-gradient-to-br from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-800/50 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 status-pulse"></span>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Cuartel Operativo</span>
            </div>
            <p class="text-[10px] text-slate-500 dark:text-slate-400">Sistema funcionando correctamente</p>
        </div>
    </div>
    
    {{-- Collapse Button --}}
    <div class="p-2 border-t border-slate-200 dark:border-slate-800">
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <i class="fas text-[10px]" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
            <span x-show="!sidebarCollapsed" x-transition>Colapsar menú</span>
        </button>
    </div>
</aside>
