@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Administración de Guardias" subtitle="Gestión de equipos y asistencia" icon="fas fa-shield" iconVariant="red">
        @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
            <form action="{{ route('admin.guardias.store') }}" method="POST" class="flex gap-2 w-full md:w-auto">
                @csrf
                <input type="text" name="name" placeholder="Nombre nueva guardia..." required
                    class="form-input w-full md:w-64 uppercase"
                    oninput="this.value = this.value.toUpperCase();"
                    @if(isset($limitData) && !$limitData['can_create']) disabled @endif>
                <x-ui.button type="submit" variant="success" size="md" icon="fas fa-plus" :disabled="isset($limitData) && !$limitData['can_create']">
                    Crear
                </x-ui.button>
            </form>
        @endif
    </x-ui.page-header>

    @if(isset($limitData) && !$limitData['can_create'])
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <p class="font-bold">{{ $limitData['message'] }}</p>
            </div>
        </div>
    @endif

    <!-- Modal de Asignación de Refuerzo -->
    <div id="refuerzoModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 border border-slate-200">
            <div class="flex justify-between items-start p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1e293b]">Agregar Refuerzo</h3>
                    <p class="text-sm text-[#475569] mt-1">Agrega un voluntario provisorio a esta guardia (se libera automáticamente a las 10:00 AM).</p>
                </div>
                <button type="button" onclick="closeRefuerzoModal()" class="text-[#475569] hover:text-[#1e293b] transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.guardias.refuerzo') }}" method="POST">
                @csrf
                <input type="hidden" name="guardia_id" id="modal_refuerzo_guardia_id">

                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Voluntario</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-xs"></i>
                            <input list="modal_refuerzo_volunteers_list" name="firefighter_id_display" autocomplete="off"
                                class="form-input pl-9"
                                placeholder="Buscar voluntario..." required
                                oninput="updateModalRefuerzoId(this)">
                            <input type="hidden" name="firefighter_id" id="modal_refuerzo_firefighter_id" required>
                        </div>
                        <datalist id="modal_refuerzo_volunteers_list">
                            @foreach($volunteers as $volunteer)
                                <option data-value="{{ $volunteer->id }}" value="{{ trim($volunteer->nombres . ' ' . $volunteer->apellido_paterno . ' ' . ($volunteer->apellido_materno ?? '') . ($volunteer->rut ? ' - ' . $volunteer->rut : '')) }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-200">
                        <x-ui.button type="button" variant="secondary" size="md" onclick="closeRefuerzoModal()" class="w-1/2">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-check" class="w-1/2">
                            Confirmar
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('new_credentials'))
        @php $creds = session('new_credentials'); @endphp
        <div class="mb-6 p-5 bg-emerald-50 border-2 border-emerald-400 rounded-2xl">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-key text-emerald-700"></i>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-emerald-800 text-sm mb-0.5">Credenciales de acceso generadas para <span class="uppercase">{{ $creds['guardia'] }}</span></p>
                    <p class="text-xs text-emerald-700 mb-3">Guarda estas credenciales ahora. No se volverán a mostrar a menos que las regeneres.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-white rounded-xl px-4 py-3 border border-emerald-300">
                            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">Usuario</p>
                            <p class="font-mono font-bold text-slate-900 text-sm select-all">{{ $creds['username'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl px-4 py-3 border border-emerald-300">
                            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">Contraseña</p>
                            <p class="font-mono font-bold text-slate-900 text-sm select-all">{{ $creds['password'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    @if($errors->any())
        <x-ui.alert type="danger" icon="fas fa-exclamation-triangle" class="mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 overflow-y-auto max-h-[650px] pr-2 flex-grow p-3 items-start">
        @foreach($guardias as $guardia)
            @php
                $isActiveWeek = isset($activeGuardia) && $activeGuardia && (int) $activeGuardia->id === (int) $guardia->id;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-all w-full">
                <!-- Header Guardia -->
                <div class="relative overflow-hidden bg-slate-900 text-white p-5 flex justify-between items-center border-b border-slate-800 {{ $isActiveWeek ? 'ring-2 ring-green-500 ring-offset-2 ring-offset-white' : '' }}">
                    @if($isActiveWeek)
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-green-500 transform rotate-45 z-0"></div>
                        <div class="absolute top-1 right-1 text-green-900 z-10">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                    @endif
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold tracking-tight uppercase text-white leading-none">{{ $guardia->name }}</h2>
                            @if($isActiveWeek)
                                <span class="bg-green-500/20 text-green-400 border border-green-500/50 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-widest shadow-sm backdrop-blur-sm">
                                    En Turno
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs mt-1 font-medium flex items-center">
                            <i class="fas fa-users mr-2 opacity-50"></i> {{ $guardia->bomberos->count() }} Voluntarios
                        </p>
                    </div>

                    <div class="flex items-center gap-1 relative z-10 bg-slate-800/80 p-1 rounded-lg backdrop-blur-sm border border-slate-700">
                        @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
                            @if(!$isActiveWeek)
                                <form action="{{ route('admin.guardias.activate_week', $guardia->id) }}" method="POST" onsubmit="return confirm('¿Activar esta guardia para la semana actual? Esto desactivará la guardia actual.');">
                                    @csrf
                                    <button type="submit" class="text-slate-300 hover:text-green-400 p-2 rounded-md hover:bg-slate-700/70 transition-all" title="Activar Semana">
                                        <i class="fas fa-calendar-check"></i>
                                    </button>
                                </form>
                            @endif
                        @endif
                        <a href="{{ route('admin.guardias.history.index', $guardia->id) }}" class="text-slate-300 hover:text-white p-2 rounded-md hover:bg-slate-700/70 transition-all" title="Historial">
                            <i class="fas fa-clock-rotate-left"></i>
                        </a>
                        <a href="{{ route('admin.guardias.edit', $guardia->id) }}" class="text-slate-300 hover:text-white p-2 rounded-md hover:bg-slate-700/70 transition-all" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
                        <form action="/admin/guardias/{{ $guardia->id }}/regenerate-credentials" method="POST" onsubmit="return confirm('¿Regenerar credenciales de acceso? La contraseña actual dejará de funcionar.');">
                            @csrf
                            <button type="submit" class="text-slate-300 hover:text-amber-400 p-2 rounded-md hover:bg-slate-700/70 transition-all" title="Regenerar credenciales">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.guardias.destroy', $guardia->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta guardia?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-red-400 p-2 rounded-md hover:bg-slate-700/70 transition-all" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de Personal -->
                <div class="flex-grow bg-white flex flex-col min-h-[200px]">
                    @if($guardia->bomberos->isEmpty())
                        <div class="flex-grow flex flex-col items-center justify-center text-slate-500 p-8 min-h-[150px]">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-2">
                                <i class="fas fa-user-slash text-xl opacity-50"></i>
                            </div>
                            <p class="text-xs font-medium">Sin personal asignado</p>
                        </div>
                    @else
                        <form action="{{ route('admin.guardias.bulk_update', $guardia->id) }}" method="POST" id="attendance-form-{{ $guardia->id }}" class="flex flex-col">
                            @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 overflow-y-auto max-h-[650px] pr-2 p-3 content-start items-start auto-rows-min">
                                @foreach($guardia->bomberos as $user)
                                    @php
                                        $repAsOriginal = isset($replacementByOriginal) ? ($replacementByOriginal[$user->id] ?? null) : null;
                                        $repAsReplacement = isset($replacementByReplacement) ? ($replacementByReplacement[$user->id] ?? null) : null;
                                        $lockAttendanceStatus = (bool) ($repAsReplacement || $user->es_refuerzo);
                                    @endphp
                                    <div id="bombero-card-{{ $user->id }}" data-guardia-id="{{ $guardia->id }}" data-bombero-id="{{ $user->id }}" class="bg-white rounded-xl border border-slate-200 shadow-sm relative overflow-visible group hover:border-blue-400 hover:shadow-md transition-all duration-200 flex flex-col items-center p-2 gap-2 text-center">
                                        
                                        <!-- Titular Toggle (Top Right) -->
                                        <div class="absolute top-2 right-2 z-10">
                                            <button type="button" 
                                                @if($repAsOriginal) disabled @else onclick="confirmToggleTitular('{{ route('admin.bomberos.toggle_titular', $user->id) }}')" @endif
                                                class="w-6 h-6 flex items-center justify-center rounded-full border shadow-sm transition-all {{ $repAsOriginal ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110' }} {{ $user->es_titular ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-white text-slate-600 border-slate-200' }}"
                                                title="{{ $user->es_titular ? 'Titular (Permanente)' : 'Transitorio (Temporal)' }}">
                                                <i class="fas {{ $user->es_titular ? 'fa-shield-halved' : 'fa-user-clock' }} text-[10px]"></i>
                                            </button>
                                        </div>

                                        <!-- Avatar (Centered) -->
                                        <div class="relative mt-1">
                                            @if($user->photo_path)
                                                <img src="{{ route('media', $user->photo_path) }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-sm mx-auto" alt="Foto">
                                            @else
                                                <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-slate-900 font-bold border border-slate-200 text-xl shadow-sm uppercase tracking-wider mx-auto">
                                                    {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellido_paterno, 0, 1) }}
                                                </div>
                                            @endif
                                            <!-- Status Dot if Active -->
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $repAsOriginal ? 'bg-amber-400' : ($user->estado_asistencia == 'constituye' ? 'bg-green-500' : ($user->estado_asistencia == 'reemplazo' ? 'bg-purple-500' : ($user->estado_asistencia == 'ausente' ? 'bg-red-500' : 'bg-slate-300'))) }}"></div>
                                        </div>

                                        <!-- Main Info (Centered) -->
                                        <div class="w-full flex flex-col items-center gap-1 mb-1">
                                            <h4 class="font-bold text-[#1e293b] text-sm leading-tight uppercase tracking-tight px-2" title="{{ $user->nombres }} {{ $user->apellido_paterno }}">
                                                {{ $user->nombres }}
                                                <span class="block text-xs font-extrabold text-[#475569] mt-0.5">{{ $user->apellido_paterno }}</span>
                                            </h4>
                                            
                                            <span class="text-[10px] font-medium text-[#475569] uppercase tracking-wider">
                                                {{ $user->es_jefe_guardia ? 'Jefe de Guardia' : 'Voluntario' }}
                                            </span>

                                            @if($user->es_refuerzo)
                                                <x-ui.badge variant="default" size="xs">Refuerzo</x-ui.badge>
                                            @endif

                                            @if($user->cargo_texto)
                                                <span class="text-[10px] font-semibold text-[#475569] uppercase tracking-wider">
                                                    {{ $user->cargo_texto }}
                                                </span>
                                            @endif

                                            <div class="flex flex-col items-center gap-0.5">
                                                @php
                                                    $ingreso = $user->fecha_ingreso ? \Carbon\Carbon::parse($user->fecha_ingreso) : null;
                                                    $diff = $ingreso ? $ingreso->diff(now()) : null;
                                                    $serviceYears = $diff ? (int) $diff->y : 0;
                                                    $serviceMonths = $diff ? (int) $diff->m : 0;
                                                    $yearsLabel = $serviceYears . ' ' . ($serviceYears === 1 ? 'a' : 'a');
                                                    $monthsLabel = $serviceMonths . ' ' . ($serviceMonths === 1 ? 'm' : 'm');
                                                    $serviceLabel = $diff ? trim($yearsLabel . ' ' . $monthsLabel) : '—';
                                                @endphp
                                                <span class="text-[10px] font-medium text-[#475569]">
                                                    {{ $serviceLabel }}
                                                </span>
                                                <span class="text-[10px] font-medium text-[#475569]">
                                                    Móvil: {{ $user->numero_portatil ? $user->numero_portatil : '—' }}
                                                </span>
                                            </div>

                                            <!-- Replacements Status Text -->
                                            @if($repAsReplacement)
                                                <div class="flex items-center gap-1 text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100 shadow-sm mt-1">
                                                    <i class="fas fa-right-left text-[9px]"></i>
                                                    <span class="text-[9px] font-bold">Reemplaza a {{ substr($repAsReplacement->originalFirefighter->nombres ?? '', 0, 1) }}. {{ $repAsReplacement->originalFirefighter->apellido_paterno ?? '' }}</span>
                                                </div>
                                            @endif
                                            @if($repAsOriginal)
                                                <div class="flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 shadow-sm mt-1">
                                                    <i class="fas fa-user-shield text-[9px]"></i>
                                                    <span class="text-[9px] font-bold">Cubierto por {{ substr($repAsOriginal->replacementFirefighter->nombres ?? '', 0, 1) }}. {{ $repAsOriginal->replacementFirefighter->apellido_paterno ?? '' }}</span>
                                                </div>
                                            @endif

                                            {{-- Cama Asignada --}}
                                            @php
                                                $bedAssignment = $bedAssignments[$user->id] ?? null;
                                            @endphp
                                            @if($bedAssignment && $bedAssignment->bed)
                                                <div class="flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 shadow-sm mt-1">
                                                    <i class="fas fa-bed text-[9px]"></i>
                                                    <span class="text-[9px] font-bold">Cama: {{ $bedAssignment->bed->name }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Controls Area (Full Width) -->
                                        <div class="w-full mt-auto space-y-2">
                                            @if($repAsOriginal)
                                                <div class="w-full text-center py-3 bg-amber-100 text-amber-800 font-semibold text-xs rounded-lg border border-amber-200 uppercase tracking-wider">
                                                    REEMPLAZADO
                                                </div>
                                            @else
                                                @if(!$lockAttendanceStatus)
                                                @endif

                                                <div class="relative w-full">
                                                    <input type="hidden" name="users[{{ $user->id }}][estado_asistencia]" id="attendance-status-{{ $user->id }}" value="{{ $user->estado_asistencia ?? 'constituye' }}">

                                                    <button type="button" id="attendance-btn-{{ $user->id }}" @if(!$lockAttendanceStatus) onclick="toggleAttendanceMenu('{{ $user->id }}')" @endif
                                                        class="w-full text-[10px] font-semibold uppercase py-2 px-2 rounded-md border-0 cursor-pointer transition-colors shadow-sm flex items-center justify-between gap-2
                                                        {{ $user->estado_asistencia == 'constituye' ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : '' }}
                                                        {{ $user->estado_asistencia == 'reemplazo' ? 'bg-purple-100 text-purple-700 ring-1 ring-purple-200' : '' }}
                                                        {{ $user->estado_asistencia == 'permiso' ? 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200' : '' }}
                                                        {{ $user->estado_asistencia == 'ausente' ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : '' }}
                                                        {{ $user->estado_asistencia == 'falta' ? 'bg-red-200 text-red-800 ring-1 ring-red-300' : '' }}
                                                        {{ $user->estado_asistencia == 'licencia' ? 'bg-blue-100 text-blue-700 ring-1 ring-blue-200' : '' }}
                                                        {{ $lockAttendanceStatus ? 'opacity-80 cursor-not-allowed' : '' }}
                                                    ">
                                                        <span class="flex items-center gap-2">
                                                            <span id="attendance-dot-{{ $user->id }}" class="w-2 h-2 rounded-full
                                                                {{ $user->estado_asistencia == 'constituye' ? 'bg-green-500' : '' }}
                                                                {{ $user->estado_asistencia == 'reemplazo' ? 'bg-purple-500' : '' }}
                                                                {{ $user->estado_asistencia == 'permiso' ? 'bg-yellow-500' : '' }}
                                                                {{ $user->estado_asistencia == 'ausente' ? 'bg-red-500' : '' }}
                                                                {{ $user->estado_asistencia == 'falta' ? 'bg-red-600' : '' }}
                                                                {{ $user->estado_asistencia == 'licencia' ? 'bg-blue-500' : '' }}
                                                            "></span>
                                                            <span id="attendance-label-{{ $user->id }}">
                                                                @if($user->estado_asistencia == 'constituye') CONSTITUYE @endif
                                                                @if($user->estado_asistencia == 'reemplazo') REEMPLAZO @endif
                                                                @if($user->estado_asistencia == 'permiso') PERMISO @endif
                                                                @if($user->estado_asistencia == 'ausente') AUSENTE @endif
                                                                @if($user->estado_asistencia == 'falta') CUMPLE FALTA @endif
                                                                @if($user->estado_asistencia == 'licencia') LICENCIA MÉDICA @endif
                                                                @if(!in_array($user->estado_asistencia, ['constituye','reemplazo','permiso','ausente','falta','licencia'])) CONSTITUYE @endif
                                                            </span>
                                                        </span>
                                                        @if(!$lockAttendanceStatus)
                                                            <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                                                        @endif
                                                    </button>

                                                    @if(!$lockAttendanceStatus)
                                                        <div id="attendance-menu-{{ $user->id }}" class="hidden absolute left-0 right-0 mt-1 z-30 bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden">
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'constituye')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                                <span class="text-green-700">Constituye</span>
                                                            </button>
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'reemplazo')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                                <span class="text-purple-700">Reemplazo</span>
                                                            </button>
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'permiso')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                                                <span class="text-yellow-700">Permiso</span>
                                                            </button>
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'ausente')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                                <span class="text-red-700">Ausente</span>
                                                            </button>
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'falta')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                                                <span class="text-red-800">Cumple Falta</span>
                                                            </button>
                                                            <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'licencia')" class="w-full px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2 hover:bg-white">
                                                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                                <span class="text-blue-700">Licencia Médica</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if(!$repAsReplacement)
                                                    <button type="button" id="btn-replacement-{{ $user->id }}" data-action="open-replacement-modal" data-guardia-id="{{ $guardia->id }}" data-user-id="{{ $user->id }}" data-user-name="{{ $user->nombres }} {{ $user->apellido_paterno }}" onclick="openReplacementModal(this.dataset.guardiaId, this.dataset.userId, this.dataset.userName)" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-3 rounded-lg text-[10px] transition shadow-sm uppercase tracking-wider">
                                                        <i class="fas fa-user-plus mr-2"></i> Reemplazar
                                                    </button>
                                                @endif

                                                {{-- Botones de Cama --}}
                                                @php
                                                    $bedAssignment = $bedAssignments[$user->id] ?? null;
                                                    $canAssignBed = in_array($user->estado_asistencia, ['constituye', 'reemplazo', 'refuerzo']) && !$user->fuera_de_servicio;
                                                    $userId = \App\Models\MapaBomberoUsuarioLegacy::where('firefighter_id', $user->id)->value('user_id');
                                                @endphp
                                                @if($userId)
                                                    @if($bedAssignment && $bedAssignment->bed)
                                                        {{-- Tiene cama asignada - botón liberar --}}
                                                        <button type="button" onclick="quickReleaseBed({{ $userId }}, {{ $bedAssignment->bed->id }}, '{{ $bedAssignment->bed->name }}')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3 rounded-lg text-[10px] transition shadow-sm uppercase tracking-wider">
                                                            <i class="fas fa-bed mr-1"></i> Liberar Cama
                                                        </button>
                                                    @elseif($canAssignBed)
                                                        {{-- No tiene cama - botón asignar --}}
                                                        @php
                                                            $userGender = \App\Models\User::find($userId)->gender ?? 'mixed';
                                                        @endphp
                                                        <button type="button" onclick="quickAssignBed({{ $userId }}, '{{ $user->nombres }} {{ $user->apellido_paterno }}', '{{ $userGender }}')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded-lg text-[10px] transition shadow-sm uppercase tracking-wider">
                                                            <i class="fas fa-bed mr-1"></i> Asignar Cama
                                                        </button>
                                                    @endif
                                                @endif
                                            @endif

                                            <div class="flex justify-center gap-2 mt-2 pt-2 border-t border-slate-200">
                                                @if($user->es_conductor)
                                                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold border border-blue-200" title="Conductor">
                                                        <i class="fas fa-car text-[10px]"></i>
                                                    </span>
                                                @endif
                                                @if($user->es_operador_rescate) 
                                                    <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] font-bold border border-orange-200" title="Rescate">R</span>
                                                @endif
                                                @if($user->es_asistente_trauma) 
                                                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[10px] font-bold border border-red-200" title="Trauma">T</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                                <div class="p-4 border-t border-slate-200 bg-white mt-auto">
                                    <button type="submit" id="attendance-submit-{{ $guardia->id }}" disabled class="w-full bg-slate-900 hover:bg-slate-800 disabled:bg-slate-400 disabled:hover:bg-slate-400 text-white font-semibold py-3 px-4 rounded-xl text-xs transition-all shadow-sm hover:shadow-md disabled:shadow-none flex items-center justify-center uppercase tracking-wider group">
                                        <span class="mr-2">Constituir Guardia</span>
                                        <i class="fas fa-check-circle text-emerald-400 text-lg group-hover:text-emerald-300 transition-colors"></i>
                                    </button>
                                </div>
                            </form>

                    @endif
                </div>

                <!-- Formulario Agregar / Asignar (Colapsable) -->
                <div class="bg-white border-t border-slate-200 mt-auto z-10 relative">
                    <div class="grid grid-cols-1">
                        <a href="{{ route('admin.dotaciones') }}" class="w-full p-3 flex items-center justify-center text-[10px] font-semibold text-[#475569] uppercase tracking-wider hover:bg-white transition-colors group outline-none">
                            <i class="fas fa-users-gear mr-2 text-[#475569]"></i> Gestionar Dotación
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Formulario Compartido para Toggle Titularidad -->
    <form id="form-titular-shared" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Modal de Asignación de Reemplazo -->
    <div id="replacementModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 border border-slate-200">
            <div class="flex justify-between items-start p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1e293b]">Asignar Reemplazo</h3>
                    <p class="text-sm text-[#475569] mt-1">Selecciona el voluntario que cubrirá el turno.</p>
                </div>
                <button type="button" onclick="closeReplacementModal()" class="text-[#475569] hover:text-[#1e293b] transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0">
                    <i class="fas fa-user-clock"></i>
                </div>
                <span class="text-xs font-semibold text-blue-600">Reemplazando a:</span>
                <p id="modal_original_user_name" class="text-sm font-bold text-[#1e293b]">Usuario Original</p>
            </div>

            <form action="{{ route('admin.guardias.replacement') }}" method="POST">
                @csrf
                <input type="hidden" name="guardia_id" id="modal_guardia_id">
                <input type="hidden" name="original_firefighter_id" id="modal_original_user_id">

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Voluntario Reemplazante</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-xs"></i>
                            <input list="modal_volunteers_list" name="replacement_firefighter_id_display" autocomplete="off"
                                class="form-input pl-9"
                                placeholder="Buscar voluntario..." required
                                oninput="updateModalUserId(this)">
                            <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_user_id" required>
                        </div>
                        <datalist id="modal_volunteers_list">
                            @foreach($volunteers as $volunteer)
                                <option data-value="{{ $volunteer->id }}" value="{{ trim($volunteer->nombres . ' ' . $volunteer->apellido_paterno . ' ' . ($volunteer->apellido_materno ?? '') . ($volunteer->rut ? ' - ' . $volunteer->rut : '')) }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-200">
                        <x-ui.button type="button" variant="secondary" size="md" onclick="closeReplacementModal()" class="w-1/2">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-check" class="w-1/2">
                            Confirmar
                        </x-ui.button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <script>
        const ATTENDANCE_THEMES = {
            constituye: {
                label: 'CONSTITUYE',
                btn: ['bg-green-100','text-green-700','ring-1','ring-green-200'],
                dot: ['bg-green-500'],
            },
            reemplazo: {
                label: 'REEMPLAZO',
                btn: ['bg-purple-100','text-purple-700','ring-1','ring-purple-200'],
                dot: ['bg-purple-500'],
            },
            permiso: {
                label: 'PERMISO',
                btn: ['bg-yellow-100','text-yellow-700','ring-1','ring-yellow-200'],
                dot: ['bg-yellow-500'],
            },
            ausente: {
                label: 'AUSENTE',
                btn: ['bg-red-100','text-red-700','ring-1','ring-red-200'],
                dot: ['bg-red-500'],
            },
            falta: {
                label: 'CUMPLE FALTA',
                btn: ['bg-red-200','text-red-800','ring-1','ring-red-300'],
                dot: ['bg-red-600'],
            },
            licencia: {
                label: 'LICENCIA MÉDICA',
                btn: ['bg-blue-100','text-blue-700','ring-1','ring-blue-200'],
                dot: ['bg-blue-500'],
            },
        };

        const ATTENDANCE_BTN_THEME_CLASSES = [
            'bg-green-100','text-green-700','ring-green-200',
            'bg-purple-100','text-purple-700','ring-purple-200',
            'bg-yellow-100','text-yellow-700','ring-yellow-200',
            'bg-red-100','text-red-700','ring-red-200',
            'bg-red-200','text-red-800','ring-red-300',
            'bg-blue-100','text-blue-700','ring-blue-200',
            'ring-1'
        ];

        const ATTENDANCE_DOT_CLASSES = [
            'bg-green-500','bg-purple-500','bg-yellow-500','bg-red-500','bg-red-600','bg-blue-500'
        ];

        function toggleAttendanceMenu(userId) {
            const menu = document.getElementById('attendance-menu-' + userId);
            if (!menu) return;
            document.querySelectorAll('[id^="attendance-menu-"]').forEach(el => {
                if (el !== menu) el.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        }

        function setAttendanceStatus(userId, status) {
            const theme = ATTENDANCE_THEMES[status] || ATTENDANCE_THEMES.constituye;
            const input = document.getElementById('attendance-status-' + userId);
            const btn = document.getElementById('attendance-btn-' + userId);
            const label = document.getElementById('attendance-label-' + userId);
            const dot = document.getElementById('attendance-dot-' + userId);
            const menu = document.getElementById('attendance-menu-' + userId);

            if (input) input.value = status;
            if (label) label.textContent = theme.label;

            if (btn) {
                btn.classList.remove(...ATTENDANCE_BTN_THEME_CLASSES);
                btn.classList.add(...theme.btn);
            }

            if (dot) {
                dot.classList.remove(...ATTENDANCE_DOT_CLASSES);
                dot.classList.add(...theme.dot);
            }

            if (menu) menu.classList.add('hidden');
        }

        function toggleAssignForm(guardiaId) {
            const form = document.getElementById('form-assign-' + guardiaId);
            const icon = document.getElementById('icon-assign-' + guardiaId);
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                form.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function toggleReplacementBtn(select, btnId) {
            const btn = document.getElementById(btnId);
            if (!btn) return;

            btn.classList.remove('hidden');
        }

        function confirmToggleTitular(url) {
            if (confirm('¿Estás seguro de que deseas cambiar la titularidad de este bombero?')) {
                if (confirm('Esta acción determinará si el bombero se mantiene en la guardia al finalizar el turno. ¿Confirmar cambio definitivo?')) {
                    const form = document.getElementById('form-titular-shared');
                    form.action = url;
                    form.submit();
                }
            }
        }

        function updateUserId(input, guardiaId) {
            const list = document.getElementById('volunteers_list_' + guardiaId);
            const hiddenInput = document.getElementById('user_id_input_' + guardiaId);
            const options = list.options;
            
            hiddenInput.value = ''; // Reset
            
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
                    hiddenInput.value = options[i].getAttribute('data-value');
                    break;
                }
            }
        }

        function updateModalUserId(input) {
            const list = document.getElementById('modal_volunteers_list');
            const hiddenInput = document.getElementById('modal_replacement_user_id');
            const options = list.options;
            
            hiddenInput.value = '';
            const displayValue = (input.value || '').trim();
            if (!displayValue) return;
            
            for (let i = 0; i < options.length; i++) {
                if ((options[i].value || '').trim() === displayValue) {
                    hiddenInput.value = options[i].getAttribute('data-value');
                    break;
                }
            }
        }

        function openReplacementModal(guardiaId, userId, userName) {
            const modal = document.getElementById('replacementModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            document.getElementById('modal_guardia_id').value = guardiaId;
            document.getElementById('modal_original_user_id').value = userId;
            document.getElementById('modal_original_user_name').textContent = userName;
            
            modal.classList.remove('hidden');

            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            });
        }

        function closeReplacementModal() {
            const modal = document.getElementById('replacementModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function updateModalRefuerzoId(input) {
            const list = document.getElementById('modal_refuerzo_volunteers_list');
            const hiddenInput = document.getElementById('modal_refuerzo_firefighter_id');
            const options = list.options;

            hiddenInput.value = '';
            const displayValue = (input.value || '').trim();
            if (!displayValue) return;
            for (let i = 0; i < options.length; i++) {
                if ((options[i].value || '').trim() === displayValue) {
                    hiddenInput.value = options[i].getAttribute('data-value');
                    break;
                }
            }
        }

        function openRefuerzoModal(guardiaId) {
            const modal = document.getElementById('refuerzoModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            const inputGuardia = document.getElementById('modal_refuerzo_guardia_id');
            if (inputGuardia) inputGuardia.value = guardiaId;

            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            });
        }

        function closeRefuerzoModal() {
            const modal = document.getElementById('refuerzoModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Close on escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeReplacementModal();
                closeRefuerzoModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            refreshAttendanceSubmitButtons();

            document.querySelectorAll('[data-action="open-replacement-modal"]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const guardiaId = this.getAttribute('data-guardia-id');
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name') || '';
                    openReplacementModal(guardiaId, userId, userName);
                });
            });

            const modalReplacementDisplay = document.querySelector('#replacementModal input[list="modal_volunteers_list"]');
            const modalReplacementList = document.getElementById('modal_volunteers_list');
            const modalReplacementHidden = document.getElementById('modal_replacement_user_id');

            if (modalReplacementDisplay && modalReplacementList && modalReplacementHidden) {
                ['change', 'blur'].forEach(evt => {
                    modalReplacementDisplay.addEventListener(evt, () => updateModalUserId(modalReplacementDisplay));
                });

                const form = modalReplacementDisplay.closest('form');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        updateModalUserId(modalReplacementDisplay);
                        if ((modalReplacementHidden.value || '').trim() === '') {
                            e.preventDefault();
                            alert('Debes seleccionar un voluntario de la lista.');
                        }
                    });
                }
            }

            const modalRefuerzoDisplay = document.querySelector('#refuerzoModal input[list="modal_refuerzo_volunteers_list"]');
            const modalRefuerzoHidden = document.getElementById('modal_refuerzo_firefighter_id');
            const modalRefuerzoList = document.getElementById('modal_refuerzo_volunteers_list');
            if (modalRefuerzoDisplay && modalRefuerzoHidden && modalRefuerzoList) {
                ['change', 'blur'].forEach(evt => {
                    modalRefuerzoDisplay.addEventListener(evt, () => updateModalRefuerzoId(modalRefuerzoDisplay));
                });

                const form = modalRefuerzoDisplay.closest('form');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        updateModalRefuerzoId(modalRefuerzoDisplay);
                        if ((modalRefuerzoHidden.value || '').trim() === '') {
                            e.preventDefault();
                            alert('Debes seleccionar un voluntario de la lista.');
                        }
                    });
                }
            }

            document.addEventListener('click', function (event) {
                const target = event.target;
                const isAttendanceBtn = target.closest && target.closest('[id^="attendance-btn-"]');
                const isAttendanceMenu = target.closest && target.closest('[id^="attendance-menu-"]');
                if (!isAttendanceBtn && !isAttendanceMenu) {
                    document.querySelectorAll('[id^="attendance-menu-"]').forEach(el => el.classList.add('hidden'));
                }
            });
        });
    </div>

    {{-- Modal Asignación Rápida de Cama --}}
    <div id="quickBedAssignModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 border border-slate-200">
            <div class="flex justify-between items-start p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1e293b]">Asignar Cama</h3>
                    <p class="text-sm text-[#475569] mt-1" id="quickBedAssignVolunteerName"></p>
                </div>
                <button type="button" onclick="closeQuickBedAssignModal()" class="text-[#475569] hover:text-[#1e293b] transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="quickBedAssignForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Seleccionar Cama</label>
                        <select name="bed_id" id="quickBedSelect" class="form-select" required>
                            <option value="">Cargando camas...</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Notas sobre la asignación..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex gap-3">
                    <button type="button" onclick="closeQuickBedAssignModal()" class="flex-1 px-4 py-2 bg-white hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Liberación Rápida de Cama --}}
    <div id="quickBedReleaseModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 border border-slate-200">
            <div class="flex justify-between items-start p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-bold text-[#1e293b]">Liberar Cama</h3>
                    <p class="text-sm text-[#475569] mt-1" id="quickBedReleaseBedName"></p>
                </div>
                <button type="button" onclick="closeQuickBedReleaseModal()" class="text-[#475569] hover:text-[#1e293b] transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="quickBedReleaseForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Notas sobre la liberación..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex gap-3">
                    <button type="button" onclick="closeQuickBedReleaseModal()" class="flex-1 px-4 py-2 bg-white hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors">
                        Liberar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Camas disponibles (cargadas desde el servidor)
        const availableBeds = @json($availableBeds);

        // Función para asignar cama rápidamente
        function quickAssignBed(firefighterId, volunteerName, gender) {
            const modal = document.getElementById('quickBedAssignModal');
            const form = document.getElementById('quickBedAssignForm');
            const select = document.getElementById('quickBedSelect');
            const nameDisplay = document.getElementById('quickBedAssignVolunteerName');

            // Configurar modal
            nameDisplay.textContent = volunteerName;

            // Filtrar camas por género si es necesario
            let filteredBeds = availableBeds;
            if (gender && gender !== 'mixed') {
                filteredBeds = availableBeds.filter(bed => {
                    if (bed.gender === 'mixed') return true;
                    if (gender === 'male' || gender === 'masculino') return bed.gender === 'male';
                    if (gender === 'female' || gender === 'femenino') return bed.gender === 'female';
                    return true;
                });
            }

            // Poblar select
            select.innerHTML = '<option value="">Seleccionar cama...</option>';
            if (filteredBeds.length === 0) {
                select.innerHTML = '<option value="">No hay camas disponibles</option>';
                select.disabled = true;
            } else {
                select.disabled = false;
                filteredBeds.forEach(bed => {
                    const option = document.createElement('option');
                    option.value = bed.id;
                    option.textContent = `${bed.name} - ${bed.location || 'Sin ubicación'}`;
                    select.appendChild(option);
                });
            }

            // Configurar acción del formulario
            form.onsubmit = async function(e) {
                e.preventDefault();
                const bedId = select.value;
                if (!bedId) {
                    alert('Debes seleccionar una cama');
                    return;
                }

                const formData = new FormData(form);
                formData.append('volunteer_id', firefighterId);

                try {
                    const response = await fetch(`/admin/beds/${bedId}/assign`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const data = await response.json();
                        alert(data.message || 'Error al asignar cama');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al asignar cama');
                }
            };

            // Mostrar modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.transform').classList.remove('scale-95');
            }, 10);
        }

        function closeQuickBedAssignModal() {
            const modal = document.getElementById('quickBedAssignModal');
            modal.classList.add('opacity-0');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('quickBedAssignForm').reset();
            }, 300);
        }

        // Función para liberar cama rápidamente
        function quickReleaseBed(firefighterId, bedId, bedName) {
            const modal = document.getElementById('quickBedReleaseModal');
            const form = document.getElementById('quickBedReleaseForm');
            const nameDisplay = document.getElementById('quickBedReleaseBedName');

            // Configurar modal
            nameDisplay.textContent = `Cama: ${bedName}`;

            // Configurar acción del formulario
            form.onsubmit = async function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                try {
                    const response = await fetch(`/admin/beds/${bedId}/release`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const data = await response.json();
                        alert(data.message || 'Error al liberar cama');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al liberar cama');
                }
            };

            // Mostrar modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.transform').classList.remove('scale-95');
            }, 10);
        }

        function closeQuickBedReleaseModal() {
            const modal = document.getElementById('quickBedReleaseModal');
            modal.classList.add('opacity-0');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('quickBedReleaseForm').reset();
            }, 300);
        }

        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQuickBedAssignModal();
                closeQuickBedReleaseModal();
            }
        });

        function toggleFullscreen() {
            try {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            } catch (e) {
                // No-op
            }
        }
    </script>
@endsection
