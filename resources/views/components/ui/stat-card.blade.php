@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'trendLabel' => 'vs mes anterior',
    'color' => 'slate',
    'size' => 'default',
])

@php
$colors = [
    'slate' => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'icon' => 'text-slate-600 dark:text-slate-400'],
    'blue' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'icon' => 'text-blue-600 dark:text-blue-400'],
    'emerald' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'icon' => 'text-emerald-600 dark:text-emerald-400'],
    'amber' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'icon' => 'text-amber-600 dark:text-amber-400'],
    'red' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'icon' => 'text-red-600 dark:text-red-400'],
    'purple' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'icon' => 'text-violet-600 dark:text-violet-400'],
    'violet' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'icon' => 'text-violet-600 dark:text-violet-400'],
    'cyan' => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'icon' => 'text-cyan-600 dark:text-cyan-400'],
    'indigo' => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
    'rose' => ['bg' => 'bg-rose-100 dark:bg-rose-900/30', 'icon' => 'text-rose-600 dark:text-rose-400'],
];
$c = $colors[$color] ?? $colors['slate'];

$valueSize = $size === 'lg' ? 'text-3xl sm:text-4xl' : 'text-2xl sm:text-3xl';
$iconSize = $size === 'lg' ? 'w-14 h-14' : 'w-12 h-12';
@endphp

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $title }}</p>
            <p class="mt-2 {{ $valueSize }} font-bold text-slate-900 dark:text-white truncate">{{ $value }}</p>
            @if($trend)
            <p class="mt-2 text-xs flex items-center gap-1.5">
                <span class="{{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-semibold">
                    <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }} text-[10px] mr-0.5"></i>
                    {{ $trend }}
                </span>
                <span class="text-slate-400 dark:text-slate-500">{{ $trendLabel }}</span>
            </p>
            @endif
        </div>
        @if($icon)
        <div class="{{ $iconSize }} rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0 ml-4">
            <i class="{{ $icon }} text-lg {{ $c['icon'] }}"></i>
        </div>
        @endif
    </div>
    {{ $slot }}
</div>
