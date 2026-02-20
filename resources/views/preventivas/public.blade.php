<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventivas - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 min-h-screen text-slate-100" @if($needsTipoIngreso) data-needs-tipo-ingreso="1" @endif>
    <div class="w-full max-w-2xl mx-auto px-3 sm:px-4 py-6 sm:py-10">
        <div class="text-center">
            @if(file_exists(public_path('brand/guardiapp9-0.png')))
                <img src="{{ asset('brand/guardiapp9-0.png') }}?v={{ filemtime(public_path('brand/guardiapp9-0.png')) }}" alt="GuardiaAPP" class="mx-auto h-[80px] w-auto drop-shadow-sm">
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 shadow-xl">
                    <i class="fas fa-qrcode text-2xl text-slate-100"></i>
                </div>
            @endif
            <div class="-mt-3 text-xs font-black uppercase tracking-widest text-slate-400">Guardias Preventivas</div>
            <div class="text-2xl font-extrabold text-white">{{ $event->title }}</div>
            <div class="text-sm text-slate-400 mt-1">Hora local: {{ $now->format('d-m-Y H:i') }}</div>
        </div>

        <div class="mt-8 bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
            @if(!$shift)
                <div class="p-6">
                    <div class="text-lg font-extrabold">No hay un turno activo en este momento</div>
                    <div class="text-sm text-slate-300 mt-2">Vuelve a intentar durante el horario de un turno.</div>
                </div>
            @else
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <div class="text-sm font-black uppercase tracking-widest text-slate-300">Turno actual</div>
                    <div class="text-xl font-extrabold text-white mt-1">
                        {{ substr((string) $shift->start_time, 0, 5) }} a {{ substr((string) $shift->end_time, 0, 5) }}
                    </div>
                    <div class="text-sm text-slate-300 mt-1">Fecha: {{ $shift->shift_date?->format('d-m-Y') }}</div>
                </div>

                {{-- Siempre mostrar el formulario de RUT --}}
                <form method="POST" action="{{ route('preventivas.public.rut', $event->public_token) }}" class="p-6">
                    @csrf
                    @if(session('success'))
                        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-100">
                            <div class="text-sm font-extrabold"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="mb-4 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-amber-100">
                            <div class="text-sm font-extrabold"><i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}</div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                            <div class="text-sm font-extrabold"><i class="fas fa-times-circle mr-2"></i>{{ session('error') }}</div>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                            @foreach($errors->all() as $error)
                                <div class="text-sm font-extrabold"><i class="fas fa-times-circle mr-2"></i>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Si hay bombero identificado que necesita tipo de ingreso, solo mostrar info del bombero (el modal se abre automáticamente) --}}
                    @if($needsTipoIngreso && $identifiedBombero)
                        <div class="mb-4 p-4 rounded-xl bg-slate-700/50 border border-slate-600">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Bombero identificado</div>
                            <div class="text-lg font-extrabold text-white uppercase">{{ $identifiedBombero->apellido_paterno }}</div>
                            <div class="text-sm text-slate-300">{{ $identifiedBombero->nombres }}</div>
                            @if($identifiedBombero->cargo_texto)
                                <div class="mt-1 text-xs font-semibold text-slate-500">{{ $identifiedBombero->cargo_texto }}</div>
                            @endif
                        </div>
                        
                        <div class="text-center text-sm text-slate-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Cargando opciones de ingreso...
                        </div>
                    @else
                        {{-- Formulario normal de RUT --}}
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Ingresa tu RUT</label>
                        <input type="text" name="rut" id="rutInput" required placeholder="Ej: 12345678-9" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold uppercase placeholder-slate-500"
                            pattern="[0-9]{7,8}-[0-9kK]" title="Formato: 12345678-5" maxlength="10">
                        <div class="text-xs text-slate-500 mt-1">Formato: 12345678-5</div>

                        <script>
                            (function() {
                                const input = document.getElementById('rutInput');
                                if (!input) return;
                                
                                input.addEventListener('input', function(e) {
                                    let value = e.target.value.replace(/[^0-9kK]/g, '');
                                    
                                    if (value.length > 1) {
                                        const body = value.slice(0, -1);
                                        const dv = value.slice(-1);
                                        value = body + '-' + dv;
                                    }
                                    
                                    e.target.value = value.toUpperCase();
                                });
                            })();
                        </script>

                        <div class="mt-4">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[12px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                                <i class="fas fa-fingerprint"></i>
                                Confirmar Asistencia
                            </button>
                        </div>
                    @endif
                </form>

                {{-- Modal de selección de tipo de ingreso --}}
                <div id="tipoIngresoModal" class="fixed inset-0 z-50 hidden">
                    {{-- Backdrop oscuro --}}
                    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-sm" onclick="closeModal()"></div>
                    
                    {{-- Contenedor centrado --}}
                    <div class="absolute inset-0 flex items-center justify-center p-4">
                        <div class="bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700 overflow-hidden">
                            {{-- Header --}}
                            <div class="p-6 border-b border-slate-700 relative">
                                <div class="text-center">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">Preventivas</div>
                                    <div class="text-xl font-black text-white">Tipo de Ingreso</div>
                                </div>
                                <button type="button" onclick="closeModal()" 
                                        class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-700 text-slate-400 hover:text-white hover:bg-slate-600 transition-colors">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                            
                            <div class="p-6">
                                @if(session('error') || $errors->any())
                                    <div class="mb-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                                        @if(session('error'))
                                            <div class="text-sm font-extrabold"><i class="fas fa-times-circle mr-2"></i>{{ session('error') }}</div>
                                        @endif
                                        @foreach($errors->all() as $error)
                                            <div class="text-sm font-extrabold"><i class="fas fa-times-circle mr-2"></i>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Info del bombero identificado --}}
                                @if($identifiedBombero)
                                <div class="mb-6 p-4 rounded-xl bg-slate-700/50 border border-slate-600">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Bombero identificado</div>
                                    <div class="text-lg font-extrabold text-white uppercase">{{ $identifiedBombero->apellido_paterno }}</div>
                                    <div class="text-sm text-slate-300">{{ $identifiedBombero->nombres }}</div>
                                    @if($identifiedBombero->cargo_texto)
                                        <div class="mt-1 text-xs font-semibold text-slate-500">{{ $identifiedBombero->cargo_texto }}</div>
                                    @endif
                                </div>
                                @endif

                                <form action="{{ route('preventivas.public.tipo_ingreso.store', $event->public_token) }}" method="POST" id="tipoIngresoForm">
                                    @csrf

                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        {{-- Opción REFUERZO --}}
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="tipo" value="refuerzo" class="peer hidden" required>
                                            <div class="p-4 rounded-xl border-2 border-slate-600 bg-slate-700/30 peer-checked:border-sky-500 peer-checked:bg-sky-500/20 transition-all group-hover:border-slate-500">
                                                <div class="text-center">
                                                    <div class="w-12 h-12 mx-auto rounded-full bg-sky-500/20 border border-sky-500/30 flex items-center justify-center mb-3">
                                                        <i class="fas fa-user-plus text-sky-400 text-xl"></i>
                                                    </div>
                                                    <div class="text-sm font-black text-white uppercase tracking-wider">REFUERZO</div>
                                                    <div class="text-[10px] text-slate-400 mt-1">Ingreso adicional</div>
                                                </div>
                                            </div>
                                        </label>

                                        {{-- Opción REEMPLAZO --}}
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="tipo" value="reemplazo" class="peer hidden" required>
                                            <div class="p-4 rounded-xl border-2 border-slate-600 bg-slate-700/30 peer-checked:border-purple-500 peer-checked:bg-purple-500/20 transition-all group-hover:border-slate-500">
                                                <div class="text-center">
                                                    <div class="w-12 h-12 mx-auto rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center mb-3">
                                                        <i class="fas fa-exchange-alt text-purple-400 text-xl"></i>
                                                    </div>
                                                    <div class="text-sm font-black text-white uppercase tracking-wider">REEMPLAZO</div>
                                                    <div class="text-[10px] text-slate-400 mt-1">Reemplazar bombero</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- Selección de bombero a reemplazar --}}
                                    <div id="reemplazoSection" class="hidden mb-6">
                                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">
                                            ¿A quién reemplazas?
                                        </label>
                                        <select name="bombero_reemplazo_id" id="bomberoReemplazoId"
                                                class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-slate-100 font-semibold focus:border-purple-500 focus:outline-none">
                                            <option value="">Selecciona un bombero</option>
                                            @foreach($availableForReplacement as $b)
                                                <option value="{{ $b->id }}">
                                                    {{ $b->apellido_paterno }}, {{ $b->nombres }}
                                                    @if($b->cargo_texto) - {{ $b->cargo_texto }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-[10px] text-slate-500 mt-2">El bombero seleccionado será marcado como reemplazado.</p>
                                    </div>

                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-6 rounded-xl text-sm transition-all shadow-lg hover:shadow-xl uppercase tracking-widest">
                                        <i class="fas fa-check-circle"></i>
                                        Confirmar Ingreso
                                    </button>
                                </form>

                                <div class="mt-4 text-center">
                                    <a href="{{ route('preventivas.public.show', $event->public_token) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-300 transition-colors">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Volver e ingresar otro RUT
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function openModal() {
                        var modal = document.getElementById('tipoIngresoModal');
                        if (modal) {
                            modal.classList.remove('hidden');
                            document.body.style.overflow = 'hidden';
                        }
                    }
                    
                    function closeModal() {
                        var modal = document.getElementById('tipoIngresoModal');
                        if (modal) {
                            modal.classList.add('hidden');
                            document.body.style.overflow = 'auto';
                        }
                    }
                    
                    // Mostrar/ocultar sección de reemplazo
                    document.querySelectorAll('input[name="tipo"]').forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            var section = document.getElementById('reemplazoSection');
                            var select = document.getElementById('bomberoReemplazoId');
                            if (this.value === 'reemplazo') {
                                section.classList.remove('hidden');
                                select.setAttribute('required', 'required');
                            } else {
                                section.classList.add('hidden');
                                select.removeAttribute('required');
                                select.value = '';
                            }
                        });
                    });

                    // Abrir modal automáticamente si necesita tipo de ingreso
                    document.addEventListener('DOMContentLoaded', function() {
                        var needsModal = document.body.getAttribute('data-needs-tipo-ingreso');
                        if (needsModal === '1' || needsModal === 'true') {
                            setTimeout(function() {
                                openModal();
                            }, 100);
                        }
                    });
                </script>

                {{-- Lista de asignados --}}
                <div class="px-6 pb-6">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-300">Asignados a este turno</div>
                    <div class="mt-3 space-y-2">
                        @foreach($assignments as $a)
                            @php
                                $locked = (bool) $a->attendance;
                                $label = trim((string)($a->firefighter?->apellido_paterno ?? '') . ' ' . (string)($a->firefighter?->nombres ?? ''));
                                $entrada = $a->entrada_hora ? $a->entrada_hora->format('H:i') : null;
                                $esReemplazo = (bool) $a->reemplaza_a_bombero_id;
                                $reemplazaA = $a->replacedFirefighter;
                                $fueReemplazado = !$a->es_refuerzo && !$esReemplazo && !$locked && !$a->attendance && in_array($a->bombero_id, $replacedFirefighterIds ?? []);
                            @endphp
                            <div class="flex items-center justify-between rounded-xl border {{ $locked ? 'border-emerald-500/30 bg-emerald-500/10' : ($fueReemplazado ? 'border-rose-500/30 bg-rose-500/10' : 'border-white/10 bg-white/5') }} px-4 py-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="text-sm font-extrabold {{ $fueReemplazado ? 'text-slate-400 line-through' : 'text-white' }}">{{ $label }}</div>
                                    @if($a->es_refuerzo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-sky-500/20 text-sky-200 border border-sky-500/30">
                                            <i class="fas fa-user-plus mr-1"></i>REFUERZO
                                        </span>
                                    @endif
                                    @if($esReemplazo && $reemplazaA)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-200 border border-purple-500/30">
                                            <i class="fas fa-exchange-alt mr-1"></i>REEMPLAZA A: {{ $reemplazaA->apellido_paterno }}
                                        </span>
                                    @endif
                                    @if($fueReemplazado)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-200 border border-rose-500/30">
                                            <i class="fas fa-times-circle mr-1"></i>REEMPLAZADO
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($entrada)
                                        <span class="text-[10px] text-slate-400">{{ $entrada }}</span>
                                    @endif
                                    @if($locked)
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-emerald-500/20 text-emerald-100 border border-emerald-500/30">
                                            <i class="fas fa-check-circle mr-1"></i>Confirmado
                                        </div>
                                    @elseif($fueReemplazado)
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-rose-500/20 text-rose-100 border border-rose-500/30">
                                            <i class="fas fa-exchange-alt mr-1"></i>Reemplazado
                                        </div>
                                    @else
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-slate-100/10 text-slate-300 border border-white/10">Pendiente</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            GuardiaAPP
        </div>
    </div>
</body>
</html>
