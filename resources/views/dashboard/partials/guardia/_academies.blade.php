{{-- Widget de academias --}}
<div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
        <div class="text-sm font-bold text-slate-200 uppercase tracking-wider">Academias Nocturnas</div>
        <button onclick="openAcademyModal()" class="text-xs font-bold text-blue-400 hover:text-blue-300 uppercase tracking-wider">Registrar</button>
    </div>
    <div class="p-5">
        @if($academiesList->isEmpty())
            <div class="text-sm text-slate-400">Sin academias registradas.</div>
        @else
            <div class="space-y-4">
                @foreach($academiesList->take(5) as $academy)
                    <div class="border-l-2 border-slate-800 pl-4">
                        <div class="text-sm font-bold text-slate-100">{{ $academy->title }}</div>
                        <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $academy->description }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                <i class="fas fa-clock text-[9px]"></i>
                                {{ ($academy->date ?? $academy->created_at)?->format('H:i') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                {{ ($academy->date ?? $academy->created_at)?->locale('es')->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
