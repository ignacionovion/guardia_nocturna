<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bed->name }} - Información</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#dde6ef] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        {{-- Header --}}
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-[#c3cfdb] rounded-full flex items-center justify-center">
                    <i class="fas fa-bed text-[#1e293b] text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-[#1e293b] mb-1">{{ $bed->name }}</h1>
                    @if($bed->location)
                        <p class="text-sm text-[#475569] flex items-center gap-1">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $bed->location }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Badges --}}
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider
                    {{ $bed->status === 'available' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                    {{ $bed->status === 'occupied' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                    {{ $bed->status === 'maintenance' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                    {{ $bed->status === 'disabled' ? 'bg-red-100 text-red-700 border border-red-200' : '' }}">
                    {{ $bed->status_label }}
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider
                    {{ $bed->gender === 'male' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                    {{ $bed->gender === 'female' ? 'bg-pink-100 text-pink-700 border border-pink-200' : '' }}
                    {{ $bed->gender === 'mixed' ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}">
                    {{ $bed->gender_label }}
                </span>
            </div>
        </div>

        {{-- Información Detallada --}}
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-[#1e293b] mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-[#475569]"></i>
                Información de la Cama
            </h2>

            <div class="space-y-4">
                <div class="p-4 bg-[#dde6ef] rounded-xl border border-[#9fb0c3]">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Nombre</p>
                    <p class="text-base font-bold text-[#1e293b]">{{ $bed->name }}</p>
                </div>

                @if($bed->location)
                <div class="p-4 bg-[#dde6ef] rounded-xl border border-[#9fb0c3]">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Ubicación</p>
                    <p class="text-base font-bold text-[#1e293b]">{{ $bed->location }}</p>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-[#dde6ef] rounded-xl border border-[#9fb0c3]">
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Género</p>
                        <p class="text-base font-bold text-[#1e293b]">{{ $bed->gender_label }}</p>
                    </div>
                    <div class="p-4 bg-[#dde6ef] rounded-xl border border-[#9fb0c3]">
                        <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Estado</p>
                        <p class="text-base font-bold text-[#1e293b]">{{ $bed->status_label }}</p>
                    </div>
                </div>

                @if($bed->notes)
                <div class="p-4 bg-[#dde6ef] rounded-xl border border-[#9fb0c3]">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-2">Observaciones</p>
                    <p class="text-sm text-[#1e293b] leading-relaxed">{{ $bed->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Estado Actual --}}
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-lg p-6">
            <div class="text-center">
                @if($bed->status === 'available')
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-emerald-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-700 mb-2">Cama Disponible</h3>
                    <p class="text-sm text-[#475569]">Esta cama está lista para ser asignada</p>
                    
                    {{-- Última ocupación si existe --}}
                    @php
                        $lastAssignment = $bed->assignments()->completed()->latest('ended_at')->first();
                    @endphp
                    @if($lastAssignment)
                        <div class="mt-4 p-3 bg-[#dde6ef] rounded-lg border border-[#9fb0c3]">
                            <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Última ocupación</p>
                            <p class="text-sm font-bold text-[#1e293b]">
                                {{ $lastAssignment->volunteer ? trim($lastAssignment->volunteer->nombres . ' ' . $lastAssignment->volunteer->apellido_paterno) : 'N/A' }}
                            </p>
                            <p class="text-xs text-[#475569] mt-1">
                                Liberada {{ $lastAssignment->ended_at->diffForHumans() }}
                            </p>
                        </div>
                    @endif
                @elseif($bed->status === 'occupied')
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-amber-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-amber-700 mb-2">Cama Ocupada</h3>
                    
                    {{-- Mostrar ocupante actual --}}
                    @if($bed->is_occupied && $bed->current_occupant_name)
                        <div class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                            <p class="text-xs font-semibold text-amber-800 uppercase tracking-wider mb-2">Ocupada por</p>
                            <p class="text-lg font-bold text-amber-900">{{ $bed->current_occupant_name }}</p>
                            @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                <div class="mt-3 space-y-1">
                                    <p class="text-sm text-amber-700">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Desde {{ $bed->currentAssignment->started_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="text-sm text-amber-700">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $bed->currentAssignment->started_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                            @if($bed->currentAssignment && $bed->currentAssignment->notes)
                                <div class="mt-3 pt-3 border-t border-amber-200">
                                    <p class="text-xs font-semibold text-amber-800 uppercase tracking-wider mb-1">Observaciones</p>
                                    <p class="text-sm text-amber-900">{{ $bed->currentAssignment->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-[#475569]">Esta cama está actualmente en uso</p>
                    @endif
                @elseif($bed->status === 'maintenance')
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tools text-blue-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-700 mb-2">En Mantención</h3>
                    <p class="text-sm text-[#475569]">Esta cama está temporalmente fuera de servicio</p>
                @else
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ban text-red-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-700 mb-2">Cama Deshabilitada</h3>
                    <p class="text-sm text-[#475569]">Esta cama no está disponible para uso</p>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center">
            <p class="text-xs text-[#475569]">
                Información actualizada en tiempo real<br>
                Sistema de Gestión EstacionAPP
            </p>
        </div>
    </div>
</body>
</html>
