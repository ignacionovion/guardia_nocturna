@props([
    'striped' => false,
    'hoverable' => true,
    'compact' => false,
])

@php
$rowClasses = $hoverable ? 'hover:bg-white transition-colors' : '';
$cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
        @if(isset($head))
        <thead class="bg-white">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
