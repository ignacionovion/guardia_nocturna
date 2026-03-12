@props([
    'volunteer',
    'status' => 'presente',
    'showActions' => true,
    'confirmed' => false,
    'bed' => null,
])

@php
$statusConfig = [
    'constituye' => ['color' => 'emerald', 'label' => 'Constituye', 'icon' => 'fa-check-circle'],
    'presente' => ['color' => 'emerald', 'label' => 'Presente', 'icon' => 'fa-check'],
    'reemplazo' => ['color' => 'amber', 'label' => 'Reemplazo', 'icon' => 'fa-people-arrows'],
    'refuerzo' => ['color' => 'blue', 'label' => 'Refuerzo', 'icon' => 'fa-user-plus'],
    'permiso' => ['color' => 'purple', 'label' => 'Permiso', 'icon' => 'fa-calendar-check'],
    'ausente' => ['color' => 'red', 'label' => 'Ausente', 'icon' => 'fa-user-xmark'],
    'licencia' => ['color' => 'cyan', 'label' => 'Licencia', 'icon' => 'fa-file-medical'],
    'falta' => ['color' => 'red', 'label' => 'Falta', 'icon' => 'fa-circle-xmark'],
    'inhabilitado' => ['color' => 'slate', 'label' => 'Inhabilitado', 'icon' => 'fa-ban'],
];

$config = $statusConfig[$status] ?? $statusConfig['presente'];
$colorClasses = [
    'emerald' => 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/50 dark:bg-emerald-900/10',
    'amber' => 'border-amber-300 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-900/10',
    'blue' => 'border-blue-300 dark:border-blue-700 bg-blue-50/50 dark:bg-blue-900/10',
    'purple' => 'border-purple-300 dark:border-purple-700 bg-purple-50/50 dark:bg-purple-900/10',
    'red' => 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-900/10',
    'cyan' => 'border-cyan-300 dark:border-cyan-700 bg-cyan-50/50 dark:bg-cyan-900/10',
    'slate' => 'border-slate-200 dark:border-slate-700 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 dark:bg-slate-800/30',
];
$badgeColors = [
    'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    'amber' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'red' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400',
    'slate' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
];
@endphp

<div {{ $attributes->merge([
    'class' => 'relative group rounded-2xl border-2 p-4 transition-all duration-200 hover:shadow-lg ' . ($colorClasses[$config['color']] ?? $colorClasses['slate'])
]) }}>
    {{-- Confirmed indicator --}}
    @if($confirmed)
    <div class="absolute top-3 right-3">
        <span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
            <i class="fas fa-check text-white text-xs"></i>
        </span>
    </div>
    @endif

    <div class="flex items-start gap-4">
        {{-- Avatar --}}
        <div class="relative shrink-0">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 font-bold">
                {{ strtoupper(substr($volunteer->nombre ?? $volunteer->name ?? 'B', 0, 1)) }}{{ strtoupper(substr(explode(' ', $volunteer->nombre ?? $volunteer->name ?? '')[1] ?? '', 0, 1)) }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-lg {{ $badgeColors[$config['color']] }} flex items-center justify-center shadow">
                <i class="fas {{ $config['icon'] }} text-[9px]"></i>
            </div>
        </div>
        
        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white truncate">
                        {{ $volunteer->nombre ?? $volunteer->name ?? 'Sin nombre' }}
                    </h4>
                    <div class="flex items-center gap-2 mt-0.5">
                        @if(isset($volunteer->cargo) || isset($volunteer->rango))
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $volunteer->cargo ?? $volunteer->rango ?? '' }}
                        </span>
                        @endif
                        @if(isset($volunteer->numero))
                        <span class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400">#{{ $volunteer->numero }}</span>
                        @endif
                    </div>
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase {{ $badgeColors[$config['color']] }}">
                    {{ $config['label'] }}
                </span>
            </div>
            
            {{-- Bed info --}}
            @if($bed)
            <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[10px] font-medium">
                <i class="fas fa-bed"></i>
                Cama {{ $bed }}
            </div>
            @endif
            
            {{-- Slot for additional content --}}
            {{ $slot }}
        </div>
    </div>
    
    {{-- Actions --}}
    @if($showActions && isset($actions))
    <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700/50 dark:border-slate-700/50 flex items-center gap-2">
        {{ $actions }}
    </div>
    @endif
</div>
