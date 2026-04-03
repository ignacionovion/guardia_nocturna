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
    'slate' => ['bg' => 'bg-white', 'icon' => 'text-slate-600'],
    'blue' => ['bg' => 'bg-blue-100', 'icon' => 'text-blue-600'],
    'emerald' => ['bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600'],
    'amber' => ['bg' => 'bg-amber-100', 'icon' => 'text-amber-600'],
    'red' => ['bg' => 'bg-red-100', 'icon' => 'text-red-600'],
    'purple' => ['bg' => 'bg-violet-100', 'icon' => 'text-violet-600'],
    'violet' => ['bg' => 'bg-violet-100', 'icon' => 'text-violet-600'],
    'cyan' => ['bg' => 'bg-cyan-100', 'icon' => 'text-cyan-600'],
    'indigo' => ['bg' => 'bg-indigo-100', 'icon' => 'text-indigo-600'],
    'rose' => ['bg' => 'bg-rose-100', 'icon' => 'text-rose-600'],
];
$c = $colors[$color] ?? $colors['slate'];

$valueSize = $size === 'lg' ? 'text-2xl' : 'text-xl';
$iconSize = $size === 'lg' ? 'w-12 h-12' : 'w-11 h-11';
@endphp

<div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $title }}</p>
            <p class="mt-1 {{ $valueSize }} font-black text-slate-900 truncate">{{ $value }}</p>
            @if($trend)
            <p class="mt-2 text-xs flex items-center gap-1.5">
                <span class="{{ $trendUp ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                    <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }} text-[10px] mr-0.5"></i>
                    {{ $trend }}
                </span>
                <span class="text-slate-400">{{ $trendLabel }}</span>
            </p>
            @endif
        </div>
        @if($icon)
        <div class="{{ $iconSize }} rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
            <i class="{{ $icon }} {{ $c['icon'] }}"></i>
        </div>
        @endif
    </div>
    {{ $slot }}
</div>
