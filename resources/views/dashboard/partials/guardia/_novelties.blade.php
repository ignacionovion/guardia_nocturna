{{-- Widget de novedades --}}
@php
    $noveltyColorMap = [
        'Informativa' => 'text-blue-400',
        'Incidente' => 'text-amber-400',
        'Mantención' => 'text-emerald-400',
        'Urgente' => 'text-red-400',
        'Permanente' => 'text-purple-400',
    ];
@endphp

<x-ui.card class="!bg-slate-900 !border-slate-800">
    <x-slot:header>
        <div class="flex items-center justify-between w-full">
            <div class="text-label">Bitácora de Novedades</div>
            <button onclick="openNoveltyModal()" class="text-xs font-semibold text-blue-400 hover:text-blue-300 uppercase tracking-wider">Registrar</button>
        </div>
    </x-slot:header>
    
    @if($noveltiesList->isEmpty())
        <div class="text-body text-slate-400">Sin novedades recientes.</div>
    @else
        <div class="space-y-4">
            @foreach($noveltiesList as $novelty)
                @php
                    $colorClass = $noveltyColorMap[$novelty->type] ?? 'text-blue-400';
                @endphp
                <div class="border-l-2 border-slate-700 pl-4 py-2">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-bold {{ $colorClass }} uppercase tracking-wider">{{ $novelty->type }}</span>
                        @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                            <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">PERMANENTE</span>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-100">{{ $novelty->title }}</div>
                    <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-2">
                        {{ $novelty->created_at->locale('es')->diffForHumans() }}
                        @if($novelty->user)
                            <span class="text-slate-600">|</span>
                            {{ $novelty->user->name ?? '-' }}
                        @endif
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
</x-ui.card>
