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

    {{-- Estadísticas y Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total de Camas --}}
        <x-ui.card>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Total Camas</p>
                        <p class="text-3xl font-bold text-[#1e293b]">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bed text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Disponibles --}}
        <x-ui.card>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Disponibles</p>
                        <p class="text-3xl font-bold text-emerald-600">{{ $stats['available'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Ocupadas --}}
        <x-ui.card>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Ocupadas</p>
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['occupied'] }}</p>
                        <p class="text-xs text-[#475569] mt-1">{{ $stats['occupancy_percentage'] }}% ocupación</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Mantención + Deshabilitadas --}}
        <x-ui.card>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Fuera de Servicio</p>
                        <p class="text-3xl font-bold text-red-600">{{ $stats['maintenance'] + $stats['disabled'] }}</p>
                        <p class="text-xs text-[#475569] mt-1">
                            {{ $stats['maintenance'] }} mantención · {{ $stats['disabled'] }} deshabilitadas
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tools text-red-600 text-xl"></i>
                    </div>
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
            {{-- Header de Sector/Pieza --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-door-open text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#1e293b]">
                                {{ $room ?: 'Sin sector asignado' }}
                            </h3>
                            <p class="text-sm text-[#475569]">
                                {{ $roomBeds->count() }} {{ $roomBeds->count() === 1 ? 'cama' : 'camas' }}
                                @if($roomBeds->where('status', 'occupied')->count() > 0)
                                    · {{ $roomBeds->where('status', 'occupied')->count() }} ocupada(s)
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Grid de Camas del Sector --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
                        @endphp
                        
                        <x-ui.card class="hover:shadow-xl transition-all duration-300 {{ $borderClass }} {{ $pulseClass }}">
                            <div class="p-5">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-[#1e293b] mb-1">{{ $bed->name }}</h3>
                                        @if($bed->room)
                                            <p class="text-xs text-[#64748b] flex items-center gap-1">
                                                <i class="fas fa-door-open text-[10px]"></i>
                                                {{ $bed->room }}
                                            </p>
                                        @endif
                                        @if($bed->location)
                                            <p class="text-xs text-[#94a3b8] flex items-center gap-1 mt-0.5">
                                                <i class="fas fa-map-marker-alt text-[10px]"></i>
                                                {{ $bed->location }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-bed text-blue-700 text-lg"></i>
                                    </div>
                                </div>

                                {{-- Estado Badge --}}
                                <div class="mb-4">
                                    <x-ui.badge variant="{{ $bed->status_color }}" size="md" class="font-semibold">
                                        {{ $bed->status_label }}
                                    </x-ui.badge>
                                    <x-ui.badge variant="{{ $bed->gender_color }}" size="sm" class="ml-2">
                                        {{ $bed->gender_label }}
                                    </x-ui.badge>
                                </div>

                                {{-- Ocupante Actual (MEJORADO) --}}
                                @if($bed->is_occupied && $bed->current_occupant_name)
                                    <div class="mb-4 p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border-2 border-red-200 shadow-sm">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="w-10 h-10 bg-red-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-red-700"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Ocupada por</p>
                                                <p class="text-base font-bold text-red-900">{{ $bed->current_occupant_name }}</p>
                                            </div>
                                        </div>
                                        @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                            <div class="flex items-center gap-2 text-xs text-red-700 bg-red-50 rounded-lg px-3 py-2 mt-2">
                                                <i class="fas fa-clock"></i>
                                                <span class="font-semibold">{{ $bed->currentAssignment->started_at->diffForHumans() }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($bed->notes)
                                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <p class="text-xs text-blue-800 line-clamp-2">
                                            <i class="fas fa-info-circle mr-1"></i>{{ $bed->notes }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Acciones Principales (MEJORADAS) --}}
                                <div class="space-y-2 mb-4">
                                    @if($bed->is_occupied)
                                        <button type="button" onclick="openReleaseModal({{ $bed->id }}, '{{ $bed->name }}')" 
                                            class="w-full px-4 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                                            <i class="fas fa-check-circle text-lg"></i>
                                            <span>Liberar cama</span>
                                        </button>
                                    @elseif($bed->canBeAssigned())
                                        <button type="button" onclick="openAssignModal({{ $bed->id }}, '{{ $bed->name }}', '{{ $bed->gender }}')" 
                                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                                            <i class="fas fa-user-plus text-lg"></i>
                                            <span>Asignar cama</span>
                                        </button>
                                    @else
                                        <div class="w-full px-4 py-3 bg-gray-100 text-gray-500 font-semibold rounded-xl text-sm text-center border-2 border-gray-200">
                                            <i class="fas fa-ban mr-2"></i>
                                            No asignable
                                        </div>
                                    @endif
                                </div>

                                {{-- Control de Estado (COMPACTO) --}}
                                @if($bed->status !== 'occupied')
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @if($bed->status === 'disabled')
                                        <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1 min-w-[100px]">
                                            @csrf
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border-2 border-emerald-300 transition-all">
                                                <i class="fas fa-check"></i> Reactivar
                                            </button>
                                        </form>
                                    @else
                                        @if($bed->status !== 'available')
                                        <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1 min-w-[90px]">
                                            @csrf
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border-2 border-emerald-300 transition-all">
                                                <i class="fas fa-check"></i> Disponible
                                            </button>
                                        </form>
                                        @endif
                                        @if($bed->status !== 'maintenance')
                                        <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1 min-w-[90px]">
                                            @csrf
                                            <input type="hidden" name="status" value="maintenance">
                                            <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 border-2 border-amber-300 transition-all">
                                                <i class="fas fa-tools"></i> Mantención
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1 min-w-[90px]">
                                            @csrf
                                            <input type="hidden" name="status" value="disabled">
                                            <button type="submit" class="w-full px-2 py-1.5 text-xs font-bold rounded-lg bg-red-100 hover:bg-red-200 text-red-800 border-2 border-red-300 transition-all">
                                                <i class="fas fa-ban"></i> Deshabilitar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @endif

                                {{-- Acciones Secundarias (MEJORADAS) --}}
                                <div class="flex gap-2 pt-4 border-t-2 border-[#cbd5e1]">
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-history" href="{{ route('admin.beds.history', $bed) }}" title="Historial" class="flex-1" />
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-pen" href="{{ route('admin.beds.edit', $bed) }}" title="Editar" class="flex-1" />
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-qrcode" href="{{ route('admin.beds.qr', $bed) }}" title="Ver QR" class="flex-1" />
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-print" href="{{ route('admin.beds.qr.print', $bed) }}" title="Imprimir" target="_blank" class="flex-1" />
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
