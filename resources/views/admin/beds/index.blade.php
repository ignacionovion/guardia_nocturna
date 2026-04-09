@extends('layouts.modern')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="w-full">
    <x-ui.page-header title="Camas" subtitle="Gestión de camas y espacios de descanso" icon="fas fa-bed" iconVariant="primary">
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.beds.create') }}">
            Nueva cama
        </x-ui.button>
    </x-ui.page-header>

    {{-- Resumen operacional por sector --}}
    @php
        $sectorSummary = $beds
            ->groupBy(fn ($bed) => $bed->room ?: 'Sin sector asignado')
            ->map(function ($roomBeds, $roomName) {
                $total = $roomBeds->count();
                $occupied = $roomBeds->filter(fn ($bed) => $bed->is_occupied || $bed->status === 'occupied')->count();
                $maintenance = $roomBeds->where('status', 'maintenance')->count();
                $available = $roomBeds->where('status', 'available')->count();
                $disabled = $roomBeds->where('status', 'disabled')->count();

                $occupiedPct = $total > 0 ? round(($occupied / $total) * 100) : 0;
                $availablePct = $total > 0 ? round(($available / $total) * 100) : 0;
                $maintenancePct = $total > 0 ? round(($maintenance / $total) * 100) : 0;
                $disabledPct = max(0, 100 - ($occupiedPct + $availablePct + $maintenancePct));

                return [
                    'name' => $roomName,
                    'total' => $total,
                    'occupied' => $occupied,
                    'available' => $available,
                    'maintenance' => $maintenance,
                    'disabled' => $disabled,
                    'occupiedPct' => $occupiedPct,
                    'availablePct' => $availablePct,
                    'maintenancePct' => $maintenancePct,
                    'disabledPct' => $disabledPct,
                ];
            })
            ->sortByDesc('occupiedPct')
            ->values();

        $activeRoomFilter = request('room');
    @endphp

    <x-ui.card class="mb-6">
        <div class="p-5 border-b border-slate-200 bg-white flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-[#1e293b]">Estado por sector</h3>
                <p class="text-xs text-slate-500 mt-1">Vista rápida para detectar áreas críticas y filtrar el tablero.</p>
            </div>
            @if($activeRoomFilter)
                <x-ui.button variant="secondary" size="sm" href="{{ route('admin.beds.index', request()->except(['room', 'page'])) }}">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpiar sector
                </x-ui.button>
            @endif
        </div>
        <div class="p-5">
            @if($sectorSummary->isEmpty())
                <p class="text-sm text-slate-500 text-center py-6">No hay sectores disponibles para resumir.</p>
            @else
                <div class="overflow-x-auto pb-2">
                    <div class="flex gap-3 min-w-max">
                        @foreach($sectorSummary as $sector)
                            @php
                                $isActiveSector = $activeRoomFilter === $sector['name'];
                                $sectorFilterUrl = route('admin.beds.index', array_merge(request()->except(['room', 'page']), ['room' => $sector['name']]));
                            @endphp
                            <a
                                href="{{ $sectorFilterUrl }}"
                                class="w-[260px] rounded-xl border p-4 bg-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $isActiveSector ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-200' }}"
                            >
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">Sector</p>
                                        <h4 class="text-sm font-bold text-slate-900 truncate">{{ $sector['name'] }}</h4>
                                    </div>
                                    <x-ui.badge variant="default" size="sm">
                                        {{ $sector['total'] }} camas
                                    </x-ui.badge>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-[11px] mb-3">
                                    <div class="rounded-lg bg-red-50 px-2 py-1.5">
                                        <p class="text-red-600 font-semibold">Ocup.</p>
                                        <p class="text-red-700 font-bold">{{ $sector['occupied'] }}</p>
                                    </div>
                                    <div class="rounded-lg bg-emerald-50 px-2 py-1.5">
                                        <p class="text-emerald-600 font-semibold">Disp.</p>
                                        <p class="text-emerald-700 font-bold">{{ $sector['available'] }}</p>
                                    </div>
                                    <div class="rounded-lg bg-amber-50 px-2 py-1.5">
                                        <p class="text-amber-600 font-semibold">Mant.</p>
                                        <p class="text-amber-700 font-bold">{{ $sector['maintenance'] }}</p>
                                    </div>
                                </div>

                                {{-- Barra segmentada operacional (sin inline styles) --}}
                                @php
                                    $totalForBar = max(1, $sector['total']);
                                    $occupiedUnits = (int) round(($sector['occupied'] / $totalForBar) * 12);
                                    $availableUnits = (int) round(($sector['available'] / $totalForBar) * 12);
                                    $maintenanceUnits = (int) round(($sector['maintenance'] / $totalForBar) * 12);

                                    $occupiedUnits = max(0, min(12, $occupiedUnits));
                                    $availableUnits = max(0, min(12 - $occupiedUnits, $availableUnits));
                                    $maintenanceUnits = max(0, min(12 - $occupiedUnits - $availableUnits, $maintenanceUnits));
                                    $disabledUnits = max(0, 12 - $occupiedUnits - $availableUnits - $maintenanceUnits);

                                    $colSpanMap = [
                                        0 => 'col-span-0',
                                        1 => 'col-span-1',
                                        2 => 'col-span-2',
                                        3 => 'col-span-3',
                                        4 => 'col-span-4',
                                        5 => 'col-span-5',
                                        6 => 'col-span-6',
                                        7 => 'col-span-7',
                                        8 => 'col-span-8',
                                        9 => 'col-span-9',
                                        10 => 'col-span-10',
                                        11 => 'col-span-11',
                                        12 => 'col-span-12',
                                    ];
                                @endphp
                                <div class="h-2 w-full rounded-full overflow-hidden bg-slate-100 grid grid-cols-12 gap-0.5">
                                    @if($occupiedUnits > 0)
                                        <div class="{{ $colSpanMap[$occupiedUnits] }} bg-red-400"></div>
                                    @endif
                                    @if($availableUnits > 0)
                                        <div class="{{ $colSpanMap[$availableUnits] }} bg-emerald-400"></div>
                                    @endif
                                    @if($maintenanceUnits > 0)
                                        <div class="{{ $colSpanMap[$maintenanceUnits] }} bg-amber-400"></div>
                                    @endif
                                    @if($disabledUnits > 0)
                                        <div class="{{ $colSpanMap[$disabledUnits] }} bg-slate-300"></div>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <p class="text-xs text-slate-500">
                                        Ocupación
                                    </p>
                                    <p class="text-sm font-bold {{ $sector['occupiedPct'] >= 80 ? 'text-red-700' : ($sector['occupiedPct'] >= 60 ? 'text-amber-700' : 'text-emerald-700') }}">
                                        {{ $sector['occupiedPct'] }}%
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Click en un sector para filtrar el grid de camas.</p>
            @endif
        </div>
    </x-ui.card>

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

    {{-- Búsqueda y Filtros --}}
    <x-ui.card class="mb-6">
        <div class="p-5 border-b border-slate-200 bg-white">
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
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
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

        <div
            x-data="{
                density: localStorage.getItem('bedsDensity') || 'standard',
                setDensity(d) {
                    this.density = d;
                    localStorage.setItem('bedsDensity', d);
                }
            }"
            class="space-y-6"
        >
            {{-- Densidad de vista: operación normal vs control room --}}
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800">Vista del tablero</p>
                    <p class="text-xs text-slate-500">Normal: más detalle por cama. Control room: más camas visibles y acciones en menú.</p>
                </div>
                <div class="inline-flex shrink-0 rounded-xl border border-slate-200 bg-slate-50 p-1">
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="density === 'standard' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        @click="setDensity('standard')"
                    >
                        Normal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="density === 'compact' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        @click="setDensity('compact')"
                    >
                        Control room
                    </button>
                </div>
            </div>

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

                {{-- Grid de camas: columnas según densidad (Normal vs Control room) --}}
                <div
                    class="grid gap-4"
                    :class="density === 'compact'
                        ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-2'
                        : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'"
                >
                    @foreach($roomBeds as $bed)
                        @php
                            $isOccupied = $bed->is_occupied || $bed->status === 'occupied';
                            $isAvailable = $bed->status === 'available' && !$isOccupied;
                            $isMaintenance = $bed->status === 'maintenance';
                            $isDisabled = $bed->status === 'disabled';

                            $statusVariant = $isAvailable ? 'success' : ($isOccupied ? 'danger' : ($isMaintenance ? 'warning' : 'default'));
                            $statusIcon = $isAvailable ? 'fas fa-circle-check' : ($isOccupied ? 'fas fa-user-clock' : ($isMaintenance ? 'fas fa-screwdriver-wrench' : 'fas fa-ban'));
                            $statusText = $isAvailable ? 'Disponible' : ($isOccupied ? 'Ocupada' : ($isMaintenance ? 'Mantención' : 'Deshabilitada'));

                            $accentClass = $isAvailable
                                ? 'border-emerald-200'
                                : ($isOccupied ? 'border-red-200' : ($isMaintenance ? 'border-amber-200' : 'border-slate-200'));
                            $indicatorClass = $isAvailable
                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                                : ($isOccupied
                                    ? 'bg-red-50 text-red-700 ring-red-100'
                                    : ($isMaintenance
                                        ? 'bg-amber-50 text-amber-700 ring-amber-100'
                                        : 'bg-slate-50 text-slate-700 ring-slate-100'));
                            $metaTextClass = $isAvailable
                                ? 'text-emerald-700'
                                : ($isOccupied ? 'text-red-700' : ($isMaintenance ? 'text-amber-700' : 'text-slate-700'));
                        @endphp

                        <x-ui.card padding="none" class="h-full overflow-visible border {{ $accentClass }} transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                            <div
                                class="relative flex h-full flex-col"
                                x-bind:class="density === 'compact' ? 'gap-2 p-3' : 'gap-4 p-4'"
                            >
                                {{-- HEADER: nombre + estado + menú secundario --}}
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span
                                                class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 font-bold text-slate-700"
                                                x-bind:class="density === 'compact' ? 'text-[10px]' : 'text-xs'"
                                            >
                                                #{{ $bed->number ?? $bed->id }}
                                            </span>
                                            <h4
                                                class="min-w-0 flex-1 truncate font-bold text-slate-900"
                                                x-bind:class="density === 'compact' ? 'text-sm' : 'text-base'"
                                            >
                                                {{ $bed->name }}
                                            </h4>
                                        </div>
                                        @if($bed->location)
                                            <p
                                                class="mt-0.5 truncate text-slate-500"
                                                x-bind:class="density === 'compact' ? 'text-[10px]' : 'text-xs'"
                                            >
                                                <i class="fas fa-location-dot mr-1 opacity-70"></i>{{ $bed->location }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 items-start gap-1.5">
                                        <x-ui.badge variant="{{ $statusVariant }}" size="sm" dot icon="{{ $statusIcon }}">
                                            {{ $statusText }}
                                        </x-ui.badge>

                                        <div class="relative" x-data="{ menuOpen: false }">
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                                                @click="menuOpen = !menuOpen"
                                                :aria-expanded="menuOpen"
                                                title="Más acciones"
                                            >
                                                <i class="fas fa-ellipsis-vertical text-sm"></i>
                                            </button>

                                            <div
                                                x-show="menuOpen"
                                                x-transition
                                                @click.outside="menuOpen = false"
                                                class="absolute right-0 z-40 mt-1 w-52 origin-top-right rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5"
                                                x-cloak
                                            >
                                                <a href="{{ route('admin.beds.qr', $bed) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-qrcode w-4 text-center text-slate-500"></i> Ver QR
                                                </a>
                                                <a href="{{ route('admin.beds.qr.print', $bed) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-print w-4 text-center text-slate-500"></i> Imprimir QR
                                                </a>
                                                <a href="{{ route('admin.beds.edit', $bed) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-pen w-4 text-center text-slate-500"></i> Editar
                                                </a>
                                                <a href="{{ route('admin.beds.history', $bed) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-history w-4 text-center text-slate-500"></i> Historial
                                                </a>

                                                <div class="my-1 border-t border-slate-100"></div>

                                                @if($bed->status === 'maintenance')
                                                    <form action="{{ route('admin.beds.status', $bed) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="available">
                                                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                            <i class="fas fa-check w-4 text-center"></i> Habilitar cama
                                                        </button>
                                                    </form>
                                                @elseif($bed->status === 'available')
                                                    <form action="{{ route('admin.beds.status', $bed) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="maintenance">
                                                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                                            <i class="fas fa-screwdriver-wrench w-4 text-center"></i> Pasar a mantención
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.beds.status', $bed) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="disabled">
                                                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-semibold text-red-700 hover:bg-red-50">
                                                            <i class="fas fa-ban w-4 text-center"></i> Deshabilitar cama
                                                        </button>
                                                    </form>
                                                @elseif($bed->status === 'disabled')
                                                    <form action="{{ route('admin.beds.status', $bed) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="available">
                                                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                                            <i class="fas fa-rotate-left w-4 text-center"></i> Reactivar cama
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- BODY: detalle (Normal) vs una línea (Control room) --}}
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50/60"
                                    x-bind:class="density === 'compact' ? 'px-2 py-2' : 'px-3 py-4'"
                                >
                                    <div x-show="density !== 'compact'" class="flex items-center gap-3" x-cloak>
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ring-4 {{ $indicatorClass }}">
                                            <i class="fas fa-bed text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            @if($isOccupied && $bed->current_occupant_name)
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Asignada a</p>
                                                <p class="text-sm font-bold {{ $metaTextClass }} truncate">{{ $bed->current_occupant_name }}</p>
                                                @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        <i class="fas fa-clock mr-1"></i>{{ $bed->currentAssignment->started_at->diffForHumans() }}
                                                    </p>
                                                @endif
                                            @elseif($isMaintenance)
                                                <p class="text-sm font-bold {{ $metaTextClass }}">En mantención</p>
                                                <p class="text-xs text-slate-500">No disponible para asignación</p>
                                            @elseif($isDisabled)
                                                <p class="text-sm font-bold {{ $metaTextClass }}">Deshabilitada</p>
                                                <p class="text-xs text-slate-500">Reactivar desde el menú</p>
                                            @else
                                                <p class="text-sm font-bold {{ $metaTextClass }}">Lista para asignar</p>
                                                <p class="text-xs text-slate-500">Sin ocupante actual</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div x-show="density === 'compact'" class="flex items-center gap-2" x-cloak>
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-2 {{ $indicatorClass }}">
                                            <i class="fas fa-bed text-sm"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            @if($isOccupied && $bed->current_occupant_name)
                                                <p class="truncate text-xs font-bold {{ $metaTextClass }}">{{ $bed->current_occupant_name }}</p>
                                                @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                                    <p class="truncate text-[10px] text-slate-500">{{ $bed->currentAssignment->started_at->diffForHumans() }}</p>
                                                @endif
                                            @elseif($isMaintenance)
                                                <p class="truncate text-xs font-bold {{ $metaTextClass }}">Mantención</p>
                                            @elseif($isDisabled)
                                                <p class="truncate text-xs font-bold {{ $metaTextClass }}">Fuera de servicio</p>
                                            @else
                                                <p class="truncate text-xs font-bold {{ $metaTextClass }}">Disponible</p>
                                            @endif
                                        </div>
                                        <x-ui.badge variant="{{ $bed->gender_color }}" size="xs" class="shrink-0">
                                            <i class="fas {{ $bed->gender === 'male' ? 'fa-mars' : ($bed->gender === 'female' ? 'fa-venus' : 'fa-venus-mars') }}"></i>
                                        </x-ui.badge>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2" x-show="density !== 'compact'">
                                    <x-ui.badge variant="{{ $bed->gender_color }}" size="sm">
                                        <i class="fas {{ $bed->gender === 'male' ? 'fa-mars' : ($bed->gender === 'female' ? 'fa-venus' : 'fa-venus-mars') }} mr-1"></i>
                                        {{ $bed->gender_label }}
                                    </x-ui.badge>
                                    @if($bed->notes)
                                        <span class="max-w-[55%] truncate text-right text-xs text-slate-500" title="{{ $bed->notes }}">
                                            <i class="fas fa-note-sticky mr-1"></i>{{ $bed->notes }}
                                        </span>
                                    @endif
                                </div>

                                @if($bed->notes)
                                    <div x-show="density === 'compact'" class="flex justify-end" x-cloak>
                                        <span class="text-[10px] text-slate-400" title="{{ $bed->notes }}">
                                            <i class="fas fa-note-sticky"></i>
                                        </span>
                                    </div>
                                @endif

                                {{-- FOOTER: solo CTA principal --}}
                                <div class="mt-auto pt-1">
                                    @if($isOccupied)
                                        <div x-show="density !== 'compact'" x-cloak>
                                            <x-ui.button
                                                type="button"
                                                variant="success"
                                                size="md"
                                                icon="fas fa-bed-pulse"
                                                onclick="openReleaseModal({{ $bed->id }}, @json($bed->name))"
                                                class="w-full justify-center"
                                            >
                                                Liberar
                                            </x-ui.button>
                                        </div>
                                        <div x-show="density === 'compact'" x-cloak>
                                            <x-ui.button
                                                type="button"
                                                variant="success"
                                                size="sm"
                                                icon="fas fa-bed-pulse"
                                                onclick="openReleaseModal({{ $bed->id }}, @json($bed->name))"
                                                class="w-full justify-center"
                                            >
                                                Liberar
                                            </x-ui.button>
                                        </div>
                                    @elseif($bed->canBeAssigned())
                                        <div x-show="density !== 'compact'" x-cloak>
                                            <x-ui.button
                                                type="button"
                                                variant="primary"
                                                size="md"
                                                icon="fas fa-user-plus"
                                                onclick="openAssignModal({{ $bed->id }}, @json($bed->name), @json($bed->gender))"
                                                class="w-full justify-center shadow-sm hover:shadow-md"
                                            >
                                                Asignar
                                            </x-ui.button>
                                        </div>
                                        <div x-show="density === 'compact'" x-cloak>
                                            <x-ui.button
                                                type="button"
                                                variant="primary"
                                                size="sm"
                                                icon="fas fa-user-plus"
                                                onclick="openAssignModal({{ $bed->id }}, @json($bed->name), @json($bed->gender))"
                                                class="w-full justify-center shadow-sm hover:shadow-md"
                                            >
                                                Asignar
                                            </x-ui.button>
                                        </div>
                                    @else
                                        <div class="w-full rounded-xl bg-slate-100 px-3 py-2 text-center text-xs font-semibold text-slate-500">
                                            <i class="fas fa-ban mr-1"></i>No asignable
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        @endforeach
        </div>
    @endif

    {{-- Modal Asignar Cama --}}
    <div id="assignModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xl max-w-md w-full">
            <form id="assignForm" method="POST">
                @csrf
                <div class="p-6 border-b border-slate-200">
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
                <div class="p-6 border-t border-slate-200 flex gap-3">
                    <button type="button" onclick="closeAssignModal()" class="flex-1 px-4 py-2 bg-white hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
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
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xl max-w-md w-full">
            <form id="releaseForm" method="POST">
                @csrf
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-[#1e293b]">Liberar Cama</h3>
                    <p class="text-sm text-[#475569]" id="releaseBedName"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Notas opcionales sobre la liberación..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex gap-3">
                    <button type="button" onclick="closeReleaseModal()" class="flex-1 px-4 py-2 bg-white hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
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
