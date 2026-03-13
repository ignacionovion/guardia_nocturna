@props([
    'value',
    'label',
    'icon' => null,
    'variant' => 'default',
    'trend' => null,
    'trendUp' => null,
])

@php
$variants = [
    'default' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-slate-100 dark:bg-slate-700',
        'iconColor' => 'text-slate-600 dark:text-slate-400',
        'accent' => 'border-l-slate-500',
    ],
    'primary' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-blue-50 dark:bg-blue-900/20',
        'iconColor' => 'text-blue-600 dark:text-blue-400',
        'accent' => 'border-l-blue-500',
    ],
    'success' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-emerald-50 dark:bg-emerald-900/20',
        'iconColor' => 'text-emerald-600 dark:text-emerald-400',
        'accent' => 'border-l-emerald-500',
    ],
    'warning' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-amber-50 dark:bg-amber-900/20',
        'iconColor' => 'text-amber-600 dark:text-amber-400',
        'accent' => 'border-l-amber-500',
    ],
    'danger' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-red-50 dark:bg-red-900/20',
        'iconColor' => 'text-red-600 dark:text-red-400',
        'accent' => 'border-l-red-500',
    ],
    'purple' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-purple-50 dark:bg-purple-900/20',
        'iconColor' => 'text-purple-600 dark:text-purple-400',
        'accent' => 'border-l-purple-500',
    ],
    'sky' => [
        'bg' => 'bg-white dark:bg-slate-800',
        'border' => 'border-slate-200 dark:border-slate-700',
        'iconBg' => 'bg-sky-50 dark:bg-sky-900/20',
        'iconColor' => 'text-sky-600 dark:text-sky-400',
        'accent' => 'border-l-sky-500',
    ],
];

$theme = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-xl border p-4 ' . $theme['bg'] . ' ' . $theme['border'] . ' border-l-4 ' . $theme['accent']]) }}>
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider leading-tight">
                {{ $label }}
            </p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $value }}
            </p>
            @if($trend !== null)
                <div class="mt-1 flex items-center gap-1">
                    @if($trendUp)
                        <i class="fas fa-arrow-trend-up text-xs text-emerald-500"></i>
                    @else
                        <i class="fas fa-arrow-trend-down text-xs text-red-500"></i>
                    @endif
                    <span class="text-xs font-medium {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $trend }}
                    </span>
                </div>
            @endif
        </div>
        @if($icon)
            <div class="shrink-0 ml-3 w-10 h-10 rounded-lg {{ $theme['iconBg'] }} flex items-center justify-center {{ $theme['iconColor'] }}">
                <i class="{{ $icon }} text-sm"></i>
            </div>
        @endif
    </div>
</div>
