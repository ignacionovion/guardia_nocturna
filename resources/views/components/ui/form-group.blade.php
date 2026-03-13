@props([
    'label' => null,
    'for' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label @if($for) for="{{ $for }}" @endif class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($hint && !$error)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i>
            {{ $error }}
        </p>
    @endif
</div>
