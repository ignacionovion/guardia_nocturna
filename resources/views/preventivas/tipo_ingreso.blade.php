@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-slate-900 flex items-center justify-center px-4">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            @if(file_exists(public_path('brand/guardiappcheck.png')))
                <img src="{{ asset('brand/guardiappcheck.png') }}?v={{ filemtime(public_path('brand/guardiappcheck.png')) }}" alt="GuardiaAPP" class="mx-auto h-[80px] w-auto drop-shadow-sm">
            @else
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-600 text-white mb-4 shadow-2xl">
                    <i class="fas fa-shield-alt text-4xl"></i>
                </div>
            @endif
            <div class="mt-4 text-xs font-black uppercase tracking-widest text-slate-400">Preventivas</div>
            <div class="text-3xl font-extrabold text-white mt-1">Tipo de Ingreso</div>
            <div class="text-sm text-slate-400 mt-2">Selecciona cómo ingresarás al turno.</div>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl border border-slate-700 p-6 shadow-2xl">
            <!-- Info del bombero identificado -->
            <div class="mb-6 p-4 rounded-xl bg-slate-700/50 border border-slate-600">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Bombero identificado</div>
                <div class="text-lg font-extrabold text-white uppercase">{{ $bombero->apellido_paterno }}</div>
                <div class="text-sm text-slate-300">{{ $bombero->nombres }}</div>
                @if($bombero->cargo_texto)
                    <div class="mt-1 text-xs font-semibold text-slate-500">{{ $bombero->cargo_texto }}</div>
                @endif
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-500/20 border border-rose-500/30 text-rose-200 text-sm font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('preventivas.public.tipo_ingreso.store', $token) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <!-- Opción REFUERZO -->
                    <label class="cursor-pointer group">
                        <input type="radio" name="tipo" value="refuerzo" class="peer hidden" required>
                        <div class="p-4 rounded-xl border-2 border-slate-600 bg-slate-700/30 peer-checked:border-sky-500 peer-checked:bg-sky-500/20 transition-all group-hover:border-slate-500">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-sky-500/20 border border-sky-500/30 flex items-center justify-center mb-3">
                                    <i class="fas fa-user-plus text-sky-400 text-xl"></i>
                                </div>
                                <div class="text-sm font-black text-white uppercase tracking-wider">REFUERZO</div>
                                <div class="text-[10px] text-slate-400 mt-1">Ingreso adicional al turno</div>
                            </div>
                        </div>
                    </label>

                    <!-- Opción REEMPLAZO -->
                    <label class="cursor-pointer group">
                        <input type="radio" name="tipo" value="reemplazo" class="peer hidden" required>
                        <div class="p-4 rounded-xl border-2 border-slate-600 bg-slate-700/30 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all group-hover:border-slate-500">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center mb-3">
                                    <i class="fas fa-exchange-alt text-purple-400 text-xl"></i>
                                </div>
                                <div class="text-sm font-black text-white uppercase tracking-wider">REEMPLAZO</div>
                                <div class="text-[10px] text-slate-400 mt-1">Reemplazar a un bombero</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Selección de bombero a reemplazar (solo visible si selecciona reemplazo) -->
                <div id="reemplazo-section" class="hidden space-y-3">
                    <label for="bombero_reemplazo_id" class="block text-xs font-black text-slate-400 uppercase tracking-widest">
                        ¿A quién reemplazas?
                    </label>
                    <select name="bombero_reemplazo_id" 
                            id="bombero_reemplazo_id"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-600 bg-slate-700/50 text-white text-sm font-bold focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        <option value="">Selecciona un bombero</option>
                        @foreach($availableForReplacement as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->apellido_paterno }}, {{ $b->nombres }}
                                @if($b->cargo_texto) - {{ $b->cargo_texto }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500">El bombero seleccionado será marcado como reemplazado.</p>
                </div>

                <button type="submit" class="w-full py-4 px-6 bg-emerald-600 hover:bg-emerald-500 text-white font-black uppercase tracking-widest rounded-xl border border-emerald-500 shadow-lg shadow-emerald-600/20 transition-all active:scale-[0.98]">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Ingreso
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('preventivas.public.show', $token) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-300 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver a la lista
                </a>
            </div>
        </div>

        <div class="mt-8 text-center">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-600">{{ $event->title }}</div>
            <div class="text-xs text-slate-500 mt-1">Turno: {{ $shift->label ?? $shift->start_time . ' - ' . $shift->end_time }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Mostrar/ocultar selección de bombero a reemplazar
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const reemplazoSection = document.getElementById('reemplazo-section');
            const select = document.getElementById('bombero_reemplazo_id');
            
            if (this.value === 'reemplazo') {
                reemplazoSection.classList.remove('hidden');
                select.setAttribute('required', 'required');
            } else {
                reemplazoSection.classList.add('hidden');
                select.removeAttribute('required');
                select.value = '';
            }
        });
    });
</script>
@endpush
