@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-shield mr-3 text-red-700"></i> Administración de Guardias
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Gestión de equipos</p>
        </div>
        
        <!-- Formulario Crear Guardia -->
        @if(auth()->user()->role === 'super_admin')
            <form action="{{ route('admin.guardias.store') }}" method="POST" class="flex gap-2 w-full md:w-auto">
                @csrf
                <input type="text" name="name" placeholder="Nombre nueva guardia..." required
                    class="rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-4 py-2.5 text-sm w-full md:w-64">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition shadow-md flex items-center">
                    <i class="fas fa-plus mr-2"></i> Crear
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-r shadow-sm flex items-center" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle mr-2 text-xl"></i>
                <p class="font-bold">Error:</p>
            </div>
            <ul class="list-disc list-inside ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 overflow-y-auto max-h-[650px] pr-2 flex-grow p-3 items-start">
        @foreach($guardias as $guardia)
            @php
                $isActiveWeek = isset($activeGuardia) && $activeGuardia && (int) $activeGuardia->id === (int) $guardia->id;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-all w-full">
                <!-- Header Guardia -->
                <div class="relative overflow-hidden bg-slate-900 text-white p-5 flex justify-between items-center border-b border-slate-800 {{ $isActiveWeek ? 'ring-2 ring-green-500 ring-offset-2 ring-offset-slate-50' : '' }}">
                    @if($isActiveWeek)
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-green-500 transform rotate-45 z-0"></div>
                        <div class="absolute top-1 right-1 text-green-900 z-10">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                    @endif
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-black tracking-tight uppercase text-white leading-none">{{ $guardia->name }}</h2>
                            @if($isActiveWeek)
                                <span class="bg-green-500/20 text-green-400 border border-green-500/50 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-widest shadow-sm backdrop-blur-sm">
                                    En Turno
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs mt-1 font-medium flex items-center">
                            <i class="fas fa-users mr-2 opacity-50"></i> {{ $guardia->firefighters->count() }} Voluntario
                        </p>
                    </div>

                    <div class="flex items-center gap-1 relative z-10 bg-slate-800/50 p-1 rounded-lg backdrop-blur-sm border border-slate-700">
                        <a href="{{ route('admin.guardias.edit', $guardia->id) }}" class="text-slate-400 hover:text-white p-2 rounded-md hover:bg-slate-700/50 transition-all" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.guardias.destroy', $guardia->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta guardia?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-400 p-2 rounded-md hover:bg-slate-700/50 transition-all" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Lista de Personal -->
                <div class="flex-grow bg-slate-50/50 flex flex-col min-h-[200px]">
                    @if($guardia->firefighters->isEmpty())
                        <div class="flex-grow flex flex-col items-center justify-center text-slate-400 p-8 min-h-[150px]">
                            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-2">
                                <i class="fas fa-user-slash text-xl opacity-50"></i>
                            </div>
                            <p class="text-xs font-medium">Sin personal asignado</p>
                        </div>
                    @else
                        <form action="{{ route('admin.guardias.bulk_update', $guardia->id) }}" method="POST" id="attendance-form-{{ $guardia->id }}" class="flex flex-col">
                            @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 overflow-y-auto max-h-[650px] pr-2 p-3 content-start items-start auto-rows-min">
                                @foreach($guardia->firefighters as $user)
                                    @php
                                        $repAsOriginal = isset($replacementByOriginal) ? ($replacementByOriginal[$user->id] ?? null) : null;
                                        $repAsReplacement = isset($replacementByReplacement) ? ($replacementByReplacement[$user->id] ?? null) : null;
                                    @endphp
                                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm relative overflow-visible group hover:border-blue-400 hover:shadow-md transition-all duration-200 flex flex-col items-center p-2 gap-2 text-center">
                                        
                                        <!-- Titular Toggle (Top Right) -->
                                        <div class="absolute top-2 right-2 z-10">
                                            <button type="button" 
                                                @if($repAsOriginal) disabled @else onclick="confirmToggleTitular('{{ route('admin.bomberos.toggle_titular', $user->id) }}')" @endif
                                                class="w-6 h-6 flex items-center justify-center rounded-full border shadow-sm transition-all {{ $repAsOriginal ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110' }} {{ $user->is_titular ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200' }}"
                                                title="{{ $user->is_titular ? 'Titular (Permanente)' : 'Transitorio (Temporal)' }}">
                                                <i class="fas {{ $user->is_titular ? 'fa-shield-halved' : 'fa-user-clock' }} text-[10px]"></i>
                                            </button>
                                        </div>

                                        <!-- Avatar (Centered) -->
                                        <div class="relative mt-1">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-500 font-bold border border-slate-200 text-xl shadow-sm uppercase tracking-wider mx-auto">
                                                {{ substr($user->name, 0, 1) }}{{ substr($user->last_name_paternal, 0, 1) }}
                                            </div>
                                            <!-- Status Dot if Active -->
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $repAsOriginal ? 'bg-amber-400' : ($user->attendance_status == 'constituye' ? 'bg-green-500' : ($user->attendance_status == 'reemplazo' ? 'bg-purple-500' : ($user->attendance_status == 'ausente' ? 'bg-red-500' : 'bg-slate-300'))) }}"></div>
                                        </div>

                                        <!-- Main Info (Centered) -->
                                        <div class="w-full flex flex-col items-center gap-1 mb-1">
                                            <h4 class="font-bold text-slate-800 text-sm leading-tight uppercase tracking-tight px-2" title="{{ $user->name }} {{ $user->last_name_paternal }}">
                                                {{ $user->name }}
                                                <span class="block text-xs font-extrabold text-slate-600 mt-0.5">{{ $user->last_name_paternal }}</span>
                                            </h4>
                                            
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                {{ $user->is_shift_leader ? 'Jefe de Guardia' : 'Voluntario' }}
                                            </span>

                                            <!-- Replacements Status Text -->
                                            @if($repAsReplacement)
                                                <div class="flex items-center gap-1 text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100 shadow-sm mt-1">
                                                    <i class="fas fa-right-left text-[9px]"></i>
                                                    <span class="text-[9px] font-bold">Reemplaza a {{ substr($repAsReplacement->originalFirefighter->name ?? '', 0, 1) }}. {{ $repAsReplacement->originalFirefighter->last_name_paternal ?? '' }}</span>
                                                </div>
                                            @endif
                                            @if($repAsOriginal)
                                                <div class="flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 shadow-sm mt-1">
                                                    <i class="fas fa-user-shield text-[9px]"></i>
                                                    <span class="text-[9px] font-bold">Cubierto por {{ substr($repAsOriginal->replacementFirefighter->name ?? '', 0, 1) }}. {{ $repAsOriginal->replacementFirefighter->last_name_paternal ?? '' }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Controls Area (Full Width) -->
                                        <div class="w-full mt-auto space-y-2">
                                            @if($repAsOriginal)
                                                <div class="w-full text-center py-3 bg-amber-100 text-amber-900 font-black text-xs rounded-lg border border-amber-200 uppercase tracking-[0.25em]">
                                                    REEMPLAZADO
                                                </div>
                                            @else
                                                <div class="relative w-full">
                                                    <input type="hidden" name="users[{{ $user->id }}][attendance_status]" id="attendance-status-{{ $user->id }}" value="{{ $user->attendance_status ?? 'constituye' }}">

                                                    <button type="button" id="attendance-btn-{{ $user->id }}" onclick="toggleAttendanceMenu('{{ $user->id }}')"
                                                        class="w-full text-[10px] font-black uppercase py-2 px-2 rounded-md border-0 cursor-pointer transition-colors shadow-sm flex items-center justify-between gap-2
                                                        {{ $user->attendance_status == 'constituye' ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : '' }}
                                                        {{ $user->attendance_status == 'reemplazo' ? 'bg-purple-100 text-purple-700 ring-1 ring-purple-200' : '' }}
                                                        {{ $user->attendance_status == 'permiso' ? 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200' : '' }}
                                                        {{ $user->attendance_status == 'ausente' ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : '' }}
                                                        {{ $user->attendance_status == 'falta' ? 'bg-red-200 text-red-800 ring-1 ring-red-300' : '' }}
                                                        {{ $user->attendance_status == 'licencia' ? 'bg-blue-100 text-blue-700 ring-1 ring-blue-200' : '' }}
                                                    ">
                                                        <span class="flex items-center gap-2">
                                                            <span id="attendance-dot-{{ $user->id }}" class="w-2 h-2 rounded-full
                                                                {{ $user->attendance_status == 'constituye' ? 'bg-green-500' : '' }}
                                                                {{ $user->attendance_status == 'reemplazo' ? 'bg-purple-500' : '' }}
                                                                {{ $user->attendance_status == 'permiso' ? 'bg-yellow-500' : '' }}
                                                                {{ $user->attendance_status == 'ausente' ? 'bg-red-500' : '' }}
                                                                {{ $user->attendance_status == 'falta' ? 'bg-red-600' : '' }}
                                                                {{ $user->attendance_status == 'licencia' ? 'bg-blue-500' : '' }}
                                                            "></span>
                                                            <span id="attendance-label-{{ $user->id }}">
                                                                @if($user->attendance_status == 'constituye') CONSTITUYE @endif
                                                                @if($user->attendance_status == 'reemplazo') REEMPLAZO @endif
                                                                @if($user->attendance_status == 'permiso') PERMISO @endif
                                                                @if($user->attendance_status == 'ausente') AUSENTE @endif
                                                                @if($user->attendance_status == 'falta') CUMPLE FALTA @endif
                                                                @if($user->attendance_status == 'licencia') LICENCIA MÉDICA @endif
                                                                @if(!in_array($user->attendance_status, ['constituye','reemplazo','permiso','ausente','falta','licencia'])) CONSTITUYE @endif
                                                            </span>
                                                        </span>
                                                        <i class="fas fa-chevron-down text-[10px] opacity-60"></i>
                                                    </button>

                                                    <div id="attendance-menu-{{ $user->id }}" class="hidden absolute left-0 right-0 mt-1 z-30 bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden">
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'constituye')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                            <span class="text-green-700">CONSTITUYE</span>
                                                        </button>
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'reemplazo')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                            <span class="text-purple-700">REEMPLAZO</span>
                                                        </button>
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'permiso')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                                            <span class="text-yellow-700">PERMISO</span>
                                                        </button>
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'ausente')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                            <span class="text-red-700">AUSENTE</span>
                                                        </button>
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'falta')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                                            <span class="text-red-800">CUMPLE FALTA</span>
                                                        </button>
                                                        <button type="button" onclick="setAttendanceStatus('{{ $user->id }}', 'licencia')" class="w-full px-3 py-2 text-xs font-black uppercase tracking-wide flex items-center gap-2 hover:bg-slate-50">
                                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                            <span class="text-blue-700">LICENCIA MÉDICA</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                @if(!$repAsReplacement)
                                                    <button type="button" id="btn-replacement-{{ $user->id }}" data-action="open-replacement-modal" data-guardia-id="{{ $guardia->id }}" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }} {{ $user->last_name_paternal }}" onclick="openReplacementModal(this.dataset.guardiaId, this.dataset.userId, this.dataset.userName)" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded-lg text-[10px] transition shadow-sm uppercase tracking-wider">
                                                        <i class="fas fa-user-plus mr-2"></i> Reemplazar
                                                    </button>
                                                @endif
                                            @endif

                                            <div class="flex justify-center gap-2 mt-2 pt-2 border-t border-slate-50">
                                                @if($user->is_rescue_operator) 
                                                    <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] font-bold border border-orange-200" title="Rescate">R</span>
                                                @endif
                                                @if($user->is_trauma_assistant) 
                                                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[10px] font-bold border border-red-200" title="Trauma">T</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                                <div class="p-4 border-t border-slate-100 bg-white mt-auto">
                                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-4 rounded-xl text-xs transition-all shadow-lg hover:shadow-xl flex items-center justify-center uppercase tracking-widest group transform hover:-translate-y-0.5">
                                        <span class="mr-2">Constituir Guardia</span>
                                        <i class="fas fa-check-circle text-emerald-400 text-lg group-hover:text-emerald-300 transition-colors"></i>
                                    </button>
                                </div>
                            </form>

                    @endif
                </div>

                <!-- Formulario Agregar / Asignar (Colapsable) -->
                <div class="bg-white border-t border-slate-200 mt-auto shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.1)] z-10 relative">
                    <a href="{{ route('admin.dotaciones') }}" class="w-full p-3 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase tracking-widest hover:bg-slate-50 transition-colors group outline-none focus:bg-slate-50">
                        <i class="fas fa-users-gear mr-2 text-slate-400"></i> Gestionar Dotación en Dotaciones
                    </a>
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
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Asignar Reemplazo</h3>
                    <p class="text-sm text-slate-500 mt-1">Selecciona el voluntario que cubrirá el turno.</p>
                </div>
                <button type="button" onclick="closeReplacementModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-500 uppercase tracking-wide">Reemplazando a:</span>
                    <p id="modal_original_user_name" class="text-sm font-bold text-slate-700">Usuario Original</p>
                </div>
            </div>

            <form action="{{ route('admin.guardias.replacement') }}" method="POST">
                @csrf
                <input type="hidden" name="guardia_id" id="modal_guardia_id">
                <input type="hidden" name="original_firefighter_id" id="modal_original_user_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Voluntario Reemplazante</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input list="modal_volunteers_list" name="replacement_firefighter_id_display" 
                                class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-9 py-2.5 bg-slate-50"
                                placeholder="Buscar voluntario..." required
                                oninput="updateModalUserId(this)">
                            <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_user_id" required>
                        </div>
                        <datalist id="modal_volunteers_list">
                            @foreach($volunteers as $volunteer)
                                <option data-value="{{ $volunteer->id }}" value="{{ trim($volunteer->last_name_paternal . ' ' . ($volunteer->last_name_maternal ?? '') . ', ' . $volunteer->name . ($volunteer->rut ? ' - ' . $volunteer->rut : '')) }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeReplacementModal()" class="w-1/2 py-2.5 px-4 rounded-lg border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors uppercase">
                            Cancelar
                        </button>
                        <button type="submit" class="w-1/2 py-2.5 px-4 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-md hover:shadow-lg transition-all uppercase flex items-center justify-center gap-2">
                            <span>Confirmar</span>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            </form>
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
            
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
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

        // Close on escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeReplacementModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-action="open-replacement-modal"]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const guardiaId = this.getAttribute('data-guardia-id');
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name') || '';
                    openReplacementModal(guardiaId, userId, userName);
                });
            });

            document.addEventListener('click', function (event) {
                const target = event.target;
                const isAttendanceBtn = target.closest && target.closest('[id^="attendance-btn-"]');
                const isAttendanceMenu = target.closest && target.closest('[id^="attendance-menu-"]');
                if (!isAttendanceBtn && !isAttendanceMenu) {
                    document.querySelectorAll('[id^="attendance-menu-"]').forEach(el => el.classList.add('hidden'));
                }
            });
        });
    </script>
@endsection
