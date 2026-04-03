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

$baseClasses = 'bg-white rounded-2xl border border-[#e5e7eb] shadow-[0_2px_8px_rgba(0,0,0,0.04)]';
$shadowClasses = $elevated ? 'shadow-[0_4px_16px_rgba(0,0,0,0.06)]' : '';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] hover:border-[#2563eb]/30 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge([
    'class' => trim($baseClasses . ' ' . $shadowClasses . ' ' . $hoverClasses . ' ' . ($header || $footer ? '' : $paddingClasses))
]) }}>
    @if($header)
    <div class="px-5 sm:px-6 py-4 border-b border-[#e5e7eb]">
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
    <div class="px-5 sm:px-6 py-4 border-t border-[#e5e7eb] bg-[#f9fbfd] rounded-b-2xl">
        {{ $footer }}
    </div>
    @endif
</div>
