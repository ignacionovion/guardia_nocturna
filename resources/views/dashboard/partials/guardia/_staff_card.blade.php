{{-- Tarjeta individual de personal --}}
@php
    $repAsReplacement = $replacementByReplacement->get($staff->id);
    $repAsOriginal = $replacementByOriginal->get($staff->id);
    $status = $repAsReplacement ? 'reemplazo' : $staff->estado_asistencia;
    $lockAttendanceStatus = (bool) ($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    $requiresConfirmation = in_array($status, ['constituye', 'reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement;
    $showConfirmBox = $requiresConfirmation;
    $hideStatusSection = (bool) $repAsReplacement;
    $canReplace = !($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    
    $statusHeaderClass = match ($status) {
        'constituye' => 'bg-emerald-950/40',
        'reemplazo' => 'bg-purple-950/40',
        'permiso' => 'bg-amber-950/35',
        'ausente' => 'bg-slate-950',
        'licencia' => 'bg-blue-950/40',
        'falta' => 'bg-rose-950/40',
        default => 'bg-slate-950',
    };
    
    $statusLabel = match ($status) {
        'constituye' => 'CONSTITUYE',
        'reemplazo' => 'REEMPLAZO',
        'permiso' => 'PERMISO',
        'ausente' => 'AUSENTE',
        'licencia' => 'LICENCIA',
        'falta' => 'FALTA',
        default => 'CONSTITUYE',
    };
    
    $statusBtnClass = match ($status) {
        'constituye' => 'bg-emerald-500/80 text-white border-emerald-400/50',
        'reemplazo' => 'bg-purple-500/80 text-white border-purple-400/50',
        'permiso' => 'bg-amber-500/80 text-white border-amber-400/50',
        'ausente' => 'bg-slate-400/30 text-white border-slate-500/30',
        'licencia' => 'bg-blue-600/80 text-white border-blue-400/50',
        'falta' => 'bg-red-600/80 text-white border-red-400/50',
        default => 'bg-emerald-500/80 text-white border-emerald-400/50',
    };
    
    $serviceLabel = $staff->service_label ?? '—';
    $bedNum = $bedByFirefighter[$staff->id] ?? null;
@endphp

<input type="hidden" name="users[{{ $staff->id }}][estado_asistencia]" id="attendance-status-{{ $staff->id }}" value="{{ $status }}">
<input type="hidden" name="users[{{ $staff->id }}][confirm_token]" id="confirm-token-{{ $staff->id }}" value="">

<div id="guardia-card-{{ $staff->id }}" 
     class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col h-full" 
     data-card-user="{{ $staff->id }}" 
     data-requires-confirmation="{{ $requiresConfirmation ? '1' : '0' }}" 
     data-is-confirmed="0">
    
    {{-- Header --}}
    <div id="card-header-{{ $staff->id }}" class="{{ $statusHeaderClass }} text-white px-2 py-1.5 flex items-center justify-between">
        <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-white leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                {{ strtoupper($staff->apellido_paterno ?: $staff->nombres) }}
            </div>
            @if($staff->es_jefe_guardia)
                <div class="flex items-center gap-2 text-xs text-slate-300">
                    <i class="fas fa-user-group opacity-70"></i>
                    <span class="font-bold">Jefe</span>
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if($canInhabilitar)
                <button type="button" onclick="toggleInhabilitado('{{ $staff->id }}')" class="h-6 px-2 rounded-md border border-slate-700 bg-slate-900/60 hover:bg-slate-900 text-[9px] font-bold uppercase tracking-wider text-slate-200">
                    Inhabilitar
                </button>
            @endif
        </div>
    </div>

    <div class="p-1.5 flex-1 flex flex-col">
        {{-- Foto con overlay --}}
        <div class="relative bg-slate-950 rounded-xl border border-slate-800 overflow-hidden w-full h-[200px] mb-2 shrink-0">
            @if($staff->photo_path)
                <img src="{{ url('media/' . ltrim($staff->photo_path, '/')) }}" class="w-full h-full object-cover object-center scale-100" alt="Foto">
            @else
                <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-200 font-bold text-3xl">
                    {{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}
                </div>
            @endif

            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/90 via-black/60 to-transparent"></div>

            <div class="absolute inset-x-0 bottom-0 p-2">
                <div class="text-xs font-semibold text-white leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                    {{ $staff->nombres }} {{ $staff->apellido_paterno }}
                </div>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                    <span class="text-[10px] font-medium text-white/90 uppercase tracking-wider">
                        {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
                    </span>
                    @if($staff->es_permanente)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-emerald-300 bg-emerald-500/30 border border-emerald-400/30 rounded px-1 py-0 leading-none">PERM</span>
                    @endif
                    @if($staff->es_refuerzo)
                        <span class="text-[8px] font-bold uppercase tracking-wider text-emerald-300 bg-emerald-500/30 border border-emerald-400/30 rounded px-1 py-0 leading-none">REF</span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[10px] font-medium text-white/70 uppercase tracking-wider">{{ $serviceLabel }}</span>
                    <span class="text-white/40 text-[10px]">·</span>
                    <span class="text-[10px] font-medium text-white/70 uppercase tracking-wider">{{ $staff->numero_portatil ?: '—' }}</span>
                </div>
            </div>

            @if($bedNum !== null)
                <div class="absolute top-1 left-1 bg-slate-900/80 backdrop-blur-sm border border-slate-600 rounded-md px-1.5 py-0.5 text-[9px] font-bold text-slate-100 leading-none whitespace-nowrap">
                    🛏 #{{ $bedNum }}
                </div>
            @endif

            <div class="absolute top-1 right-1 flex flex-col gap-1">
                @if($staff->es_conductor)
                    <span class="w-5 h-5 rounded-full bg-sky-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-sky-300 shadow-sm" title="Conductor">
                        <i class="fas fa-car text-[9px]"></i>
                    </span>
                @endif
                @if($staff->es_operador_rescate)
                    <span class="w-5 h-5 rounded-full bg-amber-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-amber-300 shadow-sm" title="Operador de Rescate">R</span>
                @endif
                @if($staff->es_asistente_trauma)
                    <span class="w-5 h-5 rounded-full bg-rose-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-rose-300 shadow-sm px-0.5" title="Asistente de Trauma">AT</span>
                @endif
            </div>
        </div>

        {{-- Info de reemplazo --}}
        @if($repAsReplacement)
            <div class="mt-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-2 text-purple-800">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-purple-700">Reemplaza a</div>
                <div class="text-sm font-medium text-slate-800">
                    {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <div class="mt-1">
                    <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="w-full bg-white dark:bg-slate-900 hover:bg-purple-50 text-purple-700 font-medium uppercase text-[10px] py-1.5 rounded-lg border border-purple-200">
                        Deshacer reemplazo
                    </button>
                </div>
            </div>
        @endif

        @if($repAsOriginal)
            <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-2 text-amber-800">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Reemplazado por</div>
                <div class="text-sm font-medium text-slate-800">
                    {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <div class="mt-1">
                    <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="w-full bg-white dark:bg-slate-900 hover:bg-amber-100 text-amber-800 font-medium uppercase text-[10px] py-1.5 rounded-lg border border-amber-200">
                        Deshacer reemplazo
                    </button>
                </div>
            </div>
        @endif

        {{-- Confirmación --}}
        @if($showConfirmBox)
            <div class="mt-1.5">
                <div id="confirm-box-wrap-{{ $staff->id }}">
                    <div id="confirm-box-{{ $staff->id }}" class="mb-2 rounded-xl border border-slate-800 bg-slate-950 px-2.5 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <div id="confirm-status-{{ $staff->id }}" class="text-[9px] font-black uppercase tracking-widest text-rose-200">NO CONFIRMADO</div>
                        </div>
                        <div id="confirm-controls-{{ $staff->id }}" class="mt-1.5 flex items-center gap-2">
                            <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="Código" class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-100 placeholder:text-slate-600 dark:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }});}" />
                            <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-100 text-[9px] font-black uppercase tracking-widest border border-slate-700">Confirmar</button>
                        </div>
                        <div id="confirm-msg-{{ $staff->id }}" class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Estado --}}
        @unless($hideStatusSection)
            <div class="mt-1.5">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Estado</label>
                @if($lockAttendanceStatus)
                    @if($repAsReplacement)
                        <div class="rounded-lg border border-purple-500/30 bg-purple-500/15 text-purple-200 px-3 py-2 text-center">
                            <div class="text-sm font-bold">REEMPLAZO</div>
                        </div>
                    @else
                        <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/15 text-emerald-200 px-3 py-2 text-center">
                            <div class="text-sm font-bold">CONSTITUYE</div>
                        </div>
                    @endif
                @else
                    <button type="button" id="status-cycle-{{ $staff->id }}" data-user-id="{{ $staff->id }}" data-status="{{ $status }}" onclick="cycleGuardiaStatus('{{ $staff->id }}')" class="w-full px-2 py-2 rounded-lg border text-[11px] font-bold uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-sm {{ $statusBtnClass }}">
                        <span id="status-cycle-label-{{ $staff->id }}">{{ $statusLabel }}</span>
                        <i class="fas fa-rotate text-[10px] opacity-80"></i>
                    </button>
                @endif
            </div>
        @endunless

        {{-- Acciones --}}
        <div class="mt-1.5 flex flex-col gap-1.5">
            @if($canReplace)
                <button
                    type="button"
                    data-open-replacement="1"
                    data-original-firefighter-id="{{ $staff->id }}"
                    data-original-user-name="{{ $staff->nombres }} {{ $staff->apellido_paterno }}"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold uppercase tracking-wider text-[10px] py-1.5 rounded-lg flex items-center justify-center gap-2"
                >
                    <i class="fas fa-user-plus"></i>
                    Reemplazar
                </button>
            @endif

            @if($staff->es_refuerzo)
                <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="w-full bg-slate-950 hover:bg-slate-900 text-slate-100 font-bold uppercase tracking-wider text-[10px] py-1.5 rounded-lg border border-slate-800">
                    Quitar refuerzo
                </button>
            @endif
        </div>
    </div>
</div>
