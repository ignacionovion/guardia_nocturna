@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'icon' => null,
])

@php
$variants = [
    'default' => 'bg-white text-slate-700',
    'primary' => 'bg-blue-100 text-blue-700',
    'success' => 'bg-emerald-100 text-emerald-700',
    'warning' => 'bg-amber-100 text-amber-700',
    'danger' => 'bg-red-100 text-red-700',
    'info' => 'bg-blue-100 text-blue-700',
    'purple' => 'bg-violet-100 text-violet-700',
    'violet' => 'bg-violet-100 text-violet-700',
    'indigo' => 'bg-indigo-100 text-indigo-700',
    'rose' => 'bg-rose-100 text-rose-700',
    'cyan' => 'bg-cyan-100 text-cyan-700',
];

$dotColors = [
    'default' => 'bg-slate-600',
    'primary' => 'bg-blue-600',
    'success' => 'bg-emerald-600',
    'warning' => 'bg-amber-600',
    'danger' => 'bg-red-600',
    'info' => 'bg-blue-600',
    'purple' => 'bg-violet-600',
    'violet' => 'bg-violet-600',
    'indigo' => 'bg-indigo-600',
    'rose' => 'bg-rose-600',
    'cyan' => 'bg-cyan-600',
];

$sizes = [
    'xs' => 'px-1.5 py-0.5 text-[10px]',
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1.5 text-sm',
];

$variantClass = $variants[$variant] ?? $variants['default'];
$dotColorClass = $dotColors[$variant] ?? $dotColors['default'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 font-semibold rounded-lg ' . $variantClass . ' ' . $sizeClass]) }}>
    @if($dot)
    <span class="w-1.5 h-1.5 rounded-full {{ $dotColorClass }}"></span>
    @endif
    @if($icon)
    <i class="{{ $icon }} text-[0.7em]"></i>
    @endif
    {{ $slot }}
</span>
