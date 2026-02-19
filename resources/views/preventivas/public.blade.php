<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventivas - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
    <div class="max-w-2xl mx-auto px-4 py-10">
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

                <form method="POST" action="{{ route('preventivas.public.confirm', $event->public_token) }}" class="p-6">
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

                    <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Selecciona tu nombre</label>
                    <select name="assignment_id" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold">
                        <option value="">Seleccionar...</option>
                        @foreach($assignments as $a)
                            @php
                                $disabled = (bool) $a->attendance;
                                $label = trim((string)($a->firefighter?->apellido_paterno ?? '') . ' ' . (string)($a->firefighter?->nombres ?? ''));
                                $refuerzoBadge = $a->es_refuerzo ? ' [REFUERZO]' : '';
                            @endphp
                            <option value="{{ $a->id }}" {{ (string) old('assignment_id') === (string) $a->id ? 'selected' : '' }} {{ $disabled ? 'disabled' : '' }}>
                                {{ $label }}{{ $refuerzoBadge }}{{ $disabled ? ' (ya confirmado)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[12px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-check"></i>
                            Confirmar Asistencia
                        </button>
                        <a href="{{ route('preventivas.public.identificar.form', $event->public_token) }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-4 rounded-xl text-[12px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-fingerprint"></i>
                            Identificar
                        </a>
                    </div>

                    <div class="text-xs text-slate-400 mt-4">
                        Una vez confirmado, el registro queda bloqueado.
                    </div>

                    <div class="mt-6">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-300">Asignados a este turno</div>
                        <div class="mt-3 space-y-2">
                            @foreach($assignments as $a)
                                @php
                                    $locked = (bool) $a->attendance;
                                    $label = trim((string)($a->firefighter?->apellido_paterno ?? '') . ' ' . (string)($a->firefighter?->nombres ?? ''));
                                    $entrada = $a->entrada_hora ? $a->entrada_hora->format('H:i') : null;
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="text-sm font-extrabold text-white">{{ $label }}</div>
                                        @if($a->es_refuerzo)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-sky-500/20 text-sky-200 border border-sky-500/30">
                                                <i class="fas fa-user-plus mr-1"></i>REFUERZO
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($entrada)
                                            <span class="text-[10px] text-slate-400">{{ $entrada }}</span>
                                        @endif
                                        @if($locked)
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-100 border border-emerald-500/30">
                                                <i class="fas fa-lock mr-1"></i>Bloqueado
                                            </div>
                                        @else
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-slate-100/10 text-slate-200 border border-white/10">Disponible</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            GuardiaAPP
        </div>
    </div>
</body>
</html>
