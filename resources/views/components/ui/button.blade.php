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
    'primary' => 'bg-slate-900 text-white hover:bg-slate-800 shadow-sm',
    'secondary' => 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 hover:border-slate-300 shadow-sm',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm',
    'warning' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm',
    'ghost' => 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-700',
    'outline' => 'bg-transparent border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300',
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
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-900/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
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
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-900/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
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
