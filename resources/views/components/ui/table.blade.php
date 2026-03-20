@props([
    'striped' => false,
    'hoverable' => true,
    'compact' => false,
])

@php
$rowClasses = $hoverable ? 'hover:bg-[#c3cfdb] transition-colors' : '';
$cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div class="overflow-x-auto rounded-2xl border border-[#9fb0c3] bg-[#dde6ef]">
    <table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
        @if(isset($head))
        <thead class="bg-[#c3cfdb]">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-[#9fb0c3] bg-[#e7eef5]">
            {{ $slot }}
        </tbody>
    </table>
</div>
