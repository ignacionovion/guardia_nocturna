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

$baseClasses = 'bg-[#edf3f8] rounded-[14px] border border-[#bcc8d6] shadow-sm';
$shadowClasses = $elevated ? 'shadow-md' : '';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-md hover:bg-[#f3f7fb] cursor-pointer' : '';
@endphp

<div {{ $attributes->merge([
    'class' => trim($baseClasses . ' ' . $shadowClasses . ' ' . $hoverClasses . ' ' . ($header || $footer ? '' : $paddingClasses))
]) }}>
    @if($header)
    <div class="px-5 sm:px-6 py-4 border-b border-[#bcc8d6]">
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
    <div class="px-5 sm:px-6 py-4 border-t border-[#bcc8d6] bg-[#e6edf4] rounded-b-[14px]">
        {{ $footer }}
    </div>
    @endif
</div>
