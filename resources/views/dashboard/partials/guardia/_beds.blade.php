{{-- Widget de camas - Diseño Premium --}}
<div class="bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                <i class="fas fa-bed text-cyan-400"></i>
            </div>
            <span class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Camas</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="sendBedReportEmail()" class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center hover:bg-emerald-500/30 transition-colors" title="Enviar reporte">
                <i class="fas fa-paper-plane text-emerald-400 text-xs"></i>
            </button>
            <a href="{{ route('camas') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-500/20 border border-blue-500/30 text-xs font-semibold text-blue-400 hover:bg-blue-500/30 transition-colors">
                Ver
            </a>
        </div>
    </div>
    <div class="p-5">
        <div class="flex items-end gap-2">
            <span class="text-5xl font-bold text-white">{{ $availableBeds }}</span>
            <span class="text-2xl font-semibold text-slate-500 mb-1">/{{ $totalBeds }}</span>
        </div>
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-2">Camas Disponibles</div>
        
        {{-- Barra de progreso visual --}}
        @php
            $occupiedPercent = $totalBeds > 0 ? (($totalBeds - $availableBeds) / $totalBeds) * 100 : 0;
        @endphp
        <div class="mt-4 h-2 bg-slate-700/50 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-cyan-500 to-cyan-400 rounded-full transition-all duration-500" style="width: {{ 100 - $occupiedPercent }}%"></div>
        </div>
    </div>
</div>
