@props([
    'striped' => false,
    'hoverable' => true,
    'compact' => false,
])

@php
$rowClasses = $hoverable ? 'hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors' : '';
$cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
    <table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
        @if(isset($head))
        <thead class="bg-slate-50 dark:bg-slate-800/50">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
            {{ $slot }}
        </tbody>
    </table>
</div>
