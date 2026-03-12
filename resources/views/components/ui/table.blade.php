@props([
    'striped' => false,
    'hoverable' => true,
    'compact' => false,
])

<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
    <table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
        @if(isset($head))
        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
            {{ $head }}
        </thead>
        @endif
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
            {{ $slot }}
        </tbody>
    </table>
</div>
