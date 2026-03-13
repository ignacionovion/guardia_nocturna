{{-- Grid de personal activo --}}
<x-ui.card class="!bg-slate-900 !border-slate-800">
    <form id="guardia-attendance-form" method="POST" action="{{ route('admin.guardias.bulk_update', $myGuardia->id) }}">
        @csrf

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
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
                <div class="col-span-full bg-slate-900 rounded-2xl border border-slate-800 p-10 text-center text-slate-300">
                    Sin dotación asignada.
                </div>
            @endforelse
        </div>

        @if($outOfServiceStaff->isNotEmpty())
            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Inhabilitados</div>
                    <div class="text-[11px] font-bold text-slate-400">{{ $outOfServiceStaff->count() }}</div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                    @foreach($outOfServiceStaff as $staff)
                        <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col p-3">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $staff->cargo_texto ?? 'Bombero' }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-100">INHABILITADO</div>
                            </div>
                            <div class="mt-2 text-sm font-semibold text-slate-100 leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                {{ $staff->apellido_paterno }}{{ $staff->apellido_materno ? ' ' . $staff->apellido_materno : '' }}, {{ $staff->nombres }}
                            </div>
                            @if($canInhabilitar)
                                <button type="button" onclick="toggleHabilitar('{{ $staff->id }}')" class="mt-3 w-full bg-slate-950 hover:bg-slate-900 text-green-300 font-bold uppercase tracking-wider text-[10px] py-1.5 rounded-lg border border-green-900">
                                    Habilitar
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </form>
</x-ui.card>
