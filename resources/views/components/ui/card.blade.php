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

$baseClasses = 'bg-[#dde6ef] rounded-[14px] border border-[#9fb0c3] shadow-sm';
$shadowClasses = $elevated ? 'shadow-md' : '';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-md hover:bg-[#e7eef5] cursor-pointer' : '';
@endphp

<div {{ $attributes->merge([
    'class' => trim($baseClasses . ' ' . $shadowClasses . ' ' . $hoverClasses . ' ' . ($header || $footer ? '' : $paddingClasses))
]) }}>
    @if($header)
    <div class="px-5 sm:px-6 py-4 border-b border-[#9fb0c3]">
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
    <div class="px-5 sm:px-6 py-4 border-t border-[#9fb0c3] bg-[#d9e2ec] rounded-b-[14px]">
        {{ $footer }}
    </div>
    @endif
</div>
