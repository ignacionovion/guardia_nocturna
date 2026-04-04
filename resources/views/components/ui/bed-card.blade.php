@props([
    'bed',
    'occupant' => null,
    'number' => null,
])

@php
$bedNumber = $number ?? $bed->numero ?? $bed->id ?? '?';
$isOccupied = $bed->ocupada ?? ($occupant !== null);
$isAvailable = !$isOccupied && ($bed->estado ?? 'libre') === 'libre';
$isMaintenance = ($bed->estado ?? '') === 'mantenimiento';

$statusClasses = [
    'occupied' => 'border-blue-200 bg-blue-50 hover:border-blue-300',
    'available' => 'border-emerald-200 bg-emerald-50 hover:border-emerald-300 cursor-pointer',
    'maintenance' => 'border-slate-200 bg-white opacity-60 cursor-not-allowed',
];

$status = $isMaintenance ? 'maintenance' : ($isOccupied ? 'occupied' : 'available');
@endphp

<div {{ $attributes->merge([
    'class' => 'relative group rounded-2xl border-2 p-4 transition-all duration-200 hover:shadow-lg ' . $statusClasses[$status]
]) }}>
    {{-- Bed number badge --}}
    <div class="absolute -top-2 -left-2 w-7 h-7 rounded-lg {{ $isOccupied ? 'bg-blue-500' : ($isMaintenance ? 'bg-slate-300' : 'bg-emerald-500') }} flex items-center justify-center text-white text-xs font-bold shadow-lg">
        {{ $bedNumber }}
    </div>
    
    {{-- Bed icon --}}
    <div class="flex justify-center mb-3 pt-2">
        <i class="fas fa-bed text-3xl {{ $isOccupied ? 'text-blue-500 dark:text-blue-400' : ($isMaintenance ? 'text-slate-400 dark:text-slate-500 dark:text-slate-400' : 'text-emerald-500 dark:text-emerald-400') }}"></i>
    </div>
    
    {{-- Occupant info --}}
    @if($isOccupied && $occupant)
    <div class="text-center">
        <div class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 text-sm font-bold">
            {{ strtoupper(substr($occupant->nombre ?? $occupant->name ?? 'O', 0, 1)) }}{{ strtoupper(substr(explode(' ', $occupant->nombre ?? $occupant->name ?? '')[1] ?? '', 0, 1)) }}
        </div>
        <p class="font-medium text-slate-900 dark:text-white text-sm truncate">
            {{ $occupant->nombre ?? $occupant->name ?? 'Ocupante' }}
        </p>
        @if(isset($occupant->numero))
        <p class="text-xs text-slate-500 dark:text-slate-400">#{{ $occupant->numero }}</p>
        @endif
    </div>
    @elseif($isMaintenance)
    <div class="text-center">
        <div class="w-10 h-10 mx-auto mb-2 rounded-lg bg-white flex items-center justify-center">
            <i class="fas fa-wrench text-slate-400"></i>
        </div>
        <p class="text-sm text-slate-500 font-medium">Mantenimiento</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400">No disponible</p>
    </div>
    @else
    <div class="text-center">
        <div class="w-10 h-10 mx-auto mb-2 rounded-lg border-2 border-dashed border-emerald-300 dark:border-emerald-600 flex items-center justify-center">
            <i class="fas fa-plus text-emerald-400 dark:text-emerald-500"></i>
        </div>
        <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Disponible</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400">Click para asignar</p>
    </div>
    @endif
    
    {{-- Hover overlay for occupied beds --}}
    @if($isOccupied)
    <div class="absolute inset-0 rounded-2xl bg-red-500/90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
        <div class="text-center text-white">
            <i class="fas fa-times-circle text-2xl mb-1"></i>
            <p class="text-xs font-medium">Liberar cama</p>
        </div>
    </div>
    @endif
    
    {{-- Slot for additional content --}}
    {{ $slot }}
</div>
