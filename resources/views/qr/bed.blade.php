@extends('layouts.qr')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        
        {{-- Card principal --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            
            {{-- Header con icono --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-8 text-center">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bed text-white text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ $bed->name }}</h1>
                @if($bed->location)
                    <p class="text-blue-100 text-sm">
                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $bed->location }}
                    </p>
                @endif
            </div>

            {{-- Contenido --}}
            <div class="p-6 space-y-6">
                
                {{-- Estado --}}
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Estado</p>
                    <div class="inline-flex items-center justify-center px-6 py-3 rounded-full text-base font-bold
                        {{ $bed->status === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $bed->status === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $bed->status === 'maintenance' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $bed->status === 'disabled' ? 'bg-gray-100 text-gray-700' : '' }}">
                        {{ $bed->status_label }}
                    </div>
                </div>

                {{-- Género --}}
                <div class="text-center pb-6 border-b border-gray-200">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Género</p>
                    <p class="text-lg font-bold
                        {{ $bed->gender === 'male' ? 'text-blue-600' : '' }}
                        {{ $bed->gender === 'female' ? 'text-pink-600' : '' }}
                        {{ $bed->gender === 'mixed' ? 'text-purple-600' : '' }}">
                        {{ $bed->gender_label }}
                    </p>
                </div>

                {{-- Info según estado --}}
                @if($bed->status === 'available')
                    <div class="text-center py-8">
                        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-emerald-600 text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-emerald-700 mb-2">Disponible</h3>
                        <p class="text-gray-600">Lista para asignar</p>
                    </div>

                @elseif($bed->status === 'occupied')
                    <div class="bg-red-50 rounded-2xl p-6">
                        <div class="text-center mb-4">
                            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-red-600 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-red-700 mb-1">Ocupada</h3>
                        </div>
                        
                        @if($bed->is_occupied && $bed->current_occupant_name)
                            <div class="text-center">
                                <p class="text-xs text-red-600 uppercase tracking-wider mb-2">Ocupada por</p>
                                <p class="text-2xl font-bold text-gray-900 mb-3">{{ $bed->current_occupant_name }}</p>
                                
                                @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                    <div class="bg-white rounded-xl p-3">
                                        <p class="text-sm text-gray-700">
                                            <i class="fas fa-clock mr-1 text-red-600"></i>
                                            {{ $bed->currentAssignment->started_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                @elseif($bed->status === 'maintenance')
                    <div class="text-center py-8">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-tools text-amber-600 text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-amber-700 mb-2">En Mantención</h3>
                        <p class="text-gray-600">Fuera de servicio</p>
                    </div>

                @else
                    <div class="text-center py-8">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-ban text-gray-600 text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">Deshabilitada</h3>
                        <p class="text-gray-600">No disponible</p>
                    </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 text-center border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    EstacionAPP · Información en tiempo real
                </p>
            </div>

        </div>

    </div>
</div>
@endsection
