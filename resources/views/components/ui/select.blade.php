@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

<div class="form-group">
    @if($label)
    <label class="form-label">{{ $label }}</label>
    @endif

    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="{{ $icon }} text-[#475569] text-sm"></i>
        </div>
        @endif

        <select {{ $attributes->merge([
            'class' => 'select-base ' .
                ($icon ? 'pl-10' : '') . ' ' .
                ($error ? 'input-error' : '')
        ]) }}>
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
            <i class="fas fa-chevron-down text-[#475569] text-xs"></i>
        </div>
    </div>

    @if($hint && !$error)
    <p class="form-hint">{{ $hint }}</p>
    @endif

    @if($error)
    <p class="form-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ $error }}
    </p>
    @endif
</div>
