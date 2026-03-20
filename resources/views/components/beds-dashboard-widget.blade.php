@php
    $totalBeds = \App\Models\Bed::count();
    $occupiedBeds = \App\Models\Bed::where('status', 'occupied')->count();
    $availableBeds = \App\Models\Bed::where('status', 'available')->count();
    $maintenanceBeds = \App\Models\Bed::where('status', 'maintenance')->count();
    $disabledBeds = \App\Models\Bed::where('status', 'disabled')->count();
    
    $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;
@endphp

<div class="bg-[#dde6ef] rounded-2xl border border-[#9fb0c3] shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="p-4 bg-[#c3cfdb] border-b border-[#9fb0c3]">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#e7eef5] rounded-xl flex items-center justify-center">
                    <i class="fas fa-bed text-[#1e293b] text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#1e293b]">Estado de Camas</h3>
                    <p class="text-xs text-[#475569]">Ocupación: {{ $occupancyRate }}%</p>
                </div>
            </div>
            <a href="{{ route('admin.beds.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Ver todas →
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="p-4">
        <div class="grid grid-cols-2 gap-3">
            {{-- Total --}}
            <div class="bg-[#e7eef5] rounded-xl border border-[#9fb0c3] p-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-bed text-[#475569] text-xs"></i>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider">Total</p>
                </div>
                <p class="text-2xl font-bold text-[#1e293b]">{{ $totalBeds }}</p>
            </div>

            {{-- Ocupadas --}}
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-user text-amber-600 text-xs"></i>
                    <p class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Ocupadas</p>
                </div>
                <p class="text-2xl font-bold text-amber-900">{{ $occupiedBeds }}</p>
            </div>

            {{-- Disponibles --}}
            <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-check text-emerald-600 text-xs"></i>
                    <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Disponibles</p>
                </div>
                <p class="text-2xl font-bold text-emerald-900">{{ $availableBeds }}</p>
            </div>

            {{-- Mantención --}}
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-tools text-blue-600 text-xs"></i>
                    <p class="text-xs font-semibold text-blue-800 uppercase tracking-wider">Mantención</p>
                </div>
                <p class="text-2xl font-bold text-blue-900">{{ $maintenanceBeds }}</p>
            </div>
        </div>

        @if($disabledBeds > 0)
            <div class="mt-3 bg-red-50 rounded-xl border border-red-200 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-ban text-red-600 text-xs"></i>
                    <p class="text-xs font-semibold text-red-800 uppercase tracking-wider">Deshabilitadas</p>
                </div>
                <p class="text-2xl font-bold text-red-900">{{ $disabledBeds }}</p>
            </div>
        @endif
    </div>

    {{-- Progress Bar --}}
    <div class="px-4 pb-4">
        <div class="bg-[#e7eef5] rounded-full h-2 overflow-hidden border border-[#9fb0c3]">
            <div class="bg-amber-500 h-full transition-all duration-300" style="width: {{ $occupancyRate }}%"></div>
        </div>
        <p class="text-xs text-[#475569] text-center mt-2">
            {{ $occupiedBeds }} de {{ $totalBeds }} camas ocupadas
        </p>
    </div>

    {{-- Quick Actions --}}
    <div class="p-4 bg-[#c3cfdb] border-t border-[#9fb0c3]">
        <div class="flex gap-2">
            <a href="{{ route('admin.beds.create') }}" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors text-center">
                <i class="fas fa-plus mr-1"></i> Nueva Cama
            </a>
            <a href="{{ route('admin.beds.index') }}" class="flex-1 px-3 py-2 bg-[#e7eef5] hover:bg-[#c3cfdb] text-[#1e293b] text-xs font-semibold rounded-lg transition-colors text-center border border-[#9fb0c3]">
                <i class="fas fa-list mr-1"></i> Gestionar
            </a>
        </div>
    </div>
</div>
