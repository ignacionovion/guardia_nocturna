@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'iconVariant' => 'red',
    'badge' => null,
    'badgeVariant' => 'default',
])

@php
$iconVariants = [
    'red' => 'icon-box-gradient-red',
    'blue' => 'icon-box-blue',
    'emerald' => 'icon-box-emerald',
    'amber' => 'icon-box-amber',
    'violet' => 'icon-box-violet',
    'slate' => 'icon-box-slate',
    'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400',
];
$iconClass = $iconVariants[$iconVariant] ?? $iconVariants['red'];
@endphp

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pb-6 border-b border-slate-200 dark:border-slate-800">
    <div class="flex items-center gap-4">
        @if($icon)
            <div class="icon-box icon-box-lg {{ $iconClass }}">
                <i class="{{ $icon }}"></i>
            </div>
        @endif
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                {{ $title }}
                @if($badge)
                    <x-ui.badge :variant="$badgeVariant" size="sm">{{ $badge }}</x-ui.badge>
                @endif
            </h1>
            @if($subtitle)
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            {{ $slot }}
        </div>
    @endif
</div>
