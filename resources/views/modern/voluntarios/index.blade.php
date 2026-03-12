@extends('layouts.modern')

@section('title', 'Voluntarios - ' . branding()->nombre_empresa)
@section('page-title', 'Voluntarios')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Voluntarios</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gestión del personal de la compañía</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-file-export mr-2"></i>
                Exportar
            </button>
            <button class="px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors shadow-lg shadow-slate-900/10 dark:shadow-white/10">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Voluntario
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalVoluntarios ?? 48 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <i class="fas fa-user-check text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $activos ?? 42 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Activos</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-shield-halved text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $enGuardia ?? 12 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">En Guardia</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <i class="fas fa-user-clock text-purple-600 dark:text-purple-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $conPermiso ?? 3 }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Con Permiso</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            {{-- Search --}}
            <div class="flex-1 relative">
                <input type="text" 
                       placeholder="Buscar por nombre, número o RUT..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white focus:border-transparent text-sm">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                <select class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white">
                    <option value="">Todos los cargos</option>
                    <option value="capitan">Capitán</option>
                    <option value="teniente">Teniente</option>
                    <option value="voluntario">Voluntario</option>
                </select>
                <select class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="licencia">En Licencia</option>
                </select>
                <select class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white">
                    <option value="">Todas las guardias</option>
                    <option value="1">Guardia 1</option>
                    <option value="2">Guardia 2</option>
                    <option value="3">Guardia 3</option>
                    <option value="4">Guardia 4</option>
                </select>
                <button class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-filter-circle-xmark mr-1"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-4 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Voluntario</th>
                        <th class="px-6 py-4 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cargo</th>
                        <th class="px-6 py-4 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Guardia</th>
                        <th class="px-6 py-4 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-4 text-right text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                    $voluntarios = [
                        ['nombre' => 'Juan Pérez', 'numero' => '15', 'cargo' => 'Capitán', 'guardia' => 1, 'estado' => 'activo', 'telefono' => '+56 9 1234 5678', 'email' => 'juan@email.com', 'enGuardia' => true],
                        ['nombre' => 'María González', 'numero' => '23', 'cargo' => 'Teniente', 'guardia' => 1, 'estado' => 'activo', 'telefono' => '+56 9 2345 6789', 'email' => 'maria@email.com', 'enGuardia' => true],
                        ['nombre' => 'Carlos Muñoz', 'numero' => '45', 'cargo' => 'Voluntario', 'guardia' => 2, 'estado' => 'activo', 'telefono' => '+56 9 3456 7890', 'email' => 'carlos@email.com', 'enGuardia' => false],
                        ['nombre' => 'Ana Silva', 'numero' => '67', 'cargo' => 'Voluntario', 'guardia' => 1, 'estado' => 'licencia', 'telefono' => '+56 9 4567 8901', 'email' => 'ana@email.com', 'enGuardia' => false],
                        ['nombre' => 'Roberto Díaz', 'numero' => '12', 'cargo' => 'Voluntario', 'guardia' => 3, 'estado' => 'activo', 'telefono' => '+56 9 5678 9012', 'email' => 'roberto@email.com', 'enGuardia' => false],
                        ['nombre' => 'Carmen López', 'numero' => '89', 'cargo' => 'Voluntario', 'guardia' => 2, 'estado' => 'activo', 'telefono' => '+56 9 6789 0123', 'email' => 'carmen@email.com', 'enGuardia' => false],
                        ['nombre' => 'Diego Fernández', 'numero' => '33', 'cargo' => 'Voluntario', 'guardia' => 4, 'estado' => 'inactivo', 'telefono' => '+56 9 7890 1234', 'email' => 'diego@email.com', 'enGuardia' => false],
                        ['nombre' => 'Patricia Rojas', 'numero' => '77', 'cargo' => 'Voluntario', 'guardia' => 1, 'estado' => 'activo', 'telefono' => '+56 9 8901 2345', 'email' => 'patricia@email.com', 'enGuardia' => true],
                    ];
                    $estadoConfig = [
                        'activo' => ['color' => 'emerald', 'label' => 'Activo'],
                        'inactivo' => ['color' => 'slate', 'label' => 'Inactivo'],
                        'licencia' => ['color' => 'amber', 'label' => 'Licencia'],
                    ];
                    $guardiaColors = ['1' => 'red', '2' => 'blue', '3' => 'emerald', '4' => 'purple'];
                    @endphp
                    @foreach($voluntarios as $v)
                    @php $ec = $estadoConfig[$v['estado']]; $gc = $guardiaColors[$v['guardia']] ?? 'slate'; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 text-sm font-bold">
                                        {{ strtoupper(substr($v['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $v['nombre'])[1], 0, 1)) }}
                                    </div>
                                    @if($v['enGuardia'])
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $v['nombre'] }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">#{{ $v['numero'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $v['cargo'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-{{ $gc }}-100 dark:bg-{{ $gc }}-900/30 text-{{ $gc }}-700 dark:text-{{ $gc }}-400">
                                Guardia {{ $v['guardia'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-{{ $ec['color'] }}-100 dark:bg-{{ $ec['color'] }}-900/30 text-{{ $ec['color'] }}-700 dark:text-{{ $ec['color'] }}-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $ec['color'] }}-500"></span>
                                {{ $ec['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $v['telefono'] }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $v['email'] }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Ver perfil">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="p-2 rounded-lg text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Mostrando <span class="font-medium text-slate-700 dark:text-slate-300">1</span> a <span class="font-medium text-slate-700 dark:text-slate-300">8</span> de <span class="font-medium text-slate-700 dark:text-slate-300">48</span> voluntarios
            </p>
            <div class="flex items-center gap-1">
                <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="w-9 h-9 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-medium">1</button>
                <button class="w-9 h-9 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition-colors">2</button>
                <button class="w-9 h-9 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition-colors">3</button>
                <span class="px-2 text-slate-400">...</span>
                <button class="w-9 h-9 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition-colors">6</button>
                <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
