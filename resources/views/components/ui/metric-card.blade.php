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
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-slate-100',
        'iconColor' => 'text-slate-600',
        'accent' => 'border-l-slate-500',
    ],
    'primary' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-blue-100',
        'iconColor' => 'text-blue-600',
        'accent' => 'border-l-blue-500',
    ],
    'success' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-emerald-100',
        'iconColor' => 'text-emerald-600',
        'accent' => 'border-l-emerald-500',
    ],
    'warning' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-amber-100',
        'iconColor' => 'text-amber-600',
        'accent' => 'border-l-amber-500',
    ],
    'danger' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-red-100',
        'iconColor' => 'text-red-600',
        'accent' => 'border-l-red-500',
    ],
    'purple' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-purple-100',
        'iconColor' => 'text-purple-600',
        'accent' => 'border-l-purple-500',
    ],
    'sky' => [
        'bg' => 'bg-white',
        'border' => 'border-slate-200',
        'iconBg' => 'bg-sky-100',
        'iconColor' => 'text-sky-600',
        'accent' => 'border-l-sky-500',
    ],
];

$theme = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-2xl border p-5 shadow-sm ' . $theme['bg'] . ' ' . $theme['border']]) }}>
    <div class="flex items-center justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                {{ $label }}
            </p>
            <p class="mt-1 text-xl font-black text-slate-900">
                {{ $value }}
            </p>
            @if($trend !== null)
                <div class="mt-1 flex items-center gap-1">
                    @if($trendUp)
                        <i class="fas fa-arrow-trend-up text-xs text-emerald-500"></i>
                    @else
                        <i class="fas fa-arrow-trend-down text-xs text-red-500"></i>
                    @endif
                    <span class="text-xs font-medium {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
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
