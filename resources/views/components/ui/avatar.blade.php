@props([
    'src' => null,
    'name' => '',
    'size' => 'md',
    'status' => null,
])

@php
$sizes = [
    'xs' => 'w-6 h-6 text-xs',
    'sm' => 'w-8 h-8 text-sm',
    'md' => 'w-10 h-10 text-base',
    'lg' => 'w-12 h-12 text-lg',
    'xl' => 'w-16 h-16 text-xl',
];

$statusColors = [
    'online' => 'bg-emerald-500',
    'offline' => 'bg-slate-400',
    'busy' => 'bg-red-500',
    'away' => 'bg-amber-500',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
$initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

<div class="relative inline-flex">
    @if($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => 'rounded-full object-cover ' . $sizeClass]) }}>
    @else
    <div {{ $attributes->merge(['class' => 'rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center font-semibold text-slate-600 dark:text-slate-300 ' . $sizeClass]) }}>
        {{ $initials ?: '?' }}
    </div>
    @endif
    
    @if($status)
    <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-slate-800 {{ $statusColors[$status] ?? $statusColors['offline'] }}"></span>
    @endif
</div>
