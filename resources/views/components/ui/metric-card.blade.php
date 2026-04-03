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
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-[#f9fbfd]',
        'iconColor' => 'text-[#475569]',
        'accent' => 'border-l-[#475569]',
    ],
    'primary' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-blue-100',
        'iconColor' => 'text-blue-600',
        'accent' => 'border-l-blue-500',
    ],
    'success' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-green-100',
        'iconColor' => 'text-green-600',
        'accent' => 'border-l-green-500',
    ],
    'warning' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-amber-100',
        'iconColor' => 'text-amber-600',
        'accent' => 'border-l-amber-500',
    ],
    'danger' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-red-100',
        'iconColor' => 'text-red-600',
        'accent' => 'border-l-red-500',
    ],
    'purple' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-purple-100',
        'iconColor' => 'text-purple-600',
        'accent' => 'border-l-purple-500',
    ],
    'sky' => [
        'bg' => 'bg-white',
        'border' => 'border-[#e5e7eb]',
        'iconBg' => 'bg-sky-100',
        'iconColor' => 'text-sky-600',
        'accent' => 'border-l-sky-500',
    ],
];

$theme = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-xl border p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ' . $theme['bg'] . ' ' . $theme['border'] . ' border-l-4 ' . $theme['accent']]) }}>
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium text-[#475569] uppercase tracking-wider leading-tight">
                {{ $label }}
            </p>
            <p class="mt-1 text-2xl font-bold text-[#0f172a] tracking-tight">
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
