@extends('layouts.modern')

@section('content')
<div class="w-full">
    <x-ui.page-header title="Camas" subtitle="Gestión de camas y espacios de descanso" icon="fas fa-bed" iconVariant="primary">
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.beds.create') }}">
            Nueva cama
        </x-ui.button>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" icon="fas fa-exclamation-circle" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    {{-- Banner Guardia Activa --}}
    @if($activeGuardia)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-900">Guardia Activa: {{ $activeGuardia->name }}</h3>
                    <p class="text-xs text-blue-700">Solo se pueden asignar camas a voluntarios presentes en esta guardia</p>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-900">Sin Guardia Activa</h3>
                    <p class="text-xs text-amber-700">No se pueden asignar camas sin una guardia activa</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas Compactas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bed text-blue-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-[#1e293b]">{{ $stats['total'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Disponibles</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $stats['available'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-red-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Ocupadas</p>
                    <p class="text-2xl font-bold text-red-700">{{ $stats['occupied'] }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-tools text-amber-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Mantención</p>
                    <p class="text-2xl font-bold text-amber-700">{{ $stats['maintenance'] + $stats['disabled'] }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Resumen por Género y Sector --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Por Género --}}
        <x-ui.card>
            <div class="p-5 border-b border-[#9fb0c3] bg-[#c3cfdb]">
                <h3 class="text-sm font-bold text-[#1e293b]">Distribución por Género</h3>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-mars text-blue-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-[#1e293b]">Masculino</span>
                        </div>
                        <span class="text-lg font-bold text-[#1e293b]">{{ $statsByGender['male'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-venus text-pink-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-[#1e293b]">Femenino</span>
                        </div>
                        <span class="text-lg font-bold text-[#1e293b]">{{ $statsByGender['female'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-venus-mars text-purple-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-[#1e293b]">Mixto</span>
                        </div>
                        <span class="text-lg font-bold text-[#1e293b]">{{ $statsByGender['mixed'] }}</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Por Sector/Pieza --}}
        <x-ui.card>
            <div class="p-5 border-b border-[#9fb0c3] bg-[#c3cfdb]">
                <h3 class="text-sm font-bold text-[#1e293b]">Distribución por Sector</h3>
            </div>
            <div class="p-5">
                @if($statsByRoom->isEmpty())
                    <p class="text-sm text-[#475569] text-center py-4">No hay sectores definidos</p>
                @else
                    <div class="space-y-3 max-h-40 overflow-y-auto">
                        @foreach($statsByRoom as $roomStat)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-door-open text-indigo-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-[#1e293b] truncate">{{ $roomStat['room'] }}</span>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <span class="text-xs text-[#475569]">
                                        <span class="font-semibold text-amber-600">{{ $roomStat['occupied'] }}</span> / 
                                        <span class="font-semibold text-emerald-600">{{ $roomStat['available'] }}</span>
                                    </span>
                                    <span class="text-lg font-bold text-[#1e293b]">{{ $roomStat['total'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>

    {{-- Búsqueda y Filtros --}}
    <x-ui.card class="mb-6">
        <div class="p-5 border-b border-[#9fb0c3] bg-[#c3cfdb]">
            <form method="GET" class="space-y-4">
                {{-- Búsqueda --}}
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="form-label mb-2">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="form-input" placeholder="Buscar por nombre, número o sector...">
                    </div>
                    <div class="flex-1">
                        <label class="form-label mb-2">Ordenar por</label>
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Nombre</option>
                            <option value="number" {{ request('sort') === 'number' ? 'selected' : '' }}>Número</option>
                            <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Estado</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="form-label mb-2">Dirección</label>
                        <select name="direction" class="form-select">
                            <option value="asc" {{ request('direction', 'asc') === 'asc' ? 'selected' : '' }}>Ascendente</option>
                            <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>Descendente</option>
                        </select>
                    </div>
                </div>

                {{-- Filtros --}}
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1">
                        <label class="form-label mb-2">Sector / Pieza</label>
                        <select name="room" class="form-select">
                            <option value="">Todos</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room }}" {{ request('room') === $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="form-label mb-2">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                            <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Ocupada</option>
                            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Mantención</option>
                            <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Deshabilitada</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="form-label mb-2">Género</label>
                        <select name="gender" class="form-select">
                            <option value="">Todos</option>
                            <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Masculino</option>
                            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Femenino</option>
                            <option value="mixed" {{ request('gender') === 'mixed' ? 'selected' : '' }}>Mixto</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <x-ui.button type="submit" variant="primary" size="md">Aplicar</x-ui.button>
                        <x-ui.button variant="secondary" size="md" href="{{ route('admin.beds.index') }}">Limpiar</x-ui.button>
                    </div>
                </div>
            </form>
        </div>
    </x-ui.card>

    {{-- Grid de Camas --}}
    @if($beds->isEmpty())
        <x-ui.card>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-[#c3cfdb] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bed text-[#475569] text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1e293b] mb-2">No hay camas registradas</h3>
                <p class="text-[#475569] mb-6">Comienza creando tu primera cama para gestionar los espacios de descanso.</p>
                <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.beds.create') }}">
                    Crear primera cama
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        @php
            $bedsByRoom = $beds->groupBy('room');
        @endphp

        @foreach($bedsByRoom as $room => $roomBeds)
            {{-- Header de Sector Profesional --}}
            <div class="mb-6">
                <x-ui.card class="!p-4 !mb-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-200 dark:bg-blue-800 rounded-xl flex items-center justify-center">
                                <i class="fas fa-door-open text-blue-700 dark:text-blue-300 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#1e293b] dark:text-white">
                                    {{ $room ?: 'Sin sector asignado' }}
                                </h3>
                                <p class="text-xs text-[#475569] dark:text-slate-400">
                                    {{ $roomBeds->count() }} {{ $roomBeds->count() === 1 ? 'cama' : 'camas' }}
                                    @if($roomBeds->where('status', 'occupied')->count() > 0)
                                        · <span class="font-semibold text-red-600 dark:text-red-400">{{ $roomBeds->where('status', 'occupied')->count() }} ocupada(s)</span>
                                    @endif
                                    @if($roomBeds->where('status', 'available')->count() > 0)
                                        · <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $roomBeds->where('status', 'available')->count() }} disponible(s)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Grid Uniforme de Camas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($roomBeds as $bed)
                        @php
                            // Clases de borde según estado
                            $borderClass = match($bed->status) {
                                'available' => 'border-l-4 border-emerald-500',
                                'occupied' => 'border-l-4 border-red-500',
                                'maintenance' => 'border-l-4 border-amber-500',
                                'disabled' => 'border-l-4 border-gray-400',
                                default => 'border-l-4 border-gray-300',
                            };
                            
                            // Animación pulse solo para ocupadas
                            $pulseClass = $bed->status === 'occupied' ? 'animate-pulse-slow' : '';
                            
                            // Fondo leve para ocupadas
                            $bgClass = $bed->status === 'occupied' ? 'bg-red-50/40' : '';
                        @endphp
                        
                        <x-ui.card class="hover:shadow-lg transition-all duration-200 {{ $borderClass }} {{ $bgClass }}">
                            <div class="p-4 flex flex-col h-full">
                                {{-- Header Compacto --}}
                                <div class="flex items-center justify-between mb-3 flex-shrink-0">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-11 h-11 rounded-xl {{ $bed->status === 'occupied' ? 'bg-red-100' : ($bed->status === 'available' ? 'bg-emerald-100' : 'bg-slate-100') }} flex items-center justify-center">
                                            <span class="text-lg font-bold {{ $bed->status === 'occupied' ? 'text-red-700' : ($bed->status === 'available' ? 'text-emerald-700' : 'text-slate-600') }}">{{ $bed->name }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-[#1e293b] truncate">{{ $bed->name }}</p>
                                            @if($bed->location)
                                                <p class="text-xs text-[#64748b] truncate"><i class="fas fa-map-marker-alt text-[9px] mr-1"></i>{{ $bed->location }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="px-2 py-1 rounded-lg text-xs font-bold flex-shrink-0
                                        {{ $bed->status === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $bed->status === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $bed->status === 'maintenance' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $bed->status === 'disabled' ? 'bg-gray-100 text-gray-600' : '' }}">
                                        {{ $bed->status_label }}
                                    </div>
                                </div>

                                {{-- Género Badge --}}
                                <div class="mb-3 flex-shrink-0">
                                    <x-ui.badge variant="{{ $bed->gender_color }}" size="sm">
                                        <i class="fas {{ $bed->gender === 'male' ? 'fa-mars' : ($bed->gender === 'female' ? 'fa-venus' : 'fa-venus-mars') }} mr-1"></i>
                                        {{ $bed->gender_label }}
                                    </x-ui.badge>
                                </div>

                                {{-- Contenido Flexible --}}
                                <div class="flex-grow flex flex-col">
                                    {{-- Ocupante Limpio --}}
                                    @if($bed->is_occupied && $bed->current_occupant_name)
                                        <div class="p-3 bg-red-50 rounded-xl border border-red-200">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-user text-red-700 text-sm"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Ocupante</p>
                                                    <p class="text-sm font-bold text-red-900 truncate">{{ $bed->current_occupant_name }}</p>
                                                </div>
                                            </div>
                                            @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                                <div class="text-xs text-red-700 bg-white rounded-lg px-2 py-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <span class="font-semibold">{{ $bed->currentAssignment->started_at->diffForHumans() }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($bed->notes)
                                        <div class="p-2.5 bg-blue-50 rounded-lg border border-blue-200">
                                            <p class="text-xs text-blue-800 line-clamp-2">
                                                <i class="fas fa-info-circle mr-1"></i>{{ $bed->notes }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Acciones Principales --}}
                                <div class="mt-auto pt-3 space-y-2 flex-shrink-0">
                                    @if($bed->is_occupied)
                                        <button type="button" onclick="openReleaseModal({{ $bed->id }}, '{{ $bed->name }}')"
                                            class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Liberar</span>
                                        </button>
                                    @elseif($bed->canBeAssigned())
                                        <button type="button" onclick="openAssignModal({{ $bed->id }}, '{{ $bed->name }}', '{{ $bed->gender }}')"
                                            class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                                            <i class="fas fa-user-plus"></i>
                                            <span>Asignar</span>
                                        </button>
                                    @else
                                        <div class="w-full px-3 py-2 bg-gray-100 text-gray-500 font-semibold rounded-lg text-sm text-center">
                                            <i class="fas fa-ban mr-1"></i>No asignable
                                        </div>
                                    @endif

                                    {{-- Control de Estado --}}
                                    @if($bed->status !== 'occupied')
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($bed->status === 'disabled')
                                            <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="status" value="available">
                                                <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition-all">
                                                    <i class="fas fa-check"></i> Reactivar
                                                </button>
                                            </form>
                                        @else
                                            @if($bed->status !== 'available')
                                            <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="status" value="available">
                                                <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition-all">
                                                    <i class="fas fa-check"></i> Disponible
                                                </button>
                                            </form>
                                            @endif
                                            @if($bed->status !== 'maintenance')
                                            <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="status" value="maintenance">
                                                <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 transition-all">
                                                    <i class="fas fa-tools"></i> Mantención
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="status" value="disabled">
                                                <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition-all">
                                                    <i class="fas fa-ban"></i> Deshabilitar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                {{-- Acciones Secundarias --}}
                                <div class="grid grid-cols-4 gap-1.5 pt-3 border-t border-[#cbd5e1] flex-shrink-0">
                                    <a href="{{ route('admin.beds.edit', $bed) }}" class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-slate-100 transition-all" title="Editar">
                                        <i class="fas fa-pen text-slate-600 text-sm mb-1"></i>
                                        <span class="text-[10px] font-semibold text-slate-600">Editar</span>
                                    </a>
                                    <a href="{{ route('admin.beds.history', $bed) }}" class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-slate-100 transition-all" title="Historial">
                                        <i class="fas fa-history text-slate-600 text-sm mb-1"></i>
                                        <span class="text-[10px] font-semibold text-slate-600">Historial</span>
                                    </a>
                                    <a href="{{ route('admin.beds.qr', $bed) }}" class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-slate-100 transition-all" title="Ver QR">
                                        <i class="fas fa-qrcode text-slate-600 text-sm mb-1"></i>
                                        <span class="text-[10px] font-semibold text-slate-600">QR</span>
                                    </a>
                                    <a href="{{ route('admin.beds.qr.print', $bed) }}" target="_blank" class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-slate-100 transition-all" title="Imprimir">
                                        <i class="fas fa-print text-slate-600 text-sm mb-1"></i>
                                        <span class="text-[10px] font-semibold text-slate-600">Imprimir</span>
                                    </a>
                                </div>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Modal Asignar Cama --}}
    <div id="assignModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-xl max-w-md w-full">
            <form id="assignForm" method="POST">
                @csrf
                <div class="p-6 border-b border-[#9fb0c3]">
                    <h3 class="text-lg font-bold text-[#1e293b]">Asignar Cama</h3>
                    <p class="text-sm text-[#475569]" id="assignBedName"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Voluntario <span class="text-red-500">*</span></label>
                        <select name="volunteer_id" required class="form-select" id="volunteerSelect">
                            <option value="">Seleccionar voluntario...</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Notas opcionales..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-[#9fb0c3] flex gap-3">
                    <button type="button" onclick="closeAssignModal()" class="flex-1 px-4 py-2 bg-[#c3cfdb] hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Liberar Cama --}}
    <div id="releaseModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-xl max-w-md w-full">
            <form id="releaseForm" method="POST">
                @csrf
                <div class="p-6 border-b border-[#9fb0c3]">
                    <h3 class="text-lg font-bold text-[#1e293b]">Liberar Cama</h3>
                    <p class="text-sm text-[#475569]" id="releaseBedName"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Notas opcionales sobre la liberación..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-[#9fb0c3] flex gap-3">
                    <button type="button" onclick="closeReleaseModal()" class="flex-1 px-4 py-2 bg-[#c3cfdb] hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors">
                        Liberar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allVolunteers = [];

// Cargar voluntarios al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("admin.beds.api.volunteers") }}')
        .then(response => response.json())
        .then(data => {
            allVolunteers = data;
        })
        .catch(error => console.error('Error cargando voluntarios:', error));
});

function openAssignModal(bedId, bedName, bedGender = null) {
    const modal = document.getElementById('assignModal');
    const form = document.getElementById('assignForm');
    const select = document.getElementById('volunteerSelect');
    
    // Configurar formulario
    form.action = `/admin/beds/${bedId}/assign`;
    document.getElementById('assignBedName').textContent = bedName;
    
    // Cargar voluntarios filtrados por género de la cama
    const url = bedGender ? `{{ route("admin.beds.api.volunteers") }}?gender=${bedGender}` : '{{ route("admin.beds.api.volunteers") }}';
    
    fetch(url)
        .then(response => response.json())
        .then(volunteers => {
            select.innerHTML = '<option value="">Seleccionar voluntario...</option>';
            
            if (volunteers.length === 0) {
                select.innerHTML = '<option value="">No hay voluntarios disponibles</option>';
                select.disabled = true;
            } else {
                select.disabled = false;
                volunteers.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.id;
                    
                    let label = v.name;
                    if (v.rut) label += ` (${v.rut})`;
                    if (v.cargo) label += ` - ${v.cargo}`;
                    if (v.has_active_bed) label += ' ⚠️ Ya tiene cama';
                    
                    option.textContent = label;
                    if (v.has_active_bed) {
                        option.style.color = '#d97706';
                        option.style.fontWeight = 'bold';
                    }
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error cargando voluntarios:', error);
            select.innerHTML = '<option value="">Error al cargar voluntarios</option>';
            select.disabled = true;
        });
    
    modal.classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignForm').reset();
}

function openReleaseModal(bedId, bedName) {
    const modal = document.getElementById('releaseModal');
    const form = document.getElementById('releaseForm');
    
    form.action = `/admin/beds/${bedId}/release`;
    document.getElementById('releaseBedName').textContent = bedName;
    
    modal.classList.remove('hidden');
}

function closeReleaseModal() {
    document.getElementById('releaseModal').classList.add('hidden');
    document.getElementById('releaseForm').reset();
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAssignModal();
        closeReleaseModal();
    }
});
</script>
@endsection
