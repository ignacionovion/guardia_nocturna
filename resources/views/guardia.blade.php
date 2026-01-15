@extends('layouts.app')

@section('content')
    <!-- Header de Sección -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-clipboard-list mr-3 text-red-700"></i> Libro de Guardia
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Registro y control de asistencia nocturna</p>
        </div>
        
        @if($shift)
            <form action="{{ route('guardia.close', $shift->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de finalizar el turno de guardia? Esta acción cerrará el registro de asistencia.');">
                @csrf
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center uppercase text-sm tracking-wide">
                    <i class="fas fa-lock mr-2"></i> Finalizar Turno
                </button>
            </form>
        @endif
    </div>

    <!-- Alertas y Mensajes -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-r shadow-sm flex items-center" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle mr-2 text-xl"></i>
                <p class="font-bold">Se encontraron errores:</p>
            </div>
            <ul class="list-disc list-inside ml-2 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$shift)
        <!-- Estado: Sin Guardia Activa -->
        <div class="max-w-2xl mx-auto mt-12">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 p-8 text-center border-b border-slate-100">
                    <div class="w-24 h-24 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <i class="fas fa-shield-halved text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">Guardia No Constituida</h2>
                    <p class="text-slate-500">Actualmente no hay un turno de guardia nocturna activo en el sistema.</p>
                </div>
                <div class="p-8 bg-white text-center">
                    <p class="text-slate-600 mb-8 max-w-md mx-auto">Para comenzar el registro de asistencia y asignar camas, debes iniciar un nuevo turno de guardia.</p>
                    
                    <form action="{{ route('guardia.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all w-full md:w-auto uppercase tracking-wider flex items-center justify-center mx-auto">
                            <i class="fas fa-circle-play mr-3 text-xl"></i> Constituir Guardia Ahora
                        </button>
                    </form>
                </div>
            </div>
        </div>

    @else
        <!-- Estado: Guardia Activa -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Columna Izquierda: Panel de Control (Asignación) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 sticky top-24 overflow-hidden">
                    <div class="bg-slate-800 px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                        <h2 class="font-bold text-white flex items-center">
                            <i class="fas fa-user-plus mr-2 text-blue-400"></i> Registrar Asistencia
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route('guardia.add_user', $shift->id) }}" method="POST">
                            @csrf
                            
                            <div class="mb-5">
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="user_id">
                                    Voluntario
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-slate-400"></i>
                                    </div>
                                    <input list="volunteers-list" name="user_id" id="user_id" 
                                        class="pl-10 shadow-sm appearance-none border border-slate-300 rounded-lg w-full py-2.5 px-3 text-slate-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                        placeholder="Buscar por nombre..." required autocomplete="off">
                                </div>
                                <datalist id="volunteers-list">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name_paternal }} - {{ $user->company }}</option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="mb-5">
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="assignment_type">
                                    Rol en Guardia
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-tag text-slate-400"></i>
                                    </div>
                                    <select name="assignment_type" id="assignment_type" 
                                        class="pl-10 shadow-sm appearance-none border border-slate-300 rounded-lg w-full py-2.5 px-3 text-slate-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white" 
                                        required onchange="toggleReemplazo(this.value)">
                                        <option value="Bombero">Bombero</option>
                                        <option value="Oficial a cargo">Oficial a cargo</option>
                                        <option value="Canje">Canje</option>
                                        <option value="Asistente trauma">Asistente trauma</option>
                                        <option value="Operador de rescate">Operador de rescate</option>
                                        <option value="Reemplazo">Reemplazo</option>
                                        <option value="Cumple falta">Cumple falta</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6 hidden bg-slate-50 p-4 rounded-lg border border-slate-200" id="reemplazo_div">
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="replaced_user_id">
                                    Reemplaza a:
                                </label>
                                <div class="relative">
                                    <select name="replaced_user_id" id="replaced_user_id" class="shadow-sm appearance-none border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">Seleccione voluntario...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name_paternal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center uppercase text-sm tracking-wide">
                                <i class="fas fa-user-check mr-2"></i> Ingresar a Guardia
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Listado de Personal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                    <div class="bg-white px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 flex items-center">
                                <span class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></span>
                                Guardia Activa
                            </h2>
                            <p class="text-slate-500 text-sm mt-1 ml-6">
                                <i class="fas fa-calendar-days mr-1"></i> {{ \Carbon\Carbon::parse($shift->date)->translatedFormat('l d \d\e F, Y') }}
                                <span class="mx-2 text-slate-300">|</span>
                                <i class="fas fa-clock mr-1"></i> Inicio: {{ \Carbon\Carbon::parse($shift->created_at)->format('H:i') }} hrs
                            </p>
                        </div>
                        <div class="bg-green-100 text-green-800 text-xs font-bold px-4 py-2 rounded-full border border-green-200 shadow-sm uppercase tracking-wide">
                            En Curso
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Voluntario</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rol / Calidad</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Llegada</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($shift->users->whereNull('end_time') as $shiftUser)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                                                    {{ substr($shiftUser->user->name, 0, 1) }}{{ substr($shiftUser->user->last_name_paternal, 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-bold text-slate-800">{{ $shiftUser->user->name }} {{ $shiftUser->user->last_name_paternal }}</div>
                                                    <div class="text-xs text-slate-500 font-medium">{{ $shiftUser->user->company }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $shiftUser->assignment_type }}
                                            </span>
                                            @if($shiftUser->assignment_type === 'Reemplazo' && $shiftUser->replacedUser)
                                                <div class="text-xs text-slate-400 mt-1 flex items-center">
                                                    <i class="fas fa-right-left mr-1 text-xs"></i> {{ $shiftUser->replacedUser->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-medium">
                                            <i class="fas fa-clock mr-1 text-slate-400"></i> {{ \Carbon\Carbon::parse($shiftUser->start_time)->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('guardia.remove_user', ['shiftId' => $shift->id, 'userId' => $shiftUser->user_id]) }}" method="POST" onsubmit="return confirm('¿Registrar salida de este voluntario?');" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Registrar Salida">
                                                    <i class="fas fa-right-from-bracket"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                            <i class="fas fa-users-slash text-4xl mb-3 opacity-50"></i>
                                            <p class="font-medium">No hay personal activo registrado en este momento.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($shift->users->whereNotNull('end_time')->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden opacity-90">
                        <div class="bg-slate-50 px-6 py-3 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide">
                                <i class="fas fa-history mr-2"></i> Historial de Turno (Retirados)
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Voluntario</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Rol</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Horario</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($shift->users->whereNotNull('end_time') as $shiftUser)
                                        <tr>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-slate-700">
                                                {{ $shiftUser->user->name }} {{ $shiftUser->user->last_name_paternal }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-500">
                                                {{ $shiftUser->assignment_type }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-500">
                                                {{ \Carbon\Carbon::parse($shiftUser->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shiftUser->end_time)->format('H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <script>
            function toggleReemplazo(value) {
                const div = document.getElementById('reemplazo_div');
                const select = document.getElementById('replaced_user_id');
                if (value === 'Reemplazo') {
                    div.classList.remove('hidden');
                    select.required = true;
                } else {
                    div.classList.add('hidden');
                    select.required = false;
                    select.value = '';
                }
            }
        </script>
    @endif
@endsection
