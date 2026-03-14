{{-- Tarjeta individual de personal - Diseño Compacto Operativo --}}
@php
    $repAsReplacement = $replacementByReplacement->get($staff->id);
    $repAsOriginal = $replacementByOriginal->get($staff->id);
    $status = $repAsReplacement ? 'reemplazo' : $staff->estado_asistencia;
    $lockAttendanceStatus = (bool) ($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    $requiresConfirmation = in_array($status, ['constituye', 'reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement;
    $showConfirmBox = $requiresConfirmation;
    $hideStatusSection = (bool) $repAsReplacement;
    $canReplace = !($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
    
    // Clases del borde según estado
    $cardBorderClass = match ($status) {
        'constituye' => 'border-emerald-500/60',
        'reemplazo' => 'border-purple-500/60',
        'permiso' => 'border-amber-500/60',
        'licencia' => 'border-blue-500/60',
        'falta' => 'border-rose-500/60',
        default => 'border-slate-600/50',
    };
    
    // Color del indicador de estado
    $statusIndicatorClass = match ($status) {
        'constituye' => 'bg-emerald-500',
        'reemplazo' => 'bg-purple-500',
        'permiso' => 'bg-amber-500',
        'licencia' => 'bg-blue-500',
        'falta' => 'bg-rose-500',
        default => 'bg-slate-500',
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
        'constituye' => 'bg-emerald-600 hover:bg-emerald-500 text-white',
        'reemplazo' => 'bg-purple-600 hover:bg-purple-500 text-white',
        'permiso' => 'bg-amber-600 hover:bg-amber-500 text-white',
        'ausente' => 'bg-slate-600 hover:bg-slate-500 text-white',
        'licencia' => 'bg-blue-600 hover:bg-blue-500 text-white',
        'falta' => 'bg-rose-600 hover:bg-rose-500 text-white',
        default => 'bg-emerald-600 hover:bg-emerald-500 text-white',
    };
    
    $serviceLabel = $staff->service_label ?? '—';
    $bedNum = $bedByFirefighter[$staff->id] ?? null;
@endphp

<input type="hidden" name="users[{{ $staff->id }}][estado_asistencia]" id="attendance-status-{{ $staff->id }}" value="{{ $status }}">
<input type="hidden" name="users[{{ $staff->id }}][confirm_token]" id="confirm-token-{{ $staff->id }}" value="">

<div id="guardia-card-{{ $staff->id }}" 
     class="group bg-slate-900 rounded-lg border {{ $cardBorderClass }} overflow-hidden shadow-md hover:shadow-lg transition-all duration-200" 
     data-card-user="{{ $staff->id }}" 
     data-requires-confirmation="{{ $requiresConfirmation ? '1' : '0' }}" 
     data-is-confirmed="0">
    
    {{-- Bloque de imagen prominente con overlay --}}
    <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="relative block w-full h-32 bg-slate-800 overflow-hidden group/foto">
        @if($staff->photo_path)
            <img src="{{ route('media', $staff->photo_path) }}" class="w-full h-full object-cover object-top group-hover/foto:scale-105 transition-transform duration-300" alt="Foto">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800">
                <span class="text-3xl font-bold text-slate-500">{{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}</span>
            </div>
        @endif
        
        {{-- Gradient overlay inferior --}}
        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent"></div>
        
        {{-- Nombre sobre la imagen --}}
        <div class="absolute inset-x-0 bottom-0 px-2 pb-1.5">
            <div class="text-xs font-bold text-white leading-tight truncate drop-shadow-lg">
                {{ $staff->nombres }} {{ $staff->apellido_paterno }}
            </div>
            <div class="text-[10px] text-white/70 truncate">
                {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
            </div>
        </div>
        
        {{-- Badges esquina superior derecha --}}
        <div class="absolute top-1.5 right-1.5 flex flex-col gap-1">
            @if($staff->es_conductor)
                <span class="w-5 h-5 rounded bg-sky-500/90 flex items-center justify-center shadow" title="Conductor">
                    <i class="fas fa-car text-[8px] text-white"></i>
                </span>
            @endif
            @if($staff->es_operador_rescate)
                <span class="w-5 h-5 rounded bg-amber-500/90 flex items-center justify-center shadow" title="Rescate">
                    <span class="text-[8px] font-bold text-white">R</span>
                </span>
            @endif
            @if($staff->es_asistente_trauma)
                <span class="w-5 h-5 rounded bg-rose-500/90 flex items-center justify-center shadow" title="Trauma">
                    <span class="text-[7px] font-bold text-white">AT</span>
                </span>
            @endif
        </div>
        
        {{-- Badge cama esquina superior izquierda --}}
        @if($bedNum !== null)
            <div class="absolute top-1.5 left-1.5 flex items-center gap-1 bg-slate-900/80 backdrop-blur-sm rounded px-1.5 py-0.5">
                <i class="fas fa-bed text-[8px] text-slate-400"></i>
                <span class="text-[9px] font-bold text-white">#{{ $bedNum }}</span>
            </div>
        @endif
        
        {{-- Indicador jefe de guardia --}}
        @if($staff->es_jefe_guardia)
            <div class="absolute top-1.5 left-1.5 {{ $bedNum !== null ? 'top-7' : '' }}">
                <span class="w-5 h-5 rounded bg-amber-500/90 flex items-center justify-center shadow" title="Jefe de Guardia">
                    <i class="fas fa-star text-[8px] text-white"></i>
                </span>
            </div>
        @endif
    </a>

    {{-- Cuerpo compacto --}}
    <div class="p-2">
        {{-- Badges de estado inline --}}
        <div class="flex items-center gap-1 mb-2 flex-wrap">
            @if($staff->es_permanente)
                <span class="text-[8px] font-bold uppercase px-1 py-0.5 rounded bg-emerald-500/20 text-emerald-400">PERM</span>
            @endif
            @if($staff->es_refuerzo)
                <span class="text-[8px] font-bold uppercase px-1 py-0.5 rounded bg-sky-500/20 text-sky-400">REF</span>
            @endif
            <span class="text-[9px] text-slate-500 ml-auto">{{ $serviceLabel }}</span>
        </div>

        {{-- Info de reemplazo compacta --}}
        @if($repAsReplacement)
            <div class="mb-2 rounded border border-purple-500/30 bg-purple-500/10 px-2 py-1.5">
                <div class="text-[8px] font-bold uppercase text-purple-400">Reemplaza a</div>
                <div class="text-[10px] font-semibold text-purple-200">
                    {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="mt-1 w-full bg-purple-600/30 hover:bg-purple-600/50 text-purple-300 font-semibold uppercase text-[8px] py-1 rounded transition-colors">
                    Deshacer
                </button>
            </div>
        @endif

        @if($repAsOriginal)
            <div class="mb-2 rounded border border-amber-500/30 bg-amber-500/10 px-2 py-1.5">
                <div class="text-[8px] font-bold uppercase text-amber-400">Reemplazado por</div>
                <div class="text-[10px] font-semibold text-amber-200">
                    {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="mt-1 w-full bg-amber-600/30 hover:bg-amber-600/50 text-amber-300 font-semibold uppercase text-[8px] py-1 rounded transition-colors">
                    Deshacer
                </button>
            </div>
        @endif

        {{-- Confirmación compacta --}}
        @if($showConfirmBox)
            <div id="confirm-box-wrap-{{ $staff->id }}" class="mb-2">
                <div id="confirm-box-{{ $staff->id }}" class="rounded border border-rose-500/30 bg-rose-500/10 px-2 py-1.5">
                    <div id="confirm-status-{{ $staff->id }}" class="text-[8px] font-bold uppercase text-rose-400">No Confirmado</div>
                    <div id="confirm-controls-{{ $staff->id }}" class="mt-1 flex items-center gap-1.5">
                        <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="N° Registro" class="flex-1 min-w-0 px-2 py-1 rounded border border-slate-700 bg-slate-800/80 text-[10px] font-semibold text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-rose-500/50" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }});}" />
                        <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-2 py-1 rounded bg-rose-600 hover:bg-rose-500 text-white text-[9px] font-bold uppercase transition-colors">OK</button>
                    </div>
                    <div id="confirm-msg-{{ $staff->id }}" class="mt-0.5 text-[9px] font-medium text-slate-400"></div>
                </div>
            </div>
        @endif

        {{-- Estado --}}
        @unless($hideStatusSection)
            <div class="mb-2">
                @if($lockAttendanceStatus)
                    @if($repAsReplacement)
                        <div class="rounded border border-purple-500/40 bg-purple-500/20 text-purple-300 px-2 py-1.5 text-center">
                            <span class="text-[10px] font-bold">REEMPLAZO</span>
                        </div>
                    @else
                        <div class="rounded border border-emerald-500/40 bg-emerald-500/20 text-emerald-300 px-2 py-1.5 text-center">
                            <span class="text-[10px] font-bold">CONSTITUYE</span>
                        </div>
                    @endif
                @else
                    <button type="button" id="status-cycle-{{ $staff->id }}" data-user-id="{{ $staff->id }}" data-status="{{ $status }}" onclick="cycleGuardiaStatus('{{ $staff->id }}')" class="w-full px-2 py-1.5 rounded text-[10px] font-bold uppercase tracking-wide transition-all flex items-center justify-center gap-1.5 {{ $statusBtnClass }}">
                        <span id="status-cycle-label-{{ $staff->id }}">{{ $statusLabel }}</span>
                        <i class="fas fa-rotate text-[8px] opacity-70"></i>
                    </button>
                @endif
            </div>
        @endunless

        {{-- Acciones --}}
        <div class="flex flex-col gap-1.5">
            @if($canReplace)
                <button
                    type="button"
                    data-open-replacement="1"
                    data-original-firefighter-id="{{ $staff->id }}"
                    data-original-user-name="{{ $staff->nombres }} {{ $staff->apellido_paterno }}"
                    class="w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold uppercase tracking-wide text-[9px] py-1.5 rounded flex items-center justify-center gap-1.5 transition-colors"
                >
                    <i class="fas fa-user-plus text-[8px]"></i>
                    Reemplazar
                </button>
            @endif

            @if($staff->es_refuerzo)
                <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold uppercase tracking-wide text-[9px] py-1.5 rounded border border-slate-700 transition-colors">
                    Quitar refuerzo
                </button>
            @endif
            
            @if($canInhabilitar && !$canReplace && !$staff->es_refuerzo)
                <button type="button" onclick="toggleInhabilitado('{{ $staff->id }}')" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-400 font-semibold uppercase tracking-wide text-[9px] py-1.5 rounded border border-slate-700 transition-colors">
                    Inhabilitar
                </button>
            @endif
        </div>
    </div>
</div>
