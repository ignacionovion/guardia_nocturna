{{-- Grid de personal activo - Diseño Premium --}}
<section>
    <form id="guardia-attendance-form" method="POST" action="{{ route('admin.guardias.bulk_update', $myGuardia->id) }}">
        @csrf

        {{-- Grid principal de bomberos - más columnas para tarjetas angostas --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5">
            @forelse($activeStaff as $staff)
                @include('dashboard.partials.guardia._staff_card', [
                    'staff' => $staff,
                    'myGuardia' => $myGuardia,
                    'replacementByReplacement' => $replacementByReplacement,
                    'replacementByOriginal' => $replacementByOriginal,
                    'bedByFirefighter' => $bedByFirefighter,
                    'canInhabilitar' => $canInhabilitar,
                ])
            @empty
                <div class="col-span-full bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border border-slate-700/50 p-12 text-center shadow-lg">
                    <div class="w-16 h-16 rounded-2xl bg-slate-700/50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-slate-500"></i>
                    </div>
                    <div class="text-lg font-semibold text-slate-400">Sin dotación asignada</div>
                    <div class="text-sm text-slate-500 mt-1">No hay personal asignado a esta guardia</div>
                </div>
            @endforelse
        </div>

        {{-- Sección de inhabilitados --}}
        @if($outOfServiceStaff->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <i class="fas fa-user-slash text-red-400 text-sm"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Inhabilitados</span>
                    <span class="px-2 py-0.5 rounded-lg bg-red-500/20 border border-red-500/30 text-xs font-bold text-red-400">{{ $outOfServiceStaff->count() }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5">
                    @foreach($outOfServiceStaff as $staff)
                        <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/70 rounded-xl border border-red-900/30 overflow-hidden p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[9px] font-medium text-slate-500 uppercase tracking-wider">{{ $staff->cargo_texto ?? 'Bombero' }}</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/30">Inhab.</span>
                            </div>
                            <div class="text-xs font-semibold text-slate-300 leading-tight truncate" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                {{ $staff->apellido_paterno }}, {{ $staff->nombres }}
                            </div>
                            @if($canInhabilitar)
                                <button type="button" onclick="toggleHabilitar('{{ $staff->id }}')" class="mt-2 w-full bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 font-semibold uppercase tracking-wider text-[9px] py-1.5 rounded border border-emerald-500/30 transition-colors">
                                    <i class="fas fa-user-check mr-1"></i> Habilitar
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </form>
</section>
