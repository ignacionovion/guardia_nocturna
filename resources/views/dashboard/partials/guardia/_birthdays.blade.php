{{-- Widget de cumpleaños - Diseño Premium --}}
<div class="bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-pink-500/20 flex items-center justify-center">
                <i class="fas fa-cake-candles text-pink-400"></i>
            </div>
            <span class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Cumpleaños</span>
        </div>
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $currentMonthName }}</span>
    </div>
    <div class="p-5">
        @if($birthdaysList->isEmpty())
            <div class="text-sm text-slate-500 text-center py-2">Sin cumpleaños este mes</div>
        @else
            <div class="space-y-3">
                @foreach($birthdaysList->take(5) as $user)
                    <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-800/50 border border-slate-700/30">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white truncate">{{ $user->nombres }} {{ $user->apellido_paterno }}</div>
                            <div class="text-xs text-slate-500">Bombero</div>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-pink-500/20 border border-pink-500/30">
                            <span class="text-sm font-bold text-pink-400">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
