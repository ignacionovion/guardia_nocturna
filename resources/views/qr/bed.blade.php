@extends('layouts.qr')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        
        <div class="bg-[#111827] rounded-2xl shadow-xl p-6 space-y-6">
            
            {{-- Header --}}
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-bed text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">{{ $bed->name }}</h1>
                @if($bed->location)
                    <p class="text-sm text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $bed->location }}
                    </p>
                @endif
            </div>

            {{-- Estado Badge --}}
            <div class="flex justify-center gap-2">
                <span class="px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wider
                    {{ $bed->status === 'available' ? 'bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/40' : '' }}
                    {{ $bed->status === 'occupied' ? 'bg-red-500/20 text-red-400 border-2 border-red-500/40' : '' }}
                    {{ $bed->status === 'maintenance' ? 'bg-amber-500/20 text-amber-400 border-2 border-amber-500/40' : '' }}
                    {{ $bed->status === 'disabled' ? 'bg-gray-500/20 text-gray-400 border-2 border-gray-500/40' : '' }}">
                    {{ $bed->status_label }}
                </span>
            </div>

            {{-- Género --}}
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Género</p>
                <p class="text-base font-semibold
                    {{ $bed->gender === 'male' ? 'text-blue-400' : '' }}
                    {{ $bed->gender === 'female' ? 'text-pink-400' : '' }}
                    {{ $bed->gender === 'mixed' ? 'text-purple-400' : '' }}">
                    {{ $bed->gender_label }}
                </p>
            </div>

            {{-- Información según estado --}}
            @if($bed->status === 'available')
                <div class="text-center p-6 bg-emerald-500/10 rounded-xl border-2 border-emerald-500/30">
                    <i class="fas fa-check-circle text-emerald-400 text-4xl mb-3"></i>
                    <h3 class="text-lg font-bold text-emerald-400 mb-2">Disponible</h3>
                    <p class="text-sm text-gray-400">Esta cama está lista para ser asignada</p>
                </div>

            @elseif($bed->status === 'occupied')
                <div class="p-6 bg-red-500/10 rounded-xl border-2 border-red-500/30">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle text-red-400 text-4xl mb-3"></i>
                        <h3 class="text-lg font-bold text-red-400 mb-1">Ocupada</h3>
                    </div>
                    
                    @if($bed->is_occupied && $bed->current_occupant_name)
                        <div class="text-center">
                            <p class="text-xs text-red-400 uppercase tracking-wider mb-2">Ocupada por</p>
                            <p class="text-xl font-bold text-white mb-3">{{ $bed->current_occupant_name }}</p>
                            
                            @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                <div class="space-y-1 text-sm text-gray-300">
                                    <p>
                                        <i class="fas fa-clock mr-1 text-red-400"></i>
                                        {{ $bed->currentAssignment->started_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            @elseif($bed->status === 'maintenance')
                <div class="text-center p-6 bg-amber-500/10 rounded-xl border-2 border-amber-500/30">
                    <i class="fas fa-tools text-amber-400 text-4xl mb-3"></i>
                    <h3 class="text-lg font-bold text-amber-400 mb-2">En Mantención</h3>
                    <p class="text-sm text-gray-400">Temporalmente fuera de servicio</p>
                </div>

            @else
                <div class="text-center p-6 bg-gray-500/10 rounded-xl border-2 border-gray-500/30">
                    <i class="fas fa-ban text-gray-400 text-4xl mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-400 mb-2">Deshabilitada</h3>
                    <p class="text-sm text-gray-500">No disponible para uso</p>
                </div>
            @endif

            {{-- Footer --}}
            <div class="text-center pt-4 border-t border-gray-700">
                <p class="text-xs text-gray-500">
                    EstacionAPP · Información en tiempo real
                </p>
            </div>

        </div>

    </div>
</div>
@endsection
