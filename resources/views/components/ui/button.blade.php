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
    'primary' => 'bg-[#0f172a] text-white hover:bg-[#1e293b] shadow-sm',
    'secondary' => 'bg-[#e7eef5] text-[#1e293b] hover:bg-[#dde6ef] border border-[#9fb0c3]',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm',
    'warning' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm',
    'ghost' => 'bg-transparent text-[#475569] hover:bg-[#c3cfdb] hover:text-[#1e293b]',
    'outline' => 'bg-transparent border border-[#9fb0c3] text-[#1e293b] hover:bg-[#e7eef5] hover:border-[#7d94ab]',
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
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-[#1e293b]/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
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
    'class' => 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-[#1e293b]/10 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none ' . $variantClass . ' ' . $sizeClass
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
