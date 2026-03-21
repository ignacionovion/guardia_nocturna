<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bed->name }} - Información</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0b1220] flex items-center justify-center px-4 py-8 overflow-x-hidden">
    <div class="w-full max-w-md mx-auto">
        {{-- Header --}}
        <div class="bg-[#111827] rounded-2xl shadow-xl p-6 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-bed text-white text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">{{ $bed->name }}</h1>
                    @if($bed->location)
                        <p class="text-sm text-gray-400 flex items-center gap-1">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                            {{ $bed->location }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Badges --}}
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider
                    {{ $bed->status === 'available' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                    {{ $bed->status === 'occupied' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}
                    {{ $bed->status === 'maintenance' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                    {{ $bed->status === 'disabled' ? 'bg-gray-500/20 text-gray-400 border border-gray-500/30' : '' }}">
                    {{ $bed->status_label }}
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider
                    {{ $bed->gender === 'male' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                    {{ $bed->gender === 'female' ? 'bg-pink-500/20 text-pink-400 border border-pink-500/30' : '' }}
                    {{ $bed->gender === 'mixed' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : '' }}">
                    {{ $bed->gender_label }}
                </span>
            </div>
        </div>

        {{-- Información Detallada --}}
        <div class="bg-[#111827] rounded-2xl shadow-xl p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-gray-400"></i>
                Información de la Cama
            </h2>

            <div class="space-y-4">
                <div class="w-full p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Nombre</p>
                    <p class="text-base font-bold text-white">{{ $bed->name }}</p>
                </div>

                @if($bed->location)
                <div class="w-full p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Ubicación</p>
                    <p class="text-base font-bold text-white">{{ $bed->location }}</p>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div class="w-full p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Género</p>
                        <p class="text-sm md:text-base font-bold text-white">{{ $bed->gender_label }}</p>
                    </div>
                    <div class="w-full p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Estado</p>
                        <p class="text-sm md:text-base font-bold text-white">{{ $bed->status_label }}</p>
                    </div>
                </div>

                @if($bed->notes)
                <div class="w-full p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Observaciones</p>
                    <p class="text-sm text-gray-300 leading-relaxed">{{ $bed->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Estado Actual --}}
        <div class="bg-[#111827] rounded-2xl shadow-xl p-6">
            <div class="text-center">
                @if($bed->status === 'available')
                    <div class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-emerald-500/30">
                        <i class="fas fa-check text-emerald-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-emerald-400 mb-2">Cama Disponible</h3>
                    <p class="text-sm text-gray-400">Esta cama está lista para ser asignada</p>
                    
                    {{-- Última ocupación si existe --}}
                    @php
                        $lastAssignment = $bed->assignments()->completed()->latest('ended_at')->first();
                    @endphp
                    @if($lastAssignment)
                        <div class="w-full mt-4 p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Última ocupación</p>
                            <p class="text-sm font-bold text-white">
                                {{ $lastAssignment->volunteer ? trim($lastAssignment->volunteer->nombres . ' ' . $lastAssignment->volunteer->apellido_paterno) : 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Liberada {{ $lastAssignment->ended_at->diffForHumans() }}
                            </p>
                        </div>
                    @endif
                @elseif($bed->status === 'occupied')
                    <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-red-500/30">
                        <i class="fas fa-user text-red-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-red-400 mb-2">Cama Ocupada</h3>
                    
                    {{-- Mostrar ocupante actual --}}
                    @if($bed->is_occupied && $bed->current_occupant_name)
                        <div class="w-full mt-4 p-4 bg-red-500/10 rounded-lg border border-red-500/30">
                            <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-2">Ocupada por</p>
                            <p class="text-base md:text-lg font-bold text-white">{{ $bed->current_occupant_name }}</p>
                            @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                <div class="mt-3 space-y-2">
                                    <p class="text-sm text-gray-300">
                                        <i class="fas fa-calendar-alt mr-1 text-red-400"></i>
                                        Desde {{ $bed->currentAssignment->started_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="text-sm text-gray-300">
                                        <i class="fas fa-clock mr-1 text-red-400"></i>
                                        {{ $bed->currentAssignment->started_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                            @if($bed->currentAssignment && $bed->currentAssignment->notes)
                                <div class="w-full mt-3 pt-3 border-t border-red-500/30">
                                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-1">Observaciones</p>
                                    <p class="text-sm text-gray-300">{{ $bed->currentAssignment->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-400">Esta cama está actualmente en uso</p>
                    @endif
                @elseif($bed->status === 'maintenance')
                    <div class="w-20 h-20 bg-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-amber-500/30">
                        <i class="fas fa-tools text-amber-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-amber-400 mb-2">En Mantención</h3>
                    <p class="text-sm text-gray-400">Esta cama está temporalmente fuera de servicio</p>
                @else
                    <div class="w-20 h-20 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-gray-500/30">
                        <i class="fas fa-ban text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-400 mb-2">Cama Deshabilitada</h3>
                    <p class="text-sm text-gray-500">Esta cama no está disponible para uso</p>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                Información actualizada en tiempo real<br>
                Sistema de Gestión EstacionAPP
            </p>
        </div>
    </div>
</body>
</html>
