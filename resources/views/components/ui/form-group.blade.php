@props([
    'label' => null,
    'for' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'form-group']) }}>
    @if($label)
        <label @if($for) for="{{ $for }}" @endif class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

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
