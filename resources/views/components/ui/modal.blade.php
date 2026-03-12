@props([
    'id',
    'title' => '',
    'maxWidth' => 'md',
    'icon' => null,
    'iconColor' => 'slate',
])

@php
$maxWidths = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
];
$maxWidthClass = $maxWidths[$maxWidth] ?? $maxWidths['md'];
$iconColors = [
    'slate' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
    'red' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
    'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    'amber' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
];
$iconColorClass = $iconColors[$iconColor] ?? $iconColors['slate'];
@endphp

<div id="{{ $id }}" 
     class="hidden fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="{{ $id }}-title" 
     role="dialog" 
     aria-modal="true"
     x-data="{ open: false }"
     x-show="open"
     x-on:open-modal-{{ $id }}.window="open = true"
     x-on:close-modal-{{ $id }}.window="open = false"
     x-on:keydown.escape.window="open = false">
    <div class="flex min-h-screen items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm transition-opacity" 
             @click="open = false; document.getElementById('{{ $id }}').classList.add('hidden')"
             x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>
        
        {{-- Modal Panel --}}
        <div class="relative bg-white dark:bg-slate-900 dark:bg-slate-900 rounded-2xl shadow-2xl {{ $maxWidthClass }} w-full transform transition-all border border-slate-200 dark:border-slate-700 dark:border-slate-800"
             x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            {{-- Header --}}
            @if($title)
            <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800">
                @if($icon)
                <div class="w-10 h-10 rounded-xl {{ $iconColorClass }} flex items-center justify-center shrink-0">
                    <i class="{{ $icon }}"></i>
                </div>
                @endif
                <div class="flex-1">
                    <h3 id="{{ $id }}-title" class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                </div>
                <button type="button" 
                        @click="open = false; document.getElementById('{{ $id }}').classList.add('hidden')"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 transition-colors">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            @endif
            
            {{-- Body --}}
            <div class="px-6 py-5">
                {{ $slot }}
            </div>
            
            {{-- Footer --}}
            @if(isset($footer))
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 dark:bg-slate-800/50 rounded-b-2xl">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>
