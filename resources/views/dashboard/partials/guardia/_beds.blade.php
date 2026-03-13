{{-- Widget de camas --}}
<div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
        <div class="text-sm font-bold text-slate-200 uppercase tracking-wider">Camas</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="sendBedReportEmail()" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 uppercase tracking-wider flex items-center gap-1" title="Enviar reporte por email">
                <i class="fas fa-paper-plane"></i>
            </button>
            <a href="{{ route('camas') }}" class="text-xs font-bold text-blue-400 hover:text-blue-300 uppercase tracking-wider">Ver</a>
        </div>
    </div>
    <div class="p-5">
        <div class="text-4xl font-bold text-slate-100">{{ $availableBeds }}<span class="text-lg text-slate-400 font-bold">/{{ $totalBeds }}</span></div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Disponibles</div>
    </div>
</div>
