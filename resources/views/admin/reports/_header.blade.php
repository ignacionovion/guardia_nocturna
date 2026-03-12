@php
$titles = [
    'admin.reports.index' => ['title' => 'Reportes de Asistencia', 'subtitle' => 'Análisis detallado por guardia, semana y período', 'icon' => 'fa-chart-line', 'color' => 'indigo'],
    'admin.reports.attendance' => ['title' => 'Reportes de Asistencia', 'subtitle' => 'Análisis detallado por guardia, semana y período', 'icon' => 'fa-chart-line', 'color' => 'indigo'],
    'admin.reports.preventivas' => ['title' => 'Preventivas', 'subtitle' => 'Registro de actividades preventivas y participación', 'icon' => 'fa-clipboard-list', 'color' => 'emerald'],
    'admin.reports.replacements' => ['title' => 'Reemplazos', 'subtitle' => 'Historial de reemplazos entre voluntarios', 'icon' => 'fa-right-left', 'color' => 'violet'],
    'admin.reports.refuerzos' => ['title' => 'Refuerzos', 'subtitle' => 'Registro de refuerzos solicitados y realizados', 'icon' => 'fa-user-plus', 'color' => 'sky'],
    'admin.reports.drivers' => ['title' => 'Conductores', 'subtitle' => 'Estadísticas de conductores y vehículos', 'icon' => 'fa-id-card', 'color' => 'amber'],
    'admin.reports.emergencias' => ['title' => 'Emergencias', 'subtitle' => 'Registro de emergencias atendidas', 'icon' => 'fa-truck-medical', 'color' => 'rose'],
];

$currentRoute = request()->route()->getName();
$config = $titles[$currentRoute] ?? $titles['admin.reports.index'];

$gradients = [
    'indigo' => 'from-indigo-500 to-indigo-700',
    'emerald' => 'from-emerald-500 to-emerald-700',
    'violet' => 'from-violet-500 to-violet-700',
    'sky' => 'from-sky-500 to-sky-700',
    'amber' => 'from-amber-500 to-amber-700',
    'rose' => 'from-rose-500 to-rose-700',
];

$shadows = [
    'indigo' => 'shadow-indigo-500/25',
    'emerald' => 'shadow-emerald-500/25',
    'violet' => 'shadow-violet-500/25',
    'sky' => 'shadow-sky-500/25',
    'amber' => 'shadow-amber-500/25',
    'rose' => 'shadow-rose-500/25',
];
@endphp

<div class="flex items-center gap-4 mb-6">
    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $gradients[$config['color']] }} flex items-center justify-center shadow-lg {{ $shadows[$config['color']] }}">
        <i class="fas {{ $config['icon'] }} text-white text-lg"></i>
    </div>
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $config['title'] }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $config['subtitle'] }}</p>
    </div>
</div>
