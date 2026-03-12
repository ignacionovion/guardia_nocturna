@extends('layouts.modern')

@section('title', 'Panel de Camas - ' . branding()->nombre_empresa)
@section('page-title', 'Panel de Camas')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Panel de Camas</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gestión de asignación de camas del cuartel</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $camasOcupadas ?? 8 }} ocupadas</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ ($camasTotal ?? 15) - ($camasOcupadas ?? 8) }} libres</span>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Ocupación actual</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ round(($camasOcupadas ?? 8) / ($camasTotal ?? 15) * 100) }}%</span>
                </div>
                <div class="h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500" style="width: {{ ($camasOcupadas ?? 8) / ($camasTotal ?? 15) * 100 }}%"></div>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-6 pl-6 border-l border-slate-200 dark:border-slate-700">
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $camasTotal ?? 15 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $camasOcupadas ?? 8 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ocupadas</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ ($camasTotal ?? 15) - ($camasOcupadas ?? 8) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">Libres</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Camas Grid --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-bed text-white"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Distribución de Camas</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Click en una cama para asignar o liberar</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-emerald-100 dark:bg-emerald-900/30 border-2 border-emerald-500"></span>
                    <span class="text-slate-500 dark:text-slate-400">Libre</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/30 border-2 border-blue-500"></span>
                    <span class="text-slate-500 dark:text-slate-400">Ocupada</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded bg-slate-100 dark:bg-slate-700 border-2 border-slate-400"></span>
                    <span class="text-slate-500 dark:text-slate-400">Mantenimiento</span>
                </div>
            </div>
        </div>
        <div class="p-6">
            @php
            $camas = [
                ['id' => 1, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Juan Pérez', 'numero' => '15']],
                ['id' => 2, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'María González', 'numero' => '23']],
                ['id' => 3, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Carlos Muñoz', 'numero' => '45']],
                ['id' => 4, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Ana Silva', 'numero' => '67']],
                ['id' => 5, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Roberto Díaz', 'numero' => '12']],
                ['id' => 6, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Diego Fernández', 'numero' => '33']],
                ['id' => 7, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Patricia Rojas', 'numero' => '77']],
                ['id' => 8, 'estado' => 'ocupada', 'ocupante' => ['nombre' => 'Felipe Araya', 'numero' => '44']],
                ['id' => 9, 'estado' => 'libre', 'ocupante' => null],
                ['id' => 10, 'estado' => 'libre', 'ocupante' => null],
                ['id' => 11, 'estado' => 'libre', 'ocupante' => null],
                ['id' => 12, 'estado' => 'mantenimiento', 'ocupante' => null],
                ['id' => 13, 'estado' => 'libre', 'ocupante' => null],
                ['id' => 14, 'estado' => 'libre', 'ocupante' => null],
                ['id' => 15, 'estado' => 'libre', 'ocupante' => null],
            ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($camas as $cama)
                @php
                $estadoClasses = [
                    'libre' => 'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 hover:border-emerald-400 dark:hover:border-emerald-600 hover:shadow-emerald-100 dark:hover:shadow-emerald-900/20',
                    'ocupada' => 'border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600 hover:shadow-blue-100 dark:hover:shadow-blue-900/20',
                    'mantenimiento' => 'border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 cursor-not-allowed opacity-60',
                ];
                $iconClasses = [
                    'libre' => 'text-emerald-500 dark:text-emerald-400',
                    'ocupada' => 'text-blue-500 dark:text-blue-400',
                    'mantenimiento' => 'text-slate-400 dark:text-slate-500',
                ];
                @endphp
                <div class="relative group rounded-2xl border-2 p-4 transition-all duration-200 cursor-pointer hover:shadow-lg {{ $estadoClasses[$cama['estado']] }}"
                     @if($cama['estado'] !== 'mantenimiento') onclick="toggleCama({{ $cama['id'] }})" @endif>
                    {{-- Cama Number --}}
                    <div class="absolute -top-2 -left-2 w-7 h-7 rounded-lg {{ $cama['estado'] === 'ocupada' ? 'bg-blue-500' : ($cama['estado'] === 'libre' ? 'bg-emerald-500' : 'bg-slate-400') }} flex items-center justify-center text-white text-xs font-bold shadow-lg">
                        {{ $cama['id'] }}
                    </div>
                    
                    {{-- Bed Icon --}}
                    <div class="flex justify-center mb-3 pt-2">
                        <i class="fas fa-bed text-3xl {{ $iconClasses[$cama['estado']] }}"></i>
                    </div>
                    
                    {{-- Status --}}
                    @if($cama['estado'] === 'ocupada' && $cama['ocupante'])
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-2 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 text-sm font-bold">
                            {{ strtoupper(substr($cama['ocupante']['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $cama['ocupante']['nombre'])[1] ?? '', 0, 1)) }}
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $cama['ocupante']['nombre'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">#{{ $cama['ocupante']['numero'] }}</p>
                    </div>
                    @elseif($cama['estado'] === 'libre')
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-2 rounded-lg border-2 border-dashed border-emerald-300 dark:border-emerald-600 flex items-center justify-center">
                            <i class="fas fa-plus text-emerald-400 dark:text-emerald-500"></i>
                        </div>
                        <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Disponible</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Click para asignar</p>
                    </div>
                    @else
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-2 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                            <i class="fas fa-wrench text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Mantenimiento</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">No disponible</p>
                    </div>
                    @endif
                    
                    {{-- Hover Action --}}
                    @if($cama['estado'] === 'ocupada')
                    <div class="absolute inset-0 rounded-2xl bg-red-500/90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="text-center text-white">
                            <i class="fas fa-times-circle text-2xl mb-1"></i>
                            <p class="text-xs font-medium">Liberar cama</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Historial Reciente --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="font-semibold text-slate-900 dark:text-white">Historial de Asignaciones</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @php
            $historial = [
                ['accion' => 'asignada', 'cama' => 8, 'bombero' => 'Felipe Araya', 'hora' => '20:45'],
                ['accion' => 'liberada', 'cama' => 9, 'bombero' => 'Carmen López', 'hora' => '20:30'],
                ['accion' => 'asignada', 'cama' => 7, 'bombero' => 'Patricia Rojas', 'hora' => '20:15'],
                ['accion' => 'asignada', 'cama' => 6, 'bombero' => 'Diego Fernández', 'hora' => '20:00'],
            ];
            @endphp
            @foreach($historial as $h)
            <div class="px-6 py-3 flex items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <div class="w-8 h-8 rounded-lg {{ $h['accion'] === 'asignada' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-emerald-100 dark:bg-emerald-900/30' }} flex items-center justify-center">
                    <i class="fas {{ $h['accion'] === 'asignada' ? 'fa-arrow-right text-blue-600 dark:text-blue-400' : 'fa-arrow-left text-emerald-600 dark:text-emerald-400' }} text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-slate-900 dark:text-white">
                        <span class="font-medium">Cama {{ $h['cama'] }}</span>
                        {{ $h['accion'] === 'asignada' ? 'asignada a' : 'liberada por' }}
                        <span class="font-medium">{{ $h['bombero'] }}</span>
                    </p>
                </div>
                <span class="text-xs text-slate-400 dark:text-slate-500">{{ $h['hora'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCama(camaId) {
    // Placeholder for cama toggle functionality
    console.log('Toggle cama:', camaId);
}
</script>
@endpush
@endsection
