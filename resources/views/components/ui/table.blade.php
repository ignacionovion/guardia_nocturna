@props([
    'striped' => false,
    'hoverable' => true,
    'compact' => false,
])

@php
$rowClasses = $hoverable ? 'hover:bg-[#f9fbfd] transition-colors' : '';
$cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div class="overflow-x-auto rounded-2xl border border-[#e5e7eb] bg-white">
    <table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
        @if(isset($head))
        <thead class="bg-[#f9fbfd]">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-[#e5e7eb] bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
