@props([
    'variant' => 'info',
    'type' => null,
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
// Support both 'type' and 'variant' props
$variant = $type ?? $variant;

$variants = [
    'info' => [
        'wrapper' => 'bg-blue-50 border-blue-200',
        'icon' => 'text-blue-600',
        'title' => 'text-blue-900',
        'text' => 'text-blue-800',
        'icon_default' => 'fas fa-circle-info',
    ],
    'success' => [
        'wrapper' => 'bg-emerald-50 border-emerald-200',
        'icon' => 'text-emerald-600',
        'title' => 'text-emerald-900',
        'text' => 'text-emerald-800',
        'icon_default' => 'fas fa-circle-check',
    ],
    'warning' => [
        'wrapper' => 'bg-amber-50 border-amber-200',
        'icon' => 'text-amber-600',
        'title' => 'text-amber-900',
        'text' => 'text-amber-800',
        'icon_default' => 'fas fa-triangle-exclamation',
    ],
    'danger' => [
        'wrapper' => 'bg-red-50 border-red-200',
        'icon' => 'text-red-600',
        'title' => 'text-red-900',
        'text' => 'text-red-800',
        'icon_default' => 'fas fa-circle-xmark',
    ],
    'default' => [
        'wrapper' => 'bg-white border-slate-200',
        'icon' => 'text-slate-600',
        'title' => 'text-slate-900',
        'text' => 'text-slate-700',
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
