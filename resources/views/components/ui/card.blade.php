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

$baseClasses = 'bg-[#f1f5f9] rounded-[14px] border border-[#cbd5e1] shadow-sm';
$shadowClasses = $elevated ? 'shadow-md' : '';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-md hover:border-slate-400 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge([
    'class' => trim($baseClasses . ' ' . $shadowClasses . ' ' . $hoverClasses . ' ' . ($header || $footer ? '' : $paddingClasses))
]) }}>
    @if($header)
    <div class="px-5 sm:px-6 py-4 border-b border-slate-300">
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
    <div class="px-5 sm:px-6 py-4 border-t border-slate-300 bg-slate-100/50 rounded-b-[14px]">
        {{ $footer }}
    </div>
    @endif
</div>
