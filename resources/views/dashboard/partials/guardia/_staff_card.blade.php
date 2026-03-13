{{-- Tarjeta individual de personal - Diseño Ficha Operativa --}}
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
        'constituye' => 'border-emerald-600/50',
        'reemplazo' => 'border-purple-600/50',
        'permiso' => 'border-amber-600/50',
        'licencia' => 'border-blue-600/50',
        'falta' => 'border-rose-600/50',
        default => 'border-slate-700/50',
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
     class="group bg-slate-900 rounded-xl border-2 {{ $cardBorderClass }} overflow-hidden shadow-lg hover:shadow-xl transition-all duration-200" 
     data-card-user="{{ $staff->id }}" 
     data-requires-confirmation="{{ $requiresConfirmation ? '1' : '0' }}" 
     data-is-confirmed="0">
    
    {{-- Header compacto con apellido y acciones --}}
    <div class="bg-slate-800/80 px-3 py-2 flex items-center justify-between border-b border-slate-700/50" id="card-header-{{ $staff->id }}">
        <div class="flex items-center gap-2 min-w-0">
            {{-- Indicador de estado --}}
            <div class="w-2.5 h-2.5 rounded-full {{ $statusIndicatorClass }} shrink-0"></div>
            <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="text-sm font-bold text-white truncate hover:text-blue-400 transition-colors" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                {{ strtoupper($staff->apellido_paterno ?: $staff->nombres) }}
            </a>
            @if($staff->es_jefe_guardia)
                <span class="shrink-0 w-5 h-5 rounded bg-amber-500/30 flex items-center justify-center" title="Jefe de Guardia">
                    <i class="fas fa-star text-[8px] text-amber-400"></i>
                </span>
            @endif
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            @if($canInhabilitar)
                <button type="button" onclick="toggleInhabilitado('{{ $staff->id }}')" class="px-2 py-1 rounded text-[9px] font-semibold uppercase bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                    Inhab.
                </button>
            @endif
            <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="w-6 h-6 rounded bg-slate-700 hover:bg-slate-600 flex items-center justify-center transition-colors" title="Ver perfil">
                <i class="fas fa-external-link-alt text-[9px] text-slate-400"></i>
            </a>
        </div>
    </div>

    {{-- Cuerpo principal: Layout horizontal foto + datos --}}
    <div class="p-3">
        <div class="flex gap-3">
            {{-- Foto contenida (cuadrada, tamaño fijo) --}}
            <a href="{{ route('admin.volunteers.show', $staff->id) }}" class="shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-slate-800 border border-slate-700 block hover:border-blue-500/50 transition-colors">
                @if($staff->photo_path)
                    <img src="{{ route('media', $staff->photo_path) }}" class="w-full h-full object-cover" alt="Foto">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800">
                        <span class="text-xl font-bold text-slate-500">{{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}</span>
                    </div>
                @endif
            </a>

            {{-- Datos del bombero --}}
            <div class="flex-1 min-w-0">
                {{-- Nombre completo --}}
                <div class="text-sm font-semibold text-white leading-tight truncate" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                    {{ $staff->nombres }}
                </div>
                
                {{-- Cargo --}}
                <div class="text-xs text-slate-400 mt-0.5 truncate">
                    {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
                </div>

                {{-- Info adicional --}}
                <div class="flex items-center gap-2 mt-1.5 text-[10px] text-slate-500">
                    <span>{{ $serviceLabel }}</span>
                    @if($staff->numero_portatil)
                        <span class="text-slate-600">•</span>
                        <span>{{ $staff->numero_portatil }}</span>
                    @endif
                </div>

                {{-- Badges en línea --}}
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    @if($staff->es_permanente)
                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">PERM</span>
                    @endif
                    @if($staff->es_refuerzo)
                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-sky-500/20 text-sky-400 border border-sky-500/30">REF</span>
                    @endif
                    @if($bedNum !== null)
                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 border border-slate-600">
                            <i class="fas fa-bed mr-0.5"></i>#{{ $bedNum }}
                        </span>
                    @endif
                    @if($staff->es_conductor)
                        <span class="w-5 h-5 rounded bg-sky-500/80 flex items-center justify-center" title="Conductor">
                            <i class="fas fa-car text-[9px] text-white"></i>
                        </span>
                    @endif
                    @if($staff->es_operador_rescate)
                        <span class="w-5 h-5 rounded bg-amber-500/80 flex items-center justify-center" title="Operador de Rescate">
                            <span class="text-[9px] font-bold text-white">R</span>
                        </span>
                    @endif
                    @if($staff->es_asistente_trauma)
                        <span class="w-5 h-5 rounded bg-rose-500/80 flex items-center justify-center" title="Asistente de Trauma">
                            <span class="text-[8px] font-bold text-white">AT</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info de reemplazo --}}
        @if($repAsReplacement)
            <div class="mt-3 rounded-lg border border-purple-500/30 bg-purple-500/10 px-3 py-2">
                <div class="text-[9px] font-bold uppercase tracking-wider text-purple-400">Reemplaza a</div>
                <div class="text-xs font-semibold text-purple-200 mt-0.5">
                    {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="mt-2 w-full bg-purple-600/30 hover:bg-purple-600/50 text-purple-300 font-semibold uppercase text-[9px] py-1.5 rounded border border-purple-500/30 transition-colors">
                    Deshacer
                </button>
            </div>
        @endif

        @if($repAsOriginal)
            <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2">
                <div class="text-[9px] font-bold uppercase tracking-wider text-amber-400">Reemplazado por</div>
                <div class="text-xs font-semibold text-amber-200 mt-0.5">
                    {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                </div>
                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="mt-2 w-full bg-amber-600/30 hover:bg-amber-600/50 text-amber-300 font-semibold uppercase text-[9px] py-1.5 rounded border border-amber-500/30 transition-colors">
                    Deshacer
                </button>
            </div>
        @endif

        {{-- Confirmación --}}
        @if($showConfirmBox)
            <div class="mt-3">
                <div id="confirm-box-wrap-{{ $staff->id }}">
                    <div id="confirm-box-{{ $staff->id }}" class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2">
                        <div id="confirm-status-{{ $staff->id }}" class="text-[9px] font-bold uppercase tracking-wider text-rose-400">No Confirmado</div>
                        <div id="confirm-controls-{{ $staff->id }}" class="mt-1.5 flex items-center gap-2">
                            <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="Código" class="flex-1 min-w-0 px-2.5 py-1.5 rounded border border-slate-700 bg-slate-800/80 text-xs font-semibold text-white placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-rose-500/50 focus:border-rose-500/50" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }});}" />
                            <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-3 py-1.5 rounded bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-bold uppercase transition-colors">OK</button>
                        </div>
                        <div id="confirm-msg-{{ $staff->id }}" class="mt-1 text-[10px] font-medium text-slate-400"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Estado --}}
        @unless($hideStatusSection)
            <div class="mt-3">
                <label class="block text-[9px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Estado</label>
                @if($lockAttendanceStatus)
                    @if($repAsReplacement)
                        <div class="rounded-lg border border-purple-500/40 bg-purple-500/20 text-purple-300 px-3 py-2 text-center">
                            <span class="text-xs font-bold">REEMPLAZO</span>
                        </div>
                    @else
                        <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/20 text-emerald-300 px-3 py-2 text-center">
                            <span class="text-xs font-bold">CONSTITUYE</span>
                        </div>
                    @endif
                @else
                    <button type="button" id="status-cycle-{{ $staff->id }}" data-user-id="{{ $staff->id }}" data-status="{{ $status }}" onclick="cycleGuardiaStatus('{{ $staff->id }}')" class="w-full px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-2 {{ $statusBtnClass }}">
                        <span id="status-cycle-label-{{ $staff->id }}">{{ $statusLabel }}</span>
                        <i class="fas fa-rotate text-[9px] opacity-70"></i>
                    </button>
                @endif
            </div>
        @endunless

        {{-- Acciones --}}
        <div class="mt-3 flex flex-col gap-2">
            @if($canReplace)
                <button
                    type="button"
                    data-open-replacement="1"
                    data-original-firefighter-id="{{ $staff->id }}"
                    data-original-user-name="{{ $staff->nombres }} {{ $staff->apellido_paterno }}"
                    class="w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold uppercase tracking-wider text-[10px] py-2 rounded-lg flex items-center justify-center gap-2 transition-colors"
                >
                    <i class="fas fa-user-plus text-[9px]"></i>
                    Reemplazar
                </button>
            @endif

            @if($staff->es_refuerzo)
                <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold uppercase tracking-wider text-[10px] py-2 rounded-lg border border-slate-700 transition-colors">
                    Quitar refuerzo
                </button>
            @endif
        </div>
    </div>
</div>
