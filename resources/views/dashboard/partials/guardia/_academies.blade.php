{{-- Widget de academias - Diseño Premium --}}
<div class="bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-indigo-400"></i>
            </div>
            <span class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Academias</span>
        </div>
        <button onclick="openAcademyModal()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/20 border border-indigo-500/30 text-xs font-semibold text-indigo-400 hover:bg-indigo-500/30 transition-colors">
            <i class="fas fa-plus text-[10px]"></i>
            Registrar
        </button>
    </div>
    <div class="p-5">
        @if($academiesList->isEmpty())
            <div class="text-sm text-slate-500 text-center py-4">Sin academias registradas</div>
        @else
            <div class="space-y-3">
                @foreach($academiesList->take(5) as $academy)
                    <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/30">
                        <div class="text-sm font-semibold text-white">{{ $academy->title }}</div>
                        <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $academy->description }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-500/15 border border-amber-500/30">
                                <i class="fas fa-clock text-[10px] text-amber-400"></i>
                                <span class="text-[10px] font-bold text-amber-400">{{ ($academy->date ?? $academy->created_at)?->format('H:i') }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500">
                                {{ ($academy->date ?? $academy->created_at)?->locale('es')->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
