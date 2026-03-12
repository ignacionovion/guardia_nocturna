@props([
    'icon' => 'fas fa-inbox',
    'title' => 'Sin datos',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-6']) }}>
    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
        <i class="{{ $icon }} text-2xl text-slate-400 dark:text-slate-500"></i>
    </div>
    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ $title }}</h3>
    @if($description)
    <p class="text-sm text-slate-500 dark:text-slate-400 text-center max-w-sm">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
    <div class="mt-4">
        {{ $slot }}
    </div>
    @endif
</div>
