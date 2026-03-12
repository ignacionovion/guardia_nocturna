@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'icon' => null,
])

@php
$variants = [
    'default' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
    'primary' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'success' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'info' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'purple' => 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400',
    'violet' => 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400',
    'indigo' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'rose' => 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
    'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
];

$dotColors = [
    'default' => 'bg-slate-500',
    'primary' => 'bg-blue-500',
    'success' => 'bg-emerald-500',
    'warning' => 'bg-amber-500',
    'danger' => 'bg-red-500',
    'info' => 'bg-blue-500',
    'purple' => 'bg-violet-500',
    'violet' => 'bg-violet-500',
    'indigo' => 'bg-indigo-500',
    'rose' => 'bg-rose-500',
    'cyan' => 'bg-cyan-500',
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
