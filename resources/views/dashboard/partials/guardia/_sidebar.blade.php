{{-- Sidebar derecho con widgets - Diseño Premium --}}
<aside class="space-y-5">
    {{-- Widget Reloj --}}
    <div class="bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 overflow-hidden shadow-lg shadow-black/20">
        <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-clock text-blue-400"></i>
                </div>
                <span class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Hora Local</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30">
                <div class="w-2 h-2 rounded-full bg-emerald-500 live-indicator"></div>
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">En Línea</span>
            </div>
        </div>
        <div class="p-5">
            @unless($attendanceEnabled)
                <div class="mb-4 flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-amber-500/15 border border-amber-500/30">
                    <i class="fas fa-clock text-amber-400 text-xs"></i>
                    <span class="text-xs font-semibold text-amber-400">Habilitado {{ $attendanceEnableTime }} - {{ $attendanceDisableTime }}</span>
                </div>
            @endunless
            <div class="bg-slate-950 border-2 border-slate-700/50 rounded-2xl py-5 px-6 flex items-center justify-center shadow-inner">
                <span id="digital-clock" class="text-3xl md:text-4xl font-mono font-bold tracking-widest text-white">--:--:--</span>
            </div>
        </div>
    </div>

    {{-- Cumpleaños --}}
    @include('dashboard.partials.guardia._birthdays')

    {{-- Novedades --}}
    @include('dashboard.partials.guardia._novelties')

    {{-- Academias --}}
    @include('dashboard.partials.guardia._academies')

    {{-- Camas --}}
    @include('dashboard.partials.guardia._beds')
</aside>
