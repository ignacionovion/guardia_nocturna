@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'loading' => false,
    'href' => null,
])

@php
$variants = [
    'primary' => 'bg-slate-900 dark:bg-white dark:bg-slate-900 text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 dark:hover:bg-slate-800 shadow-lg shadow-slate-900/10 dark:shadow-white/10',
    'secondary' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-600/20',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-600/20',
    'warning' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-lg shadow-amber-500/20',
    'ghost' => 'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white',
    'outline' => 'bg-transparent border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 dark:hover:bg-slate-800 hover:border-slate-300 dark:border-slate-600 dark:hover:border-slate-600',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-2 text-xs',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-5 py-3 text-sm',
    'xl' => 'px-6 py-3.5 text-base',
];

$variantClass = $variants[$variant] ?? $variants['primary'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge([
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-4 focus:ring-slate-900/10 dark:focus:ring-white/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
]) }}>
    @if($loading)
    <i class="fas fa-spinner fa-spin"></i>
    @elseif($icon)
    <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    @if($iconRight && !$loading)
    <i class="{{ $iconRight }}"></i>
    @endif
</a>
@else
<button type="{{ $type }}" {{ $attributes->merge([
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-4 focus:ring-slate-900/10 dark:focus:ring-white/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
]) }}>
    @if($loading)
    <i class="fas fa-spinner fa-spin"></i>
    @elseif($icon)
    <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    @if($iconRight && !$loading)
    <i class="{{ $iconRight }}"></i>
    @endif
</button>
@endif
