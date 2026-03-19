@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

<div>
    @if($label)
    <label class="block text-xs font-semibold text-[#5b6b7c] uppercase tracking-wider mb-2">{{ $label }}</label>
    @endif
    
    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="{{ $icon }} text-[#5b6b7c] text-sm"></i>
        </div>
        @endif
        
        <input type="{{ $type }}" {{ $attributes->merge([
            'class' => 'w-full rounded-xl border text-sm transition-all duration-150 ' .
                ($icon ? 'pl-10 pr-4 py-2.5' : 'px-4 py-2.5') . ' ' .
                ($error 
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50' 
                    : 'border-[#bcc8d6] focus:border-[#1f2937] focus:ring-[#1f2937]/10 bg-[#f3f7fb]'
                ) .
                ' text-[#1f2937] placeholder-[#5b6b7c] focus:outline-none focus:ring-4'
        ]) }}>
    </div>
    
    @if($hint && !$error)
    <p class="mt-1.5 text-xs text-[#5b6b7c]">{{ $hint }}</p>
    @endif
    
    @if($error)
    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
        <i class="fas fa-exclamation-circle"></i>
        {{ $error }}
    </p>
    @endif
</div>
