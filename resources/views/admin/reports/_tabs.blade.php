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

<div class="flex flex-wrap gap-2 mb-6">
    @foreach($tabs as $tab)
        @php
            $isActive = in_array($currentRoute, $tab['match']);
        @endphp
        <a href="{{ route($tab['route']) }}" 
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150
                  {{ $isActive 
                      ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-lg' 
                      : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}">
            <i class="fas {{ $tab['icon'] }} text-xs {{ $isActive ? '' : 'text-slate-400' }}"></i>
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>
