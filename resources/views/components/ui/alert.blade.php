@props([
    'variant' => 'info',
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
$variants = [
    'info' => [
        'wrapper' => 'bg-blue-50 dark:bg-slate-800/50 border-blue-200 dark:border-blue-800/30',
        'icon' => 'text-blue-600 dark:text-blue-400',
        'title' => 'text-blue-900 dark:text-blue-200',
        'text' => 'text-blue-800 dark:text-blue-300',
        'icon_default' => 'fas fa-circle-info',
    ],
    'success' => [
        'wrapper' => 'bg-emerald-50 dark:bg-slate-800/50 border-emerald-200 dark:border-emerald-800/30',
        'icon' => 'text-emerald-600 dark:text-emerald-400',
        'title' => 'text-emerald-900 dark:text-emerald-200',
        'text' => 'text-emerald-800 dark:text-emerald-300',
        'icon_default' => 'fas fa-circle-check',
    ],
    'warning' => [
        'wrapper' => 'bg-amber-50 dark:bg-slate-800/50 border-amber-200 dark:border-amber-800/30',
        'icon' => 'text-amber-600 dark:text-amber-400',
        'title' => 'text-amber-900 dark:text-amber-200',
        'text' => 'text-amber-800 dark:text-amber-300',
        'icon_default' => 'fas fa-triangle-exclamation',
    ],
    'danger' => [
        'wrapper' => 'bg-red-50 dark:bg-slate-800/50 border-red-200 dark:border-red-800/30',
        'icon' => 'text-red-600 dark:text-red-400',
        'title' => 'text-red-900 dark:text-red-200',
        'text' => 'text-red-800 dark:text-red-300',
        'icon_default' => 'fas fa-circle-xmark',
    ],
    'default' => [
        'wrapper' => 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700',
        'icon' => 'text-slate-600 dark:text-slate-400',
        'title' => 'text-slate-900 dark:text-slate-200',
        'text' => 'text-slate-700 dark:text-slate-400',
        'icon_default' => 'fas fa-circle-info',
    ],
];

$theme = $variants[$variant] ?? $variants['default'];
$iconClass = $icon ?? $theme['icon_default'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border p-4 ' . $theme['wrapper']]) }}>
    <div class="flex gap-3">
        <div class="shrink-0 mt-0.5">
            <i class="{{ $iconClass }} {{ $theme['icon'] }} text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            @if($title)
                <h3 class="font-semibold text-sm mb-1 {{ $theme['title'] }}">
                    {{ $title }}
                </h3>
            @endif
            <div class="text-sm {{ $theme['text'] }}">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button type="button" class="shrink-0 -mr-1 -mt-1 p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors" onclick="this.closest('.alert').remove()">
                <i class="fas fa-xmark text-slate-400"></i>
            </button>
        @endif
    </div>
</div>
