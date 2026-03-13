{{-- Tarjeta individual de personal - Diseño Premium --}}
@php
    $repAsReplacement = $replacementByReplacement->get($staff->id);
    $repAsOriginal = $replacementByOriginal->get($staff->id);
    $status = $repAsReplacement ? 'reemplazo' : $staff->estado_asistencia;
    $lockAttendanceStatus = (bool) ($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    $requiresConfirmation = in_array($status, ['constituye', 'reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement;
    $showConfirmBox = $requiresConfirmation;
    $hideStatusSection = (bool) $repAsReplacement;
    $canReplace = !($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    
    // Clases del header según estado
    $statusHeaderClass = match ($status) {
        'constituye' => 'bg-gradient-to-r from-emerald-900/60 to-emerald-950/40 border-b border-emerald-700/30',
        'reemplazo' => 'bg-gradient-to-r from-purple-900/60 to-purple-950/40 border-b border-purple-700/30',
        'permiso' => 'bg-gradient-to-r from-amber-900/50 to-amber-950/30 border-b border-amber-700/30',
        'ausente' => 'bg-gradient-to-r from-slate-800/80 to-slate-900/60 border-b border-slate-700/30',
        'licencia' => 'bg-gradient-to-r from-blue-900/60 to-blue-950/40 border-b border-blue-700/30',
        'falta' => 'bg-gradient-to-r from-rose-900/60 to-rose-950/40 border-b border-rose-700/30',
        default => 'bg-gradient-to-r from-slate-800/80 to-slate-900/60 border-b border-slate-700/30',
    };
    
    // Borde de la card según estado
    $cardBorderClass = match ($status) {
        'constituye' => 'border-emerald-700/40 hover:border-emerald-600/60',
        'reemplazo' => 'border-purple-700/40 hover:border-purple-600/60',
        'permiso' => 'border-amber-700/40 hover:border-amber-600/60',
        'licencia' => 'border-blue-700/40 hover:border-blue-600/60',
        'falta' => 'border-rose-700/40 hover:border-rose-600/60',
        default => 'border-slate-700/50 hover:border-slate-600/60',
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
     class="group bg-gradient-to-b from-slate-800/90 to-slate-900 rounded-2xl border {{ $cardBorderClass }} overflow-hidden flex flex-col shadow-lg shadow-black/20 hover:shadow-xl hover:shadow-black/30 transition-all duration-300 card-hover h-[380px]" 
     data-card-user="{{ $staff->id }}" 
     data-requires-confirmation="{{ $requiresConfirmation ? '1' : '0' }}" 
     data-is-confirmed="0">
    
    {{-- Header con gradiente y enlace al perfil --}}
    <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="{{ $statusHeaderClass }} text-white px-3 py-2.5 flex items-center justify-between hover:bg-white/5 transition-colors cursor-pointer" id="card-header-{{ $staff->id }}">
        <div class="min-w-0 flex-1">
            <div class="text-sm font-bold text-white leading-tight tracking-wide" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                {{ strtoupper($staff->apellido_paterno ?: $staff->nombres) }}
            </div>
            @if($staff->es_jefe_guardia)
                <div class="flex items-center gap-1.5 mt-0.5">
                    <div class="w-4 h-4 rounded bg-amber-500/30 flex items-center justify-center">
                        <i class="fas fa-star text-[8px] text-amber-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-amber-300">Jefe de Guardia</span>
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-external-link-alt text-[10px] text-white/50"></i>
            @if($canInhabilitar)
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleInhabilitado('{{ $staff->id }}')" class="h-7 px-2.5 rounded-lg border border-slate-600/50 bg-slate-800/80 hover:bg-slate-700 text-[10px] font-semibold uppercase tracking-wider text-slate-300 transition-colors">
                    Inhabilitar
                </button>
            @endif
        </div>
    </a>

    <div class="p-2.5 flex-1 flex flex-col min-h-0 overflow-hidden">
        {{-- Foto con overlay mejorado y enlace al perfil --}}
        <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="relative bg-slate-950 rounded-xl border border-slate-700/50 overflow-hidden w-full h-[180px] mb-3 shrink-0 shadow-inner block group/foto cursor-pointer">
            @if($staff->photo_path)
                <img src="{{ route('media', $staff->photo_path) }}" class="w-full h-full object-cover object-center group-hover/foto:scale-105 transition-transform duration-500" alt="Foto">
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                    <span class="text-4xl font-bold text-slate-500">{{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}</span>
                </div>
            @endif
            
            {{-- Icono de ver perfil en hover --}}
            <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover/foto:opacity-100 transition-opacity">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas fa-external-link-alt text-white text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-white">Ver Perfil</span>
                </div>
            </div>

            {{-- Gradient overlay más pronunciado --}}
            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black via-black/70 to-transparent"></div>

            {{-- Info sobre la foto --}}
            <div class="absolute inset-x-0 bottom-0 p-3">
                <div class="text-sm font-semibold text-white leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                    {{ $staff->nombres }} {{ $staff->apellido_paterno }}
                </div>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="text-xs font-medium text-white/80 uppercase tracking-wide">
                        {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
                    </span>
                    @if($staff->es_permanente)
                        <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-300 bg-emerald-500/40 border border-emerald-400/40 rounded-md px-1.5 py-0.5">PERM</span>
                    @endif
                    @if($staff->es_refuerzo)
                        <span class="text-[9px] font-bold uppercase tracking-wider text-sky-300 bg-sky-500/40 border border-sky-400/40 rounded-md px-1.5 py-0.5">REF</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-medium text-white/60">{{ $serviceLabel }}</span>
                    <span class="text-white/30">•</span>
                    <span class="text-xs font-medium text-white/60">{{ $staff->numero_portatil ?: '—' }}</span>
                </div>
            </div>

            {{-- Badge cama --}}
            @if($bedNum !== null)
                <div class="absolute top-2 left-2 flex items-center gap-1.5 bg-slate-900/90 backdrop-blur-sm border border-slate-600/50 rounded-lg px-2 py-1">
                    <i class="fas fa-bed text-[10px] text-slate-400"></i>
                    <span class="text-[10px] font-bold text-slate-200">#{{ $bedNum }}</span>
                </div>
            @endif

            {{-- Badges especialidades --}}
            <div class="absolute top-2 right-2 flex flex-col gap-1.5">
                @if($staff->es_conductor)
                    <div class="w-7 h-7 rounded-lg bg-sky-500/90 flex items-center justify-center shadow-lg shadow-sky-500/30" title="Conductor">
                        <i class="fas fa-car text-xs text-white"></i>
                    </div>
                @endif
                @if($staff->es_operador_rescate)
                    <div class="w-7 h-7 rounded-lg bg-amber-500/90 flex items-center justify-center shadow-lg shadow-amber-500/30" title="Operador de Rescate">
                        <span class="text-xs font-bold text-white">R</span>
                    </div>
                @endif
                @if($staff->es_asistente_trauma)
                    <div class="w-7 h-7 rounded-lg bg-rose-500/90 flex items-center justify-center shadow-lg shadow-rose-500/30" title="Asistente de Trauma">
                        <span class="text-[10px] font-bold text-white">AT</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info de reemplazo - Estilo oscuro --}}
        @if($repAsReplacement)
            <div class="rounded-xl border border-purple-500/30 bg-purple-500/10 px-3 py-2.5">
                <div class="text-[10px] font-bold uppercase tracking-wider text-purple-400">Reemplaza a</div>
                <div class="text-sm font-semibold text-purple-200 mt-0.5">
                    {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="mt-2 w-full bg-purple-600/30 hover:bg-purple-600/50 text-purple-300 font-semibold uppercase text-[10px] py-2 rounded-lg border border-purple-500/30 transition-colors">
                    Deshacer reemplazo
                </button>
            </div>
        @endif

        @if($repAsOriginal)
            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-3 py-2.5">
                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Reemplazado por</div>
                <div class="text-sm font-semibold text-amber-200 mt-0.5">
                    {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="mt-2 w-full bg-amber-600/30 hover:bg-amber-600/50 text-amber-300 font-semibold uppercase text-[10px] py-2 rounded-lg border border-amber-500/30 transition-colors">
                    Deshacer reemplazo
                </button>
            </div>
        @endif

        {{-- Confirmación - Estilo mejorado --}}
        @if($showConfirmBox)
            <div class="mt-2">
                <div id="confirm-box-wrap-{{ $staff->id }}">
                    <div id="confirm-box-{{ $staff->id }}" class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2.5">
                        <div id="confirm-status-{{ $staff->id }}" class="text-[10px] font-bold uppercase tracking-wider text-rose-400">No Confirmado</div>
                        <div id="confirm-controls-{{ $staff->id }}" class="mt-2 flex items-center gap-2">
                            <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="Código" class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-slate-700 bg-slate-800/80 text-sm font-semibold text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500/50" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }});}" />
                            <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-3 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold uppercase tracking-wider transition-colors">OK</button>
                        </div>
                        <div id="confirm-msg-{{ $staff->id }}" class="mt-1.5 text-xs font-semibold text-slate-400"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Estado - Botones mejorados --}}
        @unless($hideStatusSection)
            <div class="mt-3">
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Estado</label>
                @if($lockAttendanceStatus)
                    @if($repAsReplacement)
                        <div class="rounded-xl border border-purple-500/40 bg-purple-500/20 text-purple-300 px-4 py-2.5 text-center shadow-inner">
                            <div class="text-sm font-bold">REEMPLAZO</div>
                        </div>
                    @else
                        <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/20 text-emerald-300 px-4 py-2.5 text-center shadow-inner">
                            <div class="text-sm font-bold">CONSTITUYE</div>
                        </div>
                    @endif
                @else
                    <button type="button" id="status-cycle-{{ $staff->id }}" data-user-id="{{ $staff->id }}" data-status="{{ $status }}" onclick="cycleGuardiaStatus('{{ $staff->id }}')" class="w-full px-3 py-2.5 rounded-xl border text-xs font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg {{ $statusBtnClass }}">
                        <span id="status-cycle-label-{{ $staff->id }}">{{ $statusLabel }}</span>
                        <i class="fas fa-rotate text-[10px] opacity-70"></i>
                    </button>
                @endif
            </div>
        @endunless

        {{-- Acciones - Botones mejorados --}}
        <div class="mt-3 flex flex-col gap-2">
            @if($canReplace)
                <button
                    type="button"
                    data-open-replacement="1"
                    data-original-firefighter-id="{{ $staff->id }}"
                    data-original-user-name="{{ $staff->nombres }} {{ $staff->apellido_paterno }}"
                    class="w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold uppercase tracking-wider text-xs py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-purple-500/20 transition-all"
                >
                    <i class="fas fa-user-plus text-[10px]"></i>
                    Reemplazar
                </button>
            @endif

            @if($staff->es_refuerzo)
                <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold uppercase tracking-wider text-xs py-2.5 rounded-xl border border-slate-700 transition-colors">
                    Quitar refuerzo
                </button>
            @endif
        </div>
    </div>
</div>
