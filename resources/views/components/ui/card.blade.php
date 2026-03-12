@props([
    'padding' => 'p-6',
    'hover' => false,
    'gradient' => false,
])

@php
$baseClasses = 'rounded-2xl border transition-all duration-200';
$bgClasses = $gradient 
    ? 'bg-gradient-to-br from-white to-slate-50 dark:from-slate-900 dark:to-slate-800' 
    : 'bg-white dark:bg-slate-900';
$borderClasses = 'border-slate-200 dark:border-slate-800';
$hoverClasses = $hover ? 'hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 hover:border-slate-300 dark:hover:border-slate-700' : '';
@endphp

<div {{ $attributes->merge([
    'class' => $baseClasses . ' ' . $bgClasses . ' ' . $borderClasses . ' ' . $padding . ' ' . $hoverClasses
]) }}>
    {{ $slot }}
</div>
