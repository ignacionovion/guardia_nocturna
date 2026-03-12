@extends('layouts.modern')

@section('title', 'Guardia en Vivo - ' . branding()->nombre_empresa)
@section('page-title', 'Guardia en Vivo')

@section('content')
<div class="space-y-6">
    {{-- Live Status Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-800 p-6">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                    <i class="fas fa-satellite-dish text-white text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-white">Centro de Comando</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            EN VIVO
                        </span>
                    </div>
                    <p class="text-emerald-100">Guardia {{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur">
                    <i class="fas fa-clock text-white/70"></i>
                    <span class="text-white font-semibold" id="live-clock">{{ now()->format('H:i:s') }}</span>
                </div>
                <button class="px-4 py-2 rounded-xl bg-white text-emerald-700 font-semibold text-sm hover:bg-emerald-50 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Exportar PDF
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/20 text-white font-semibold text-sm hover:bg-white/30 transition-colors backdrop-blur">
                    <i class="fas fa-envelope mr-2"></i>
                    Enviar Snapshot
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalPresentes ?? 12 }}</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Presentes</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $totalReemplazos ?? 2 }}</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Reemplazos</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $totalRefuerzos ?? 1 }}</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Refuerzos</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-center">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $totalPermisos ?? 3 }}</div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Permisos</div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        {{-- Dotación Principal --}}
        <div class="xl:col-span-3 space-y-6">
            {{-- Presentes --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-emerald-50 dark:bg-emerald-900/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-white">Dotación Presente</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Bomberos que constituyen guardia</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-emerald-500 text-white">{{ $totalPresentes ?? 12 }}</span>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @php
                        $bomberosPresentes = [
                            ['nombre' => 'Juan Pérez', 'cargo' => 'Capitán', 'numero' => '15', 'cama' => 1, 'confirmado' => true],
                            ['nombre' => 'María González', 'cargo' => 'Teniente', 'numero' => '23', 'cama' => 2, 'confirmado' => true],
                            ['nombre' => 'Carlos Muñoz', 'cargo' => 'Voluntario', 'numero' => '45', 'cama' => 3, 'confirmado' => true],
                            ['nombre' => 'Ana Silva', 'cargo' => 'Voluntario', 'numero' => '67', 'cama' => 4, 'confirmado' => false],
                            ['nombre' => 'Roberto Díaz', 'cargo' => 'Voluntario', 'numero' => '12', 'cama' => 5, 'confirmado' => true],
                            ['nombre' => 'Carmen López', 'cargo' => 'Voluntario', 'numero' => '89', 'cama' => null, 'confirmado' => true],
                            ['nombre' => 'Diego Fernández', 'cargo' => 'Voluntario', 'numero' => '33', 'cama' => 6, 'confirmado' => true],
                            ['nombre' => 'Patricia Rojas', 'cargo' => 'Voluntario', 'numero' => '77', 'cama' => 7, 'confirmado' => true],
                        ];
                        @endphp
                        @foreach($bomberosPresentes as $bombero)
                        <div class="relative group p-4 rounded-xl border-2 {{ $bombero['confirmado'] ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-900/10' : 'border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10' }} hover:shadow-md transition-all">
                            @if($bombero['confirmado'])
                            <div class="absolute top-2 right-2">
                                <span class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </span>
                            </div>
                            @else
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500 text-white animate-pulse">
                                    Pendiente
                                </span>
                            </div>
                            @endif
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 font-bold">
                                    {{ strtoupper(substr($bombero['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $bombero['nombre'])[1], 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $bombero['nombre'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $bombero['cargo'] }} · #{{ $bombero['numero'] }}</div>
                                    @if($bombero['cama'])
                                    <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[10px] font-medium">
                                        <i class="fas fa-bed"></i>
                                        Cama {{ $bombero['cama'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Reemplazos y Refuerzos --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Reemplazos --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-amber-50 dark:bg-amber-900/20">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                                <i class="fas fa-people-arrows text-white text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-900 dark:text-white">Reemplazos</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-white">{{ $totalReemplazos ?? 2 }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @php
                        $reemplazos = [
                            ['nombre' => 'Felipe Araya', 'numero' => '44', 'reemplaza' => 'Pedro Soto', 'cama' => 8],
                            ['nombre' => 'Javiera Mora', 'numero' => '55', 'reemplaza' => 'Luis Vera', 'cama' => null],
                        ];
                        @endphp
                        @foreach($reemplazos as $r)
                        <div class="p-3 rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-900/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($r['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $r['nombre'])[1], 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-slate-900 dark:text-white text-sm">{{ $r['nombre'] }} <span class="text-slate-400">#{{ $r['numero'] }}</span></div>
                                    <div class="text-xs text-amber-600 dark:text-amber-400">
                                        <i class="fas fa-arrow-right-arrow-left mr-1"></i>
                                        Reemplaza a {{ $r['reemplaza'] }}
                                    </div>
                                </div>
                                @if($r['cama'])
                                <span class="px-2 py-1 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[10px] font-medium">
                                    <i class="fas fa-bed mr-1"></i>{{ $r['cama'] }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Refuerzos --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center">
                                <i class="fas fa-user-plus text-white text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-900 dark:text-white">Refuerzos</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-500 text-white">{{ $totalRefuerzos ?? 1 }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @php
                        $refuerzos = [
                            ['nombre' => 'Andrés Castillo', 'numero' => '99', 'motivo' => 'Apoyo por emergencia', 'cama' => null],
                        ];
                        @endphp
                        @foreach($refuerzos as $ref)
                        <div class="p-3 rounded-xl border border-blue-200 dark:border-blue-800/50 bg-blue-50/50 dark:bg-blue-900/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($ref['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $ref['nombre'])[1], 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-slate-900 dark:text-white text-sm">{{ $ref['nombre'] }} <span class="text-slate-400">#{{ $ref['numero'] }}</span></div>
                                    <div class="text-xs text-blue-600 dark:text-blue-400">
                                        <i class="fas fa-plus-circle mr-1"></i>
                                        {{ $ref['motivo'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if(count($refuerzos) === 0)
                        <div class="text-center py-6 text-slate-400 dark:text-slate-500">
                            <i class="fas fa-user-plus text-2xl mb-2"></i>
                            <p class="text-sm">Sin refuerzos</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ausencias --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                            <i class="fas fa-user-clock text-slate-500 dark:text-slate-400"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-white">Ausencias y Permisos</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Bomberos no disponibles hoy</p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @php
                        $ausencias = [
                            ['nombre' => 'Pedro Soto', 'numero' => '34', 'tipo' => 'permiso', 'motivo' => 'Cita médica'],
                            ['nombre' => 'Luis Vera', 'numero' => '56', 'tipo' => 'licencia', 'motivo' => 'Licencia médica'],
                            ['nombre' => 'Rosa Martínez', 'numero' => '78', 'tipo' => 'ausente', 'motivo' => 'Sin justificar'],
                        ];
                        $tipoConfig = [
                            'permiso' => ['color' => 'purple', 'icon' => 'fa-calendar-check', 'label' => 'Permiso'],
                            'licencia' => ['color' => 'cyan', 'icon' => 'fa-file-medical', 'label' => 'Licencia'],
                            'ausente' => ['color' => 'red', 'icon' => 'fa-user-xmark', 'label' => 'Ausente'],
                        ];
                        @endphp
                        @foreach($ausencias as $a)
                        @php $tc = $tipoConfig[$a['tipo']]; @endphp
                        <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-{{ $tc['color'] }}-100 dark:bg-{{ $tc['color'] }}-900/30 flex items-center justify-center">
                                    <i class="fas {{ $tc['icon'] }} text-{{ $tc['color'] }}-600 dark:text-{{ $tc['color'] }}-400 text-xs"></i>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-{{ $tc['color'] }}-100 dark:bg-{{ $tc['color'] }}-900/30 text-{{ $tc['color'] }}-700 dark:text-{{ $tc['color'] }}-400">
                                    {{ $tc['label'] }}
                                </span>
                            </div>
                            <div class="font-medium text-slate-900 dark:text-white text-sm">{{ $a['nombre'] }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">#{{ $a['numero'] }} · {{ $a['motivo'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Lateral --}}
        <div class="space-y-6">
            {{-- Resumen Operativo --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-slate-400"></i>
                    Resumen Operativo
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Dotación requerida</span>
                        <span class="font-bold text-slate-900 dark:text-white">15</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                        <span class="text-sm text-emerald-700 dark:text-emerald-400">Dotación actual</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ ($totalPresentes ?? 12) + ($totalReemplazos ?? 2) + ($totalRefuerzos ?? 1) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                        <span class="text-sm text-blue-700 dark:text-blue-400">Camas asignadas</span>
                        <span class="font-bold text-blue-700 dark:text-blue-400">{{ $camasAsignadas ?? 8 }}/{{ $camasTotal ?? 15 }}</span>
                    </div>
                </div>
            </div>

            {{-- Estado de Camas --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-bed text-slate-400"></i>
                        Camas
                    </h3>
                    <a href="{{ route('camas') }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Ver todas</a>
                </div>
                <div class="grid grid-cols-5 gap-2">
                    @for($i = 1; $i <= 15; $i++)
                    @php $ocupada = $i <= 8; @endphp
                    <div class="aspect-square rounded-lg {{ $ocupada ? 'bg-blue-500' : 'bg-slate-200 dark:bg-slate-700' }} flex items-center justify-center text-xs font-bold {{ $ocupada ? 'text-white' : 'text-slate-400' }}" title="Cama {{ $i }} - {{ $ocupada ? 'Ocupada' : 'Libre' }}">
                        {{ $i }}
                    </div>
                    @endfor
                </div>
                <div class="flex items-center justify-center gap-4 mt-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-blue-500"></span>
                        <span class="text-slate-500 dark:text-slate-400">Ocupada</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-slate-200 dark:bg-slate-700"></span>
                        <span class="text-slate-500 dark:text-slate-400">Libre</span>
                    </div>
                </div>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Acciones</h3>
                <div class="space-y-2">
                    <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors text-sm font-medium">
                        <i class="fas fa-check-circle w-5"></i>
                        Guardar Asistencia
                    </button>
                    <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors text-sm font-medium">
                        <i class="fas fa-people-arrows w-5"></i>
                        Registrar Reemplazo
                    </button>
                    <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-sm font-medium">
                        <i class="fas fa-user-plus w-5"></i>
                        Agregar Refuerzo
                    </button>
                    <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors text-sm font-medium">
                        <i class="fas fa-calendar-check w-5"></i>
                        Registrar Permiso
                    </button>
                </div>
            </div>

            {{-- Última Actualización --}}
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">Última actualización</p>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mt-1">{{ now()->format('H:i:s') }}</p>
                <button class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    <i class="fas fa-sync-alt mr-1"></i> Actualizar ahora
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live clock
    setInterval(() => {
        const clock = document.getElementById('live-clock');
        if (clock) {
            clock.textContent = new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }, 1000);
</script>
@endpush
@endsection
