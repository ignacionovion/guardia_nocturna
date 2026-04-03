@php
    $totalBeds = \App\Models\Bed::count();
    $occupiedBeds = \App\Models\Bed::where('status', 'occupied')->count();
    $availableBeds = \App\Models\Bed::where('status', 'available')->count();
    $maintenanceBeds = \App\Models\Bed::where('status', 'maintenance')->count();
    $disabledBeds = \App\Models\Bed::where('status', 'disabled')->count();
    
    $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bed text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Estado de Camas</h3>
                    <p class="text-xs text-slate-500">Ocupación: {{ $occupancyRate }}%</p>
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
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $totalBeds }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center">
                        <i class="fas fa-bed text-slate-600"></i>
                    </div>
                </div>
            </div>

            {{-- Ocupadas --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ocupadas</p>
                        <p class="mt-1 text-xl font-black text-amber-600">{{ $occupiedBeds }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                        <i class="fas fa-user text-amber-600"></i>
                    </div>
                </div>
            </div>

            {{-- Disponibles --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Disponibles</p>
                        <p class="mt-1 text-xl font-black text-emerald-600">{{ $availableBeds }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-check text-emerald-600"></i>
                    </div>
                </div>
            </div>

            {{-- Mantención --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mantención</p>
                        <p class="mt-1 text-xl font-black text-blue-600">{{ $maintenanceBeds }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-tools text-blue-600"></i>
                    </div>
                </div>
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
        <div class="bg-slate-100 rounded-full h-2 overflow-hidden">
            <div class="bg-amber-500 h-full transition-all duration-300" style="width: {{ $occupancyRate }}%"></div>
        </div>
        <p class="text-xs text-slate-500 text-center mt-2">
            {{ $occupiedBeds }} de {{ $totalBeds }} camas ocupadas
        </p>
    </div>

    {{-- Quick Actions --}}
    <div class="p-4 bg-[#f9fbfd] border-t border-[#e5e7eb]">
        <div class="flex gap-2">
            <a href="{{ route('admin.beds.create') }}" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors text-center">
                <i class="fas fa-plus mr-1"></i> Nueva Cama
            </a>
            <a href="{{ route('admin.beds.index') }}" class="flex-1 px-3 py-2 bg-white hover:bg-[#f9fbfd] text-[#0f172a] text-xs font-semibold rounded-lg transition-colors text-center border border-[#e5e7eb]">
                <i class="fas fa-list mr-1"></i> Gestionar
            </a>
        </div>
    </div>
</div>
