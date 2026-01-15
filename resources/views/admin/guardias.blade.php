@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-shield-halved mr-3 text-red-700"></i> Administración de Guardias
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Gestión de equipos y asignación de personal</p>
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
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-all w-full">
                <!-- Header Guardia -->
                <div class="relative overflow-hidden bg-slate-900 text-white p-5 flex justify-between items-center border-b border-slate-800 {{ $guardia->is_active_week ? 'ring-2 ring-green-500 ring-offset-2 ring-offset-slate-50' : '' }}">
                    @if($guardia->is_active_week)
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-green-500 transform rotate-45 z-0"></div>
                        <div class="absolute top-1 right-1 text-green-900 z-10">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                    @endif
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-black tracking-tight uppercase text-white leading-none">{{ $guardia->name }}</h2>
                            @if($guardia->is_active_week)
                                <span class="bg-green-500/20 text-green-400 border border-green-500/50 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-widest shadow-sm backdrop-blur-sm">
                                    En Turno
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs mt-1 font-medium flex items-center">
                            <i class="fas fa-users mr-2 opacity-50"></i> {{ $guardia->users->filter(fn($u) => !$u->replacedBy)->count() }} Efectivos
                        </p>
                    </div>

                    <div class="flex items-center gap-1 relative z-10 bg-slate-800/50 p-1 rounded-lg backdrop-blur-sm border border-slate-700">
                        @if(!$guardia->is_active_week)
                            <form action="{{ route('admin.guardias.activate_week', $guardia->id) }}" method="POST" onsubmit="return confirm('¿Marcar esta guardia como ACTIVA para la semana?');">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-green-400 p-2 rounded-md hover:bg-slate-700/50 transition-all group" title="Activar Semana">
                                    <i class="fas fa-calendar-check group-hover:scale-110 transition-transform"></i>
                                </button>
                            </form>
                        @endif
                        
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
                    @if($guardia->users->isEmpty())
                        <div class="flex-grow flex flex-col items-center justify-center text-slate-400 p-8 min-h-[150px]">
                            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-2">
                                <i class="fas fa-user-slash text-xl opacity-50"></i>
                            </div>
                            <p class="text-xs font-medium">Sin personal asignado</p>
                        </div>
                    @else
                        @if($guardia->is_active_week)
                            <form action="{{ route('admin.guardias.bulk_update', $guardia->id) }}" method="POST" id="attendance-form-{{ $guardia->id }}" class="flex flex-col">
                                @csrf
                        @endif
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 overflow-y-auto max-h-[650px] pr-2 p-3 content-start items-start auto-rows-min">
                                @foreach($guardia->users as $user)
                                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm relative overflow-visible group hover:border-blue-400 hover:shadow-md transition-all duration-200 flex flex-col items-center p-2 gap-2 text-center">
                                        
                                        <!-- Titular Toggle (Top Right) -->
                                        <div class="absolute top-2 right-2 z-10">
                                            <button type="button" 
                                                onclick="confirmToggleTitular('{{ route('admin.bomberos.toggle_titular', $user->id) }}')" 
                                                class="w-6 h-6 flex items-center justify-center rounded-full border shadow-sm transition-all hover:scale-110 {{ $user->is_titular ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200' }}"
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
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $user->attendance_status == 'constituye' ? 'bg-green-500' : ($user->attendance_status == 'ausente' ? 'bg-red-500' : 'bg-slate-300') }}"></div>
                                        </div>

                                        <!-- Main Info (Centered) -->
                                        <div class="w-full flex flex-col items-center gap-1 mb-1">
                                            <h4 class="font-bold text-slate-800 text-sm leading-tight uppercase tracking-tight px-2" title="{{ $user->name }} {{ $user->last_name_paternal }}">
                                                {{ $user->name }}
                                                <span class="block text-xs font-extrabold text-slate-600 mt-0.5">{{ $user->last_name_paternal }}</span>
                                            </h4>
                                            
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                {{ $user->role == 'jefe_guardia' ? 'Jefe de Guardia' : 'Bombero' }}
                                            </span>

                                            <!-- Replacements Status Text -->
                                            @if($user->jobReplacement)
                                                <div class="flex items-center gap-1 text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100 shadow-sm mt-1">
                                                    <i class="fas fa-right-left text-[9px]"></i> <span class="text-[9px] font-bold">Reemplaza a {{ substr($user->jobReplacement->name, 0, 1) }}. {{ $user->jobReplacement->last_name_paternal }}</span>
                                                </div>
                                            @endif
                                            @if($user->replacedBy)
                                                <div class="flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 shadow-sm mt-1">
                                                    <i class="fas fa-user-shield text-[9px]"></i> <span class="text-[9px] font-bold">Cubierto por {{ substr($user->replacedBy->name, 0, 1) }}. {{ $user->replacedBy->last_name_paternal }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Controls Area (Full Width) -->
                                        <div class="w-full mt-auto space-y-2">
                                            @if(!$guardia->is_active_week)
                                                <!-- Edit/Delete Actions -->
                                                <div class="flex justify-center gap-2">
                                                    <a href="{{ route('admin.bomberos.edit', $user->id) }}" class="p-2 rounded-lg bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-slate-200" title="Editar">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.bomberos.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                                                        @csrf @method('DELETE')
                                                        <button class="p-2 rounded-lg bg-slate-50 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors border border-slate-200" title="Eliminar">
                                                            <i class="fas fa-trash-can text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                
                                                <!-- Static Badges -->
                                                <div class="flex justify-center gap-2 mt-2 pt-2 border-t border-slate-50">
                                                    @if($user->is_rescue_operator) 
                                                        <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] font-bold border border-orange-200" title="Rescate">R</span>
                                                    @endif
                                                    @if($user->is_trauma_assistant) 
                                                        <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[10px] font-bold border border-red-200" title="Trauma">T</span>
                                                    @endif
                                                </div>
                                            @else
                                                <!-- Attendance Controls -->
                                                @if($user->replacedBy)
                                                    <div class="w-full text-center py-2 bg-slate-100 text-slate-400 font-bold text-xs rounded-lg border border-slate-200 uppercase tracking-wide flex items-center justify-center gap-1.5 opacity-75">
                                                        <i class="fas fa-ban"></i> Cubierto
                                                    </div>
                                                @else
                                                    <!-- Status Selector (Bar Style) -->
                                                    <div class="relative w-full">
                                                        <select name="users[{{ $user->id }}][attendance_status]" 
                                                            data-user-id="{{ $user->id }}"
                                                            onchange="toggleReplacementBtn(this, 'btn-replacement-{{ $user->id }}')"
                                                            class="attendance-select w-full text-[10px] font-black uppercase py-2 pl-2 pr-6 rounded-md border-0 cursor-pointer text-center appearance-none transition-colors shadow-sm
                                                            {{ $user->attendance_status == 'constituye' ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : '' }}
                                                            {{ $user->attendance_status == 'reemplazo' ? 'bg-purple-100 text-purple-700 ring-1 ring-purple-200' : '' }}
                                                            {{ $user->attendance_status == 'permiso' ? 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200' : '' }}
                                                            {{ $user->attendance_status == 'ausente' ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : '' }}
                                                            {{ $user->attendance_status == 'falta' ? 'bg-red-200 text-red-800 ring-1 ring-red-300' : '' }}
                                                            {{ $user->attendance_status == 'licencia' ? 'bg-blue-100 text-blue-700 ring-1 ring-blue-200' : '' }}
                                                        ">
                                                            <option value="constituye" {{ $user->attendance_status == 'constituye' ? 'selected' : '' }}>Constituye</option>
                                                            <option value="reemplazo" {{ $user->attendance_status == 'reemplazo' ? 'selected' : '' }}>Reemplazo</option>
                                                            <option value="permiso" {{ $user->attendance_status == 'permiso' ? 'selected' : '' }}>Permiso</option>
                                                            <option value="ausente" {{ $user->attendance_status == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                                            <option value="falta" {{ $user->attendance_status == 'falta' ? 'selected' : '' }}>Falta</option>
                                                            <option value="licencia" {{ $user->attendance_status == 'licencia' ? 'selected' : '' }}>Licencia</option>
                                                        </select>
                                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-current opacity-50">
                                                            <i class="fas fa-sort text-[10px]"></i>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Toggle Buttons (R / T) - Bottom Row -->
                                                    <div class="flex justify-center gap-3 mt-2 px-2">
                                                        <label class="cursor-pointer select-none group flex flex-col items-center gap-1" title="Operador de Rescate">
                                                            <input type="checkbox" name="users[{{ $user->id }}][is_rescue_operator]" value="1" {{ $user->is_rescue_operator ? 'checked' : '' }} class="peer sr-only">
                                                            <span class="w-7 h-7 rounded-full bg-slate-50 border border-slate-200 peer-checked:bg-orange-500 peer-checked:border-orange-600 peer-checked:text-white text-slate-300 transition-all shadow-sm flex items-center justify-center font-bold text-[10px] group-hover:border-orange-300">
                                                                R
                                                            </span>
                                                        </label>
                                                        <label class="cursor-pointer select-none group flex flex-col items-center gap-1" title="Asistente de Trauma">
                                                            <input type="checkbox" name="users[{{ $user->id }}][is_trauma_assistant]" value="1" {{ $user->is_trauma_assistant ? 'checked' : '' }} class="peer sr-only">
                                                            <span class="w-7 h-7 rounded-full bg-slate-50 border border-slate-200 peer-checked:bg-red-500 peer-checked:border-red-600 peer-checked:text-white text-slate-300 transition-all shadow-sm flex items-center justify-center font-bold text-[10px] group-hover:border-red-300">
                                                                T
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                        @if($guardia->is_active_week)
                                <div class="p-4 border-t border-slate-100 bg-white mt-auto">
                                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-4 rounded-xl text-xs transition-all shadow-lg hover:shadow-xl flex items-center justify-center uppercase tracking-widest group transform hover:-translate-y-0.5">
                                        <span class="mr-2">Guardar Asistencia</span>
                                        <i class="fas fa-check-circle text-emerald-400 text-lg group-hover:text-emerald-300 transition-colors"></i>
                                    </button>
                                </div>
                            </form>
                        @endif

                    @endif
                </div>

                <!-- Formulario Agregar / Asignar (Colapsable) -->
                <div class="bg-white border-t border-slate-200 mt-auto shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.1)] z-10 relative">
                    <button type="button" onclick="toggleAssignForm('{{ $guardia->id }}')" class="w-full p-2 flex justify-between items-center text-[9px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-colors group outline-none focus:bg-slate-50">
                        <span class="flex items-center group-hover:text-slate-600 transition-colors"><i class="fas fa-plus mr-1.5"></i> Asignar Voluntario</span>
                        <i id="icon-assign-{{ $guardia->id }}" class="fas fa-chevron-down transition-transform duration-200 group-hover:text-slate-600"></i>
                    </button>
                    
                    <div id="form-assign-{{ $guardia->id }}" class="hidden px-3 pb-3 border-t border-slate-50">
                        <form action="{{ route('admin.guardias.assign') }}" method="POST">
                            @csrf
                            <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">
                            
                            <div class="space-y-2 pt-2">
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input list="volunteers_list_{{ $guardia->id }}" name="user_id_display" 
                                        class="w-full text-xs border-slate-200 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-100 pl-9 py-2 bg-slate-50 transition-all hover:bg-white"
                                        placeholder="Buscar voluntario..." required
                                        oninput="updateUserId(this, '{{ $guardia->id }}')">
                                    <input type="hidden" name="user_id" id="user_id_input_{{ $guardia->id }}" required>
                                    
                                    <datalist id="volunteers_list_{{ $guardia->id }}">
                                        @foreach($volunteers as $volunteer)
                                            <option data-value="{{ $volunteer->id }}" value="{{ $volunteer->name }} {{ $volunteer->last_name_paternal }} ({{ $volunteer->company ?? 'S/C' }})"></option>
                                        @endforeach
                                    </datalist>
                                </div>

                                <div class="space-y-2">
                                    <!-- Opciones Rápidas (Badges Selectables) -->
                                    <div class="flex flex-wrap gap-1.5">
                                        <label class="cursor-pointer select-none group">
                                            <input type="checkbox" name="is_shift_leader" value="1" class="peer sr-only">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold border border-slate-200 text-slate-500 bg-white transition-all
                                                peer-checked:bg-yellow-50 peer-checked:text-yellow-700 peer-checked:border-yellow-200 peer-checked:shadow-sm
                                                group-hover:border-slate-300">
                                                <i class="fas fa-star mr-1"></i> OFICIAL
                                            </span>
                                        </label>
                                        <label class="cursor-pointer select-none group">
                                            <input type="checkbox" name="is_exchange" value="1" class="peer sr-only">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold border border-slate-200 text-slate-500 bg-white transition-all
                                                peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-200 peer-checked:shadow-sm
                                                group-hover:border-slate-300">
                                                <i class="fas fa-right-left mr-1"></i> CANJE
                                            </span>
                                        </label>
                                        <label class="cursor-pointer select-none group">
                                            <input type="checkbox" name="is_penalty" value="1" class="peer sr-only">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-bold border border-slate-200 text-slate-500 bg-white transition-all
                                                peer-checked:bg-red-50 peer-checked:text-red-700 peer-checked:border-red-200 peer-checked:shadow-sm
                                                group-hover:border-slate-300">
                                                <i class="fas fa-exclamation mr-1"></i> FALTA
                                            </span>
                                        </label>
                                    </div>
                                
                                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition duration-150 shadow-md hover:shadow-lg flex items-center justify-center uppercase tracking-wider transform hover:-translate-y-0.5">
                                        <i class="fas fa-plus mr-2"></i> Asignar a Guardia
                                    </button>
                                </div>
                            </div>
                        </form>
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
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Asignar Reemplazo</h3>
                    <p class="text-sm text-slate-500 mt-1">Selecciona el voluntario que cubrirá el turno.</p>
                </div>
                <button onclick="closeReplacementModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
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
                <input type="hidden" name="original_user_id" id="modal_original_user_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Voluntario Reemplazante</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input list="modal_volunteers_list" name="replacement_user_id_display" 
                                class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-9 py-2.5 bg-slate-50"
                                placeholder="Buscar voluntario..." required
                                oninput="updateModalUserId(this)">
                            <input type="hidden" name="replacement_user_id" id="modal_replacement_user_id" required>
                        </div>
                        <datalist id="modal_volunteers_list">
                            @foreach($volunteers as $volunteer)
                                <option data-value="{{ $volunteer->id }}" value="{{ $volunteer->name }} {{ $volunteer->last_name_paternal }} ({{ $volunteer->company ?? 'S/C' }})"></option>
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
            
            // Solo mostrar el botón si se selecciona explícitamente "Reemplazo"
            if (select.value === 'reemplazo') {
                btn.classList.remove('hidden');
                btn.classList.add('flex');
                btn.style.display = ''; // Limpiar el display: none inline si existe
            } else {
                btn.classList.remove('flex');
                btn.classList.add('hidden');
                btn.style.display = 'none'; // Forzar ocultamiento inline
            }
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
            document.getElementById('modal_guardia_id').value = guardiaId;
            document.getElementById('modal_original_user_id').value = userId;
            document.getElementById('modal_original_user_name').textContent = userName;
            
            modal.classList.remove('hidden');
            // Small delay for transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div').classList.remove('scale-95');
                modal.querySelector('div').classList.add('scale-100');
            }, 10);
        }

        function closeReplacementModal() {
            const modal = document.getElementById('replacementModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div').classList.remove('scale-100');
            modal.querySelector('div').classList.add('scale-95');
            
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
            // Inicializar estado de botones según el valor actual de los selects
            document.querySelectorAll('.attendance-select').forEach(select => {
                const userId = select.getAttribute('data-user-id');
                if(userId) {
                    toggleReplacementBtn(select, 'btn-replacement-' + userId);
                }
            });
        });
    </script>
@endsection
