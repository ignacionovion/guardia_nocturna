{{-- Widget de novedades - Diseño Premium --}}
@php
    $noveltyStyles = [
        'Informativa' => ['icon' => 'fa-circle-info', 'color' => 'blue'],
        'Incidente' => ['icon' => 'fa-triangle-exclamation', 'color' => 'amber'],
        'Mantención' => ['icon' => 'fa-wrench', 'color' => 'emerald'],
        'Urgente' => ['icon' => 'fa-bolt', 'color' => 'red'],
        'Permanente' => ['icon' => 'fa-thumbtack', 'color' => 'purple'],
    ];
@endphp

<div class="bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center">
                <i class="fas fa-clipboard-list text-amber-400"></i>
            </div>
            <span class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Novedades</span>
        </div>
        <button onclick="openNoveltyModal()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-500/20 border border-blue-500/30 text-xs font-semibold text-blue-400 hover:bg-blue-500/30 transition-colors">
            <i class="fas fa-plus text-[10px]"></i>
            Registrar
        </button>
    </div>
    <div class="p-5">
        @if($noveltiesList->isEmpty())
            <div class="text-sm text-slate-500 text-center py-4">Sin novedades recientes</div>
        @else
            <div class="space-y-3">
                @foreach($noveltiesList as $novelty)
                    @php
                        $style = $noveltyStyles[$novelty->type] ?? $noveltyStyles['Informativa'];
                        $colorClass = $style['color'];
                    @endphp
                    <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/30">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-{{ $colorClass }}-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas {{ $style['icon'] }} text-{{ $colorClass }}-400 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold text-{{ $colorClass }}-400 uppercase tracking-wider">{{ $novelty->type }}</span>
                                    @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                                        <span class="text-[9px] font-bold text-purple-400 uppercase tracking-wider px-1.5 py-0.5 rounded bg-purple-500/20">FIJO</span>
                                    @endif
                                </div>
                                <div class="text-sm font-semibold text-white mt-1">{{ $novelty->title }}</div>
                                <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                                <div class="flex items-center gap-2 mt-2 text-[10px] text-slate-500">
                                    <span>{{ $novelty->created_at->locale('es')->diffForHumans() }}</span>
                                    @if($novelty->user)
                                        <span class="text-slate-600">•</span>
                                        <span>{{ $novelty->user->name ?? '-' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($noveltiesPaginator)
                <div class="mt-4 dark-pagination">
                    {{ $noveltiesPaginator->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
