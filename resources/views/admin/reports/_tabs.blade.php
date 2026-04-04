@php
$currentRoute = request()->route()->getName();
$tabs = [
    ['route' => 'admin.reports.index', 'icon' => 'fa-chart-line', 'label' => 'Asistencia', 'match' => ['admin.reports.index', 'admin.reports.attendance']],
    ['route' => 'admin.reports.preventivas', 'icon' => 'fa-clipboard-list', 'label' => 'Preventivas', 'match' => ['admin.reports.preventivas']],
    ['route' => 'admin.reports.replacements', 'icon' => 'fa-right-left', 'label' => 'Reemplazos', 'match' => ['admin.reports.replacements']],
    ['route' => 'admin.reports.refuerzos', 'icon' => 'fa-user-plus', 'label' => 'Refuerzos', 'match' => ['admin.reports.refuerzos']],
    ['route' => 'admin.reports.drivers', 'icon' => 'fa-id-card', 'label' => 'Conductores', 'match' => ['admin.reports.drivers']],
    ['route' => 'admin.reports.emergencias', 'icon' => 'fa-truck-medical', 'label' => 'Emergencias', 'match' => ['admin.reports.emergencias']],
];
@endphp

<div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-800 rounded-xl w-fit">
    @foreach($tabs as $tab)
        @php
            $isActive = in_array($currentRoute, $tab['match']);
        @endphp
        <a href="{{ route($tab['route']) }}" 
           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150
                  {{ $isActive 
                      ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' 
                      : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <i class="fas {{ $tab['icon'] }} text-xs {{ $isActive ? 'text-indigo-600 dark:text-indigo-400' : '' }}"></i>
            <span class="hidden sm:inline">{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>
