@props([
    'padding' => 'default',
    'hover' => false,
    'elevated' => false,
    'header' => null,
    'footer' => null,
])

@php
$paddingClasses = match($padding) {
    'none' => '',
    'sm' => 'p-4',
    'lg' => 'p-6 sm:p-8',
    default => 'p-5 sm:p-6',
};

$baseClasses = 'bg-white rounded-2xl border border-slate-200 shadow-sm';
$shadowClasses = $elevated ? 'shadow-md' : '';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge([
    'class' => trim($baseClasses . ' ' . $shadowClasses . ' ' . $hoverClasses . ' ' . ($header || $footer ? '' : $paddingClasses))
]) }}>
    @if($header)
    <div class="px-6 py-4 border-b border-slate-200">
        {{ $header }}
    </div>
    @endif
    
    @if($header || $footer)
    <div class="{{ $paddingClasses }}">
        {{ $slot }}
    </div>
    @else
        {{ $slot }}
    @endif
    
    @if($footer)
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
        {{ $footer }}
    </div>
    @endif
</div>
