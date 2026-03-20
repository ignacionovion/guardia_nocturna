@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

<div>
    @if($label)
    <label class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-2">{{ $label }}</label>
    @endif

    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="{{ $icon }} text-[#475569] text-sm"></i>
        </div>
        @endif

        <select {{ $attributes->merge([
            'class' => 'w-full rounded-xl border text-sm transition-all duration-150 appearance-none cursor-pointer min-h-[44px] ' .
                ($icon ? 'pl-10 pr-10 py-3' : 'px-4 pr-10 py-3') . ' ' .
                ($error
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50'
                    : 'border-[#9fb0c3] focus:border-[#1e293b] focus:ring-[#1e293b]/10 bg-[#e7eef5]'
                ) .
                ' text-[#1e293b] focus:outline-none focus:ring-2'
        ]) }}>
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
            <i class="fas fa-chevron-down text-[#475569] text-xs"></i>
        </div>
    </div>

    @if($hint && !$error)
    <p class="mt-1.5 text-xs text-[#475569]">{{ $hint }}</p>
    @endif

    @if($error)
    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
        <i class="fas fa-exclamation-circle"></i>
        {{ $error }}
    </p>
    @endif
</div>
