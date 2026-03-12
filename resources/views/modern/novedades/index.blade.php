@extends('layouts.modern')

@section('title', 'Novedades - ' . branding()->nombre_empresa)
@section('page-title', 'Novedades')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Novedades</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Permisos, licencias, reemplazos y observaciones</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-white dark:bg-slate-900 text-white dark:text-slate-900 text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10 dark:shadow-white/10">
                <i class="fas fa-plus mr-2"></i>
                Nueva Novedad
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4 hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalNovedades ?? 12 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Total</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-clipboard-list text-slate-500 dark:text-slate-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4 hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $permisos ?? 4 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Permisos</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-check text-purple-500 dark:text-purple-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4 hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $licencias ?? 2 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Licencias</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-medical text-cyan-500 dark:text-cyan-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4 hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $reemplazos ?? 3 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Reemplazos</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-people-arrows text-amber-500 dark:text-amber-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4 hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $pendientes ?? 3 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Pendientes</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock text-red-500 dark:text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex-1 relative">
                <input type="text" 
                       placeholder="Buscar por bombero o motivo..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white focus:border-transparent text-sm">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white dark:bg-slate-900 text-white dark:text-slate-900 text-sm font-medium">
                    Todas
                </button>
                <button class="px-4 py-2 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-sm font-medium hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                    <i class="fas fa-calendar-check mr-1"></i> Permisos
                </button>
                <button class="px-4 py-2 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 text-sm font-medium hover:bg-cyan-200 dark:hover:bg-cyan-900/50 transition-colors">
                    <i class="fas fa-file-medical mr-1"></i> Licencias
                </button>
                <button class="px-4 py-2 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-sm font-medium hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">
                    <i class="fas fa-people-arrows mr-1"></i> Reemplazos
                </button>
                <select class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Novedades Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @php
        $novedades = [
            ['tipo' => 'permiso', 'bombero' => 'Pedro Soto', 'numero' => '34', 'motivo' => 'Cita médica programada', 'fecha' => '12 Mar 2026', 'estado' => 'pendiente', 'guardia' => 'Guardia 1'],
            ['tipo' => 'licencia', 'bombero' => 'Luis Vera', 'numero' => '56', 'motivo' => 'Licencia médica por 3 días - Reposo absoluto', 'fecha' => '10-13 Mar 2026', 'estado' => 'aprobado', 'guardia' => 'Guardia 2'],
            ['tipo' => 'reemplazo', 'bombero' => 'Felipe Araya', 'numero' => '44', 'motivo' => 'Reemplaza a Pedro Soto por permiso médico', 'fecha' => '12 Mar 2026', 'estado' => 'aprobado', 'guardia' => 'Guardia 1'],
            ['tipo' => 'permiso', 'bombero' => 'Ana Silva', 'numero' => '67', 'motivo' => 'Asunto personal - Trámite bancario', 'fecha' => '13 Mar 2026', 'estado' => 'pendiente', 'guardia' => 'Guardia 1'],
            ['tipo' => 'licencia', 'bombero' => 'Rosa Martínez', 'numero' => '78', 'motivo' => 'Licencia maternal', 'fecha' => '01 Mar - 01 Jun 2026', 'estado' => 'aprobado', 'guardia' => 'Guardia 3'],
            ['tipo' => 'reemplazo', 'bombero' => 'Javiera Mora', 'numero' => '55', 'motivo' => 'Reemplaza a Luis Vera por licencia médica', 'fecha' => '10-13 Mar 2026', 'estado' => 'aprobado', 'guardia' => 'Guardia 2'],
        ];
        $tipoConfig = [
            'permiso' => ['color' => 'purple', 'icon' => 'fa-calendar-check', 'label' => 'Permiso', 'bg' => 'from-purple-500 to-purple-600'],
            'licencia' => ['color' => 'cyan', 'icon' => 'fa-file-medical', 'label' => 'Licencia', 'bg' => 'from-cyan-500 to-cyan-600'],
            'reemplazo' => ['color' => 'amber', 'icon' => 'fa-people-arrows', 'label' => 'Reemplazo', 'bg' => 'from-amber-500 to-amber-600'],
        ];
        $estadoConfig = [
            'pendiente' => ['color' => 'amber', 'label' => 'Pendiente'],
            'aprobado' => ['color' => 'emerald', 'label' => 'Aprobado'],
            'rechazado' => ['color' => 'red', 'label' => 'Rechazado'],
        ];
        @endphp
        @foreach($novedades as $n)
        @php $tc = $tipoConfig[$n['tipo']]; $ec = $estadoConfig[$n['estado']]; @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden hover:shadow-lg transition-shadow group">
            <div class="flex">
                {{-- Left Color Bar --}}
                <div class="w-1.5 bg-gradient-to-b {{ $tc['bg'] }}"></div>
                
                {{-- Content --}}
                <div class="flex-1 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl bg-{{ $tc['color'] }}-100 dark:bg-{{ $tc['color'] }}-900/30 flex items-center justify-center shrink-0">
                                <i class="fas {{ $tc['icon'] }} text-{{ $tc['color'] }}-600 dark:text-{{ $tc['color'] }}-400 text-lg"></i>
                            </div>
                            
                            {{-- Info --}}
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-{{ $tc['color'] }}-100 dark:bg-{{ $tc['color'] }}-900/30 text-{{ $tc['color'] }}-700 dark:text-{{ $tc['color'] }}-400">
                                        {{ $tc['label'] }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-{{ $ec['color'] }}-100 dark:bg-{{ $ec['color'] }}-900/30 text-{{ $ec['color'] }}-700 dark:text-{{ $ec['color'] }}-400">
                                        {{ $ec['label'] }}
                                    </span>
                                </div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $n['bombero'] }} <span class="text-slate-400 font-normal">#{{ $n['numero'] }}</span></h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $n['motivo'] }}</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-slate-500 dark:text-slate-400">
                                    <span><i class="fas fa-calendar mr-1"></i> {{ $n['fecha'] }}</span>
                                    <span><i class="fas fa-shield-halved mr-1"></i> {{ $n['guardia'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @if($n['estado'] === 'pendiente')
                            <button class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors" title="Aprobar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Rechazar">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 transition-colors" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 transition-colors" title="Editar">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Timeline Section --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800">
            <h2 class="font-semibold text-slate-900 dark:text-white">Historial Reciente</h2>
        </div>
        <div class="p-6">
            <div class="relative">
                {{-- Timeline Line --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700"></div>
                
                {{-- Timeline Items --}}
                <div class="space-y-6">
                    @php
                    $historial = [
                        ['accion' => 'Permiso aprobado', 'detalle' => 'Pedro Soto - Cita médica', 'hora' => 'Hace 2 horas', 'color' => 'emerald', 'icon' => 'fa-check'],
                        ['accion' => 'Nueva licencia registrada', 'detalle' => 'Luis Vera - Licencia médica 3 días', 'hora' => 'Hace 5 horas', 'color' => 'cyan', 'icon' => 'fa-file-medical'],
                        ['accion' => 'Reemplazo asignado', 'detalle' => 'Felipe Araya reemplaza a Pedro Soto', 'hora' => 'Hace 6 horas', 'color' => 'amber', 'icon' => 'fa-people-arrows'],
                        ['accion' => 'Permiso solicitado', 'detalle' => 'Ana Silva - Asunto personal', 'hora' => 'Ayer 18:30', 'color' => 'purple', 'icon' => 'fa-calendar-check'],
                    ];
                    @endphp
                    @foreach($historial as $h)
                    <div class="relative flex items-start gap-4 pl-10">
                        {{-- Dot --}}
                        <div class="absolute left-0 w-8 h-8 rounded-full bg-{{ $h['color'] }}-100 dark:bg-{{ $h['color'] }}-900/30 border-4 border-white dark:border-slate-900 flex items-center justify-center">
                            <i class="fas {{ $h['icon'] }} text-{{ $h['color'] }}-600 dark:text-{{ $h['color'] }}-400 text-xs"></i>
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex-1 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-slate-900 dark:text-white text-sm">{{ $h['accion'] }}</p>
                                <span class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400">{{ $h['hora'] }}</span>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $h['detalle'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
