@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'trendLabel' => 'vs mes anterior',
    'color' => 'slate',
])

@php
$colors = [
    'slate' => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'icon' => 'text-slate-600 dark:text-slate-400'],
    'blue' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'icon' => 'text-blue-600 dark:text-blue-400'],
    'emerald' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'icon' => 'text-emerald-600 dark:text-emerald-400'],
    'amber' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'icon' => 'text-amber-600 dark:text-amber-400'],
    'red' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'icon' => 'text-red-600 dark:text-red-400'],
    'purple' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
    'cyan' => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'icon' => 'text-cyan-600 dark:text-cyan-400'],
];
$c = $colors[$color] ?? $colors['slate'];
@endphp

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-all">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $title }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $value }}</p>
            @if($trend)
            <p class="mt-1.5 text-xs flex items-center gap-1">
                <span class="{{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                    <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }} text-[10px]"></i>
                    {{ $trend }}
                </span>
                <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400">{{ $trendLabel }}</span>
            </p>
            @endif
        </div>
        @if($icon)
        <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} flex items-center justify-center">
            <i class="{{ $icon }} text-lg {{ $c['icon'] }}"></i>
        </div>
        @endif
    </div>
    {{ $slot }}
</div>
