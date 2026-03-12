@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

<div>
    @if($label)
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ $label }}</label>
    @endif
    
    <div class="relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i class="{{ $icon }} text-slate-400 dark:text-slate-500 text-sm"></i>
        </div>
        @endif
        
        <input type="{{ $type }}" {{ $attributes->merge([
            'class' => 'w-full rounded-xl border text-sm transition-all duration-150 ' .
                ($icon ? 'pl-10 pr-4 py-2.5' : 'px-4 py-2.5') . ' ' .
                ($error 
                    ? 'border-red-300 dark:border-red-700 focus:border-red-500 focus:ring-red-500/20 bg-red-50 dark:bg-red-900/10' 
                    : 'border-slate-200 dark:border-slate-700 focus:border-slate-900 dark:focus:border-white focus:ring-slate-900/10 dark:focus:ring-white/10 bg-slate-50 dark:bg-slate-800'
                ) .
                ' text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-4 focus:bg-white dark:focus:bg-slate-900'
        ]) }}>
    </div>
    
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
