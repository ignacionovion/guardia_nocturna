@extends('layouts.modern')

@section('title', 'Dashboard - ' . branding()->nombre_empresa)
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-slate-800 dark:via-slate-900 dark:to-black p-6 sm:p-8">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.05)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>
        <div class="relative">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Cuartel Operativo
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-1">
                        Bienvenido, {{ explode(' ', Auth::user()->name)[0] }}
                    </h1>
                    <p class="text-slate-400 text-sm sm:text-base">
                        Centro de comando de {{ branding()->nombre_empresa }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('guardia.now') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-slate-900 text-sm font-semibold hover:bg-slate-50 transition-colors shadow-lg border border-slate-200">
                        <i class="fas fa-satellite-dish"></i>
                        Ver Guardia en Vivo
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Bomberos en Guardia --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">En Guardia</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $bomberosEnGuardia ?? 12 }}</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i>
                        <span>Dotación completa</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <i class="fas fa-user-group text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
        </div>

        {{-- Camas Ocupadas --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Camas</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $camasOcupadas ?? 8 }}<span class="text-lg text-slate-400">/{{ $camasTotal ?? 15 }}</span></p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        {{ $camasTotal - $camasOcupadas ?? 7 }} disponibles
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-bed text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>

        {{-- Emergencias Hoy --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Emergencias</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $emergenciasHoy ?? 3 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Hoy
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="fas fa-truck-medical text-red-600 dark:text-red-400"></i>
                </div>
            </div>
        </div>

        {{-- Novedades Activas --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-slate-900/50 transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Novedades</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $novedadesActivas ?? 5 }}</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                        {{ $novedadesPendientes ?? 2 }} pendientes
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Dotación Actual --}}
        <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                        <i class="fas fa-shield-halved text-white"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900 dark:text-white">Dotación Actual</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Guardia {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('guardia.now') }}" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Ver todo <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                    $dotacionEjemplo = [
                        ['nombre' => 'Juan Pérez', 'cargo' => 'Capitán', 'numero' => '15', 'estado' => 'constituye'],
                        ['nombre' => 'María González', 'cargo' => 'Teniente', 'numero' => '23', 'estado' => 'constituye'],
                        ['nombre' => 'Carlos Muñoz', 'cargo' => 'Voluntario', 'numero' => '45', 'estado' => 'reemplazo'],
                        ['nombre' => 'Ana Silva', 'cargo' => 'Voluntario', 'numero' => '67', 'estado' => 'refuerzo'],
                        ['nombre' => 'Pedro Soto', 'cargo' => 'Voluntario', 'numero' => '34', 'estado' => 'constituye'],
                        ['nombre' => 'Luis Vera', 'cargo' => 'Voluntario', 'numero' => '56', 'estado' => 'permiso'],
                    ];
                    $estadoConfig = [
                        'constituye' => ['color' => 'emerald', 'label' => 'Constituye', 'icon' => 'fa-check-circle'],
                        'reemplazo' => ['color' => 'amber', 'label' => 'Reemplazo', 'icon' => 'fa-people-arrows'],
                        'refuerzo' => ['color' => 'blue', 'label' => 'Refuerzo', 'icon' => 'fa-user-plus'],
                        'permiso' => ['color' => 'purple', 'label' => 'Permiso', 'icon' => 'fa-calendar-check'],
                        'ausente' => ['color' => 'red', 'label' => 'Ausente', 'icon' => 'fa-user-xmark'],
                    ];
                    @endphp
                    @foreach($dotacionEjemplo as $bombero)
                    @php $config = $estadoConfig[$bombero['estado']]; @endphp
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 hover:border-slate-300 transition-colors shadow-sm">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-300 dark:to-slate-500 flex items-center justify-center text-white dark:text-slate-900 text-sm font-bold">
                            {{ strtoupper(substr($bombero['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $bombero['nombre'])[1], 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-900 dark:text-white text-sm truncate">{{ $bombero['nombre'] }}</span>
                                <span class="text-xs text-slate-400">#{{ $bombero['numero'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $bombero['cargo'] }}</span>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold uppercase bg-{{ $config['color'] }}-100 dark:bg-{{ $config['color'] }}-900/30 text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-400">
                            <i class="fas {{ $config['icon'] }}"></i>
                            {{ $config['label'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Panel Lateral --}}
        <div class="space-y-6">
            {{-- Accesos Rápidos --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Accesos Rápidos</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('camas') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 transition-colors group shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-bed text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Camas</span>
                    </a>
                    <a href="{{ route('admin.volunteers.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 dark:border-slate-700/50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-group text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Voluntarios</span>
                    </a>
                    <a href="{{ route('admin.emergencies.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 dark:border-slate-700/50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-truck-medical text-red-600 dark:text-red-400"></i>
                        </div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Emergencias</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 dark:border-slate-700/50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Reportes</span>
                    </a>
                </div>
            </div>

            {{-- Estado de Camas Mini --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Estado de Camas</h3>
                    <a href="{{ route('camas') }}" class="text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300">
                        Ver todas
                    </a>
                </div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-1">
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: {{ ($camasOcupadas ?? 8) / ($camasTotal ?? 15) * 100 }}%"></div>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ round(($camasOcupadas ?? 8) / ($camasTotal ?? 15) * 100) }}%</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $camasOcupadas ?? 8 }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase">Ocupadas</p>
                    </div>
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ ($camasTotal ?? 15) - ($camasOcupadas ?? 8) }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase">Libres</p>
                    </div>
                    <div class="p-2 rounded-lg bg-white border border-slate-200">
                        <p class="text-lg font-bold text-slate-600">{{ $camasTotal ?? 15 }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Total</p>
                    </div>
                </div>
            </div>

            {{-- Actividad Reciente --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Actividad Reciente</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Asistencia guardada</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 5 minutos</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <i class="fas fa-bed text-blue-600 dark:text-blue-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Cama 3 asignada</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 15 minutos</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                            <i class="fas fa-people-arrows text-amber-600 dark:text-amber-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Reemplazo registrado</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 1 hora</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                            <i class="fas fa-truck-medical text-red-600 dark:text-red-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Emergencia atendida</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 2 horas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Novedades Activas --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-white"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Novedades Activas</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Permisos, licencias y observaciones</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <button class="px-3 py-1.5 text-xs font-medium text-white bg-slate-900 dark:bg-white dark:bg-slate-900 dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> Nueva
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bombero</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Motivo</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-300 dark:to-slate-500 flex items-center justify-center text-white dark:text-slate-900 text-xs font-bold">PS</div>
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-white text-sm">Pedro Soto</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">#34</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-semibold uppercase bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                <i class="fas fa-calendar-check mr-1"></i> Permiso
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 dark:text-slate-300">Cita médica</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">12 Mar 2026</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-semibold uppercase bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                Pendiente
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-300 dark:to-slate-500 flex items-center justify-center text-white dark:text-slate-900 text-xs font-bold">LV</div>
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-white text-sm">Luis Vera</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">#56</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-semibold uppercase bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400">
                                <i class="fas fa-file-medical mr-1"></i> Licencia
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 dark:text-slate-300">Licencia médica 3 días</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">10-13 Mar 2026</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-semibold uppercase bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                Aprobado
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
