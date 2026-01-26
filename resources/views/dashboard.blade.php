@extends('layouts.app')

@section('content')
    @if(isset($myGuardia))
        @php
            // Filtrar personal activo (excluyendo los que están siendo reemplazados)
            $activeStaff = $myStaff->filter(fn($u) => !$u->replacedBy);
        @endphp
        <!-- VISTA ESPECÍFICA PARA CUENTA DE GUARDIA -->
        <div class="mb-6 border-b border-slate-200 pb-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5 block">Centro de Mando</span>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">
                        <span class="text-red-700">Guardia</span> {{ $myGuardia->name }}
                    </h1>
                    <p class="text-slate-500 font-medium text-xs mt-0.5">Gestión Operativa y Control de Personal</p>
                </div>
                <!-- Reloj -->
                <div class="bg-slate-900 text-white px-4 py-2 rounded-lg shadow-lg border-2 border-slate-700 flex items-center gap-3">
                    <div class="flex flex-col items-end border-r border-slate-600 pr-3">
                        <span class="text-[9px] text-slate-400 uppercase tracking-wider font-bold">Hora Local</span>
                        <span class="text-[9px] text-green-500 font-bold animate-pulse">OPERATIVO</span>
                    </div>
                    <div class="flex items-center">
                        <span id="digital-clock" class="text-2xl font-mono font-bold tracking-widest text-white drop-shadow-md">--:--:--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Rápidos de la Guardia (4 Columnas) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <!-- Dotación Total -->
            <div class="bg-white p-3 rounded-xl shadow-sm border-l-4 border-blue-600 relative overflow-hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dotación</p>
                        <p class="text-xl font-black text-slate-800">{{ $activeStaff->count() }} <span class="text-[10px] font-bold text-slate-400">Voluntarios</span></p>
                    </div>
                    <div class="bg-blue-50 p-2 rounded-lg text-blue-600"><i class="fas fa-users text-sm"></i></div>
                </div>
            </div>
            
            <!-- Conductores -->
            <div class="bg-white p-3 rounded-xl shadow-sm border-l-4 border-indigo-600 relative overflow-hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Conductores</p>
                        <p class="text-xl font-black text-slate-800">{{ $activeStaff->where('is_driver', true)->count() }} <span class="text-[10px] font-bold text-slate-400">Habitaciones</span></p>
                    </div>
                    <div class="bg-indigo-50 p-2 rounded-lg text-indigo-600"><i class="fas fa-truck text-sm"></i></div>
                </div>
            </div>
            
            <!-- Operadores de Rescate -->
            <div class="bg-white p-3 rounded-xl shadow-sm border-l-4 border-orange-500 relative overflow-hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Operadores Rescate</p>
                        <p class="text-xl font-black text-slate-800">{{ $activeStaff->where('is_rescue_operator', true)->count() }} <span class="text-[10px] font-bold text-slate-400">Operadores.</span></p>
                    </div>
                    <div class="bg-orange-50 p-2 rounded-lg text-orange-500"><i class="fas fa-tools text-sm"></i></div>
                </div>
            </div>

            <!-- Asistentes de Trauma -->
            <div class="bg-white p-3 rounded-xl shadow-sm border-l-4 border-red-600 relative overflow-hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Asistente Trauma</p>
                        <p class="text-xl font-black text-slate-800">{{ $activeStaff->where('is_trauma_assistant', true)->count() }} <span class="text-[10px] font-bold text-slate-400">Asistentes.</span></p>
                    </div>
                    <div class="bg-red-50 p-2 rounded-lg text-red-600"><i class="fas fa-medkit text-sm"></i></div>
                </div>
            </div>
        </div>

        <!-- Sección Principal: Dotación (Ancho Completo - 5 Cols) -->
        <div class="mb-10">
            <div class="flex justify-between items-end mb-4">
                <h2 class="font-bold text-slate-700 flex items-center text-lg uppercase tracking-wide">
                    <i class="fas fa-user-shield mr-2 text-red-600"></i> Dotación de la Unidad
                </h2>
                <span class="text-[10px] font-bold text-slate-400 bg-white px-3 py-1 rounded-full shadow-sm border border-slate-200">
                    {{ $myStaff->filter(fn($u) => !$u->replacedBy)->count() }} Efectivos en Lista
                </span>
            </div>
            
            <div class="flex flex-col space-y-2 overflow-y-auto max-h-[600px] pr-1">
                @forelse($myStaff as $staff)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all p-3 flex items-center gap-3 {{ $staff->replacedBy ? 'bg-slate-50 opacity-75' : '' }}">
                        <!-- Status Bar (Left) -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 {{ $staff->role == 'jefe_guardia' ? 'bg-red-500' : 'bg-slate-200' }}"></div>

                        <!-- Avatar -->
                        <div class="relative shrink-0 ml-1">
                            <div class="w-12 h-12 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500 font-bold border border-slate-100 text-sm shadow-sm">
                                {{ substr($staff->name, 0, 1) }}{{ substr($staff->last_name_paternal, 0, 1) }}
                            </div>
                            <a href="{{ route('admin.bomberos.edit', $staff->id) }}" class="absolute -right-1 -top-1 text-slate-300 hover:text-blue-600 bg-white rounded-full p-0.5 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-cog text-[10px]"></i>
                            </a>
                        </div>

                        <!-- Info -->
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $staff->name }} {{ $staff->last_name_paternal }}">
                                    {{ $staff->name }} {{ $staff->last_name_paternal }}
                                </h4>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border {{ $staff->role == 'jefe_guardia' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-slate-50 text-slate-400 border-slate-100' }}">
                                    {{ $staff->role == 'jefe_guardia' ? 'Jefe' : 'Bombero' }}
                                </span>
                            </div>
                            
                             <!-- Specialties -->
                            <div class="flex gap-1 mt-1 text-slate-300">
                                @if($staff->is_driver) <i class="fas fa-truck text-[10px] text-blue-500" title="Conductor"></i> @endif
                                @if($staff->is_rescue_operator) <i class="fas fa-tools text-[10px] text-orange-500" title="Rescate"></i> @endif
                                @if($staff->is_trauma_assistant) <i class="fas fa-medkit text-[10px] text-red-500" title="Trauma"></i> @endif
                                @if(!$staff->is_driver && !$staff->is_rescue_operator && !$staff->is_trauma_assistant) <span class="text-[9px] text-slate-300">-</span> @endif
                            </div>
                        </div>

                        <!-- Status Badge & Time -->
                        <div class="shrink-0 text-right flex flex-col items-end gap-1">
                            @if($staff->replacedBy)
                                <div class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 flex items-center justify-center gap-1">
                                    <i class="fas fa-ban text-[8px]"></i> CUBIERTO
                                </div>
                            @elseif($staff->attendance_status == 'constituye')
                                <div class="text-[9px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-100 flex items-center justify-center gap-1">
                                    <i class="fas fa-check text-[8px]"></i> EN TURNO
                                </div>
                            @else
                                <div class="text-[9px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">
                                    {{ $staff->attendance_status }}
                                </div>
                            @endif
                            
                            <span class="text-[9px] font-medium text-slate-400">
                                {{ $staff->service_time }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                        <i class="fas fa-users-slash text-2xl mb-2 opacity-30"></i>
                        <p class="text-xs">Sin personal asignado a esta guardia.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sección Inferior: Widgets (3 Columnas) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Columna 1: Cumpleaños -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <h2 class="font-bold text-slate-700 flex items-center text-sm uppercase">
                        <i class="fas fa-birthday-cake text-amber-500 mr-2"></i> Cumpleaños Mes
                    </h2>
                </div>
                <div class="p-4">
                    @if($birthdays->isEmpty())
                        <p class="text-center text-xs text-slate-400 py-4">Sin cumpleaños este mes.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($birthdays->take(3) as $user)
                                <li class="flex items-center gap-3 p-1.5 rounded hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
                                    <div class="text-[10px] font-bold text-slate-500 w-5 text-center bg-slate-100 rounded py-0.5">
                                        {{ \Carbon\Carbon::parse($user->birthdate)->format('d') }}
                                    </div>
                                    <div class="text-xs font-bold text-slate-700 truncate">
                                        {{ $user->name }} {{ $user->last_name_paternal }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if($birthdays->count() > 3)
                            <div class="mt-2 text-center border-t border-slate-50 pt-1">
                                <span class="text-[10px] text-slate-400 font-bold">+{{ $birthdays->count() - 3 }} más</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Columna 2: Acciones Rápidas -->
            <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-5 text-white relative overflow-hidden h-full">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16"></div>
                <h3 class="font-bold text-sm mb-4 flex items-center uppercase tracking-wider"><i class="fas fa-rocket mr-2 text-red-500"></i> Acciones Rápidas</h3>
                <div class="space-y-2 relative z-10">
                    <a href="{{ route('camas') }}" class="block bg-slate-700 hover:bg-slate-600 p-2.5 rounded-lg transition-all flex items-center justify-between group text-sm">
                        <span class="font-medium">Gestionar Camas</span>
                        <i class="fas fa-bed text-slate-400 group-hover:text-white transition-colors"></i>
                    </a>
                    <button onclick="openNoveltyModal()" class="w-full text-left block bg-slate-700 hover:bg-slate-600 p-2.5 rounded-lg transition-all flex items-center justify-between group text-sm">
                        <span class="font-medium">Registrar Novedad</span>
                        <i class="fas fa-pen-to-square text-slate-400 group-hover:text-white transition-colors"></i>
                    </button>
                    <a href="{{ route('admin.guardias') }}" class="block bg-slate-700 hover:bg-slate-600 p-2.5 rounded-lg transition-all flex items-center justify-between group text-sm">
                        <span class="font-medium">Gestionar Dotación</span>
                        <i class="fas fa-users-gear text-slate-400 group-hover:text-white transition-colors"></i>
                    </a>
                </div>
            </div>

            <!-- Columna 3: Últimas Novedades -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <h2 class="font-bold text-slate-700 flex items-center text-sm uppercase">
                        <i class="fas fa-bullhorn text-slate-600 mr-2"></i> Últimas Novedades
                    </h2>
                </div>
                <div class="p-4">
                     @if($novelties->isEmpty())
                         <div class="text-center py-4 text-slate-400 text-xs">Sin novedades recientes.</div>
                     @else
                        <div class="space-y-3">
                            @foreach($novelties->take(2) as $novelty)
                                <div class="bg-slate-50 p-2.5 rounded border border-slate-100">
                                    <h3 class="font-bold text-slate-800 text-xs mb-0.5 truncate">{{ $novelty->title }}</h3>
                                    <p class="text-[10px] text-slate-600 line-clamp-2 leading-tight">{{ $novelty->description }}</p>
                                    <span class="text-[9px] text-slate-400 mt-1 block">{{ $novelty->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                     @endif
                </div>
            </div>
        </div>

    @else
        <!-- VISTA ADMIN / GENERAL (DASHBOARD ORIGINAL) -->
        <!-- Header del Dashboard -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-gauge-high mr-3 text-red-700"></i> Panel de Control
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Resumen operativo de la unidad</p>
        </div>
        
        <!-- Reloj Digital Estilo Panel -->
        <div class="bg-slate-900 text-white px-6 py-3 rounded-lg shadow-lg border-2 border-slate-700 flex items-center gap-4">
            <div class="flex flex-col items-end border-r border-slate-600 pr-4">
                <span class="text-xs text-slate-400 uppercase tracking-wider font-bold">Hora Local</span>
                <span class="text-xs text-red-500 font-bold animate-pulse">EN LÍNEA</span>
            </div>
            <div class="flex items-center">
                <span id="digital-clock" class="text-3xl font-mono font-bold tracking-widest text-white drop-shadow-md">--:--:--</span>
            </div>
        </div>
    </div>

    <!-- Grid de KPIs (Tarjetas Principales) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <!-- Tarjeta Camas -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Disponibilidad</p>
                    <h3 class="text-slate-700 font-bold text-lg">Camas Guardia</h3>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $availableBeds }} <span class="text-lg text-slate-400 font-normal">/ {{ $totalBeds }}</span></p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-bed text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tarjeta Estado Guardia -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 w-1 h-full bg-red-600"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Estado Operativo</p>
                    <h3 class="text-slate-700 font-bold text-lg">Guardia Nocturna</h3>
                    
                    @if(isset($activeGuardia))
                        <div class="mt-1">
                            <span class="text-xs font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded uppercase tracking-wide border border-red-200">
                                <i class="fas fa-calendar-week mr-1"></i> {{ $activeGuardia->name }}
                            </span>
                        </div>
                    @endif

                    <p class="text-xl font-bold mt-2">
                        @if($currentShift)
                            <span class="text-green-600 flex items-center gap-2">
                                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span> CONSTITUIDA
                            </span>
                        @else
                            <!-- <span class="text-slate-400 italic font-normal text-base">Sin constituir</span> -->
                        @endif
                    </p>
                </div>
                <div class="bg-red-50 p-3 rounded-lg text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                    <i class="fas fa-shield-halved text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tarjeta Cumpleaños -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Efemérides</p>
                    <h3 class="text-slate-700 font-bold text-lg">Cumpleaños Mes</h3>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $birthdays->count() }}</p>
                </div>
                <div class="bg-amber-50 p-3 rounded-lg text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                    <i class="fas fa-birthday-cake text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tarjeta Novedades -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 w-1 h-full bg-slate-600"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bitácora</p>
                    <h3 class="text-slate-700 font-bold text-lg">Novedades</h3>
                    <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $novelties->count() }}</p>
                </div>
                <div class="bg-slate-100 p-3 rounded-lg text-slate-600 group-hover:bg-slate-600 group-hover:text-white transition-colors">
                    <i class="fas fa-clipboard-check text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Inferior -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Lista de Cumpleaños -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="font-bold text-slate-700 flex items-center">
                    <i class="fas fa-calendar-days text-amber-500 mr-2"></i> Próximos Cumpleaños
                </h2>
                <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-1 rounded">{{ date('F') }}</span>
            </div>
            <div class="p-6">
                @if($birthdays->isEmpty())
                    <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-calendar-xmark text-4xl mb-3 opacity-50"></i>
                        <p>No hay cumpleaños registrados para este mes.</p>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach($birthdays as $user)
                            <li class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                                        {{ substr($user->name, 0, 1) }}{{ substr($user->last_name_paternal, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-700 text-sm">{{ $user->name }} {{ $user->last_name_paternal }}</p>
                                        <p class="text-xs text-slate-500 uppercase">{{ $user->role == 'bombero' ? 'Bombero' : 'Oficial' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-bold text-slate-700">{{ \Carbon\Carbon::parse($user->birthdate)->format('d') }}</span>
                                    <span class="text-xs text-slate-400 uppercase">{{ \Carbon\Carbon::parse($user->birthdate)->format('M') }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Últimas Novedades -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="font-bold text-slate-700 flex items-center">
                    <i class="fas fa-bullhorn text-slate-600 mr-2"></i> Bitácora de Novedades
                </h2>
                <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-800">Ver todas</a>
            </div>
            <div class="p-6">
                @if($novelties->isEmpty())
                     <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-clipboard-list text-4xl mb-3 opacity-50"></i>
                        <p>No se han registrado novedades recientes.</p>
                    </div>
                @else
                    <div class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                        @foreach($novelties as $novelty)
                            <div class="ml-6 relative">
                                <span class="absolute -left-[31px] top-0 flex items-center justify-center w-4 h-4 bg-white rounded-full ring-4 ring-white">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                </span>
                                <h3 class="text-sm font-bold text-slate-800">{{ $novelty->title }}</h3>
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $novelty->description }}</p>
                                <span class="text-xs text-slate-400 mt-2 block font-medium">
                                    <i class="fas fa-clock mr-1"></i> {{ $novelty->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @endif

    <div id="noveltyModal" class="fixed inset-0 bg-slate-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-pen-to-square text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800">Registrar Novedad</h3>
                <p class="text-slate-500 text-sm mt-1">Bitácora de la Guardia</p>
            </div>
            
            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border {{ $errors->has('title') ? 'border-red-500' : 'border-slate-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" placeholder="Ej: Falla en carro B-3" required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Tipo</label>
                    <select name="type" class="w-full px-4 py-2.5 border {{ $errors->has('type') ? 'border-red-500' : 'border-slate-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="Informativa" {{ old('type') == 'Informativa' ? 'selected' : '' }}>Informativa</option>
                        <option value="Incidente" {{ old('type') == 'Incidente' ? 'selected' : '' }}>Incidente</option>
                        <option value="Mantención" {{ old('type') == 'Mantención' ? 'selected' : '' }}>Mantención</option>
                        <option value="Urgente" {{ old('type') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border {{ $errors->has('description') ? 'border-red-500' : 'border-slate-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px]" placeholder="Detalle de la novedad..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeNoveltyModal()" class="w-1/2 px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors uppercase text-sm">
                        Cancelar
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2.5 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition-colors shadow-md uppercase text-sm">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.openNoveltyModal = function() {
            const modal = document.getElementById('noveltyModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Auto-focus en el primer campo
                setTimeout(() => {
                    const firstInput = modal.querySelector('input[name="title"]');
                    if(firstInput) firstInput.focus();
                }, 100);
            } else {
                console.error('Modal de novedades no encontrado');
            }
        }

        window.closeNoveltyModal = function() {
            const modal = document.getElementById('noveltyModal');
            if (modal) modal.classList.add('hidden');
        }
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                window.closeNoveltyModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Si hay errores de validación, reabrir el modal automáticamente
            @if($errors->any())
                openNoveltyModal();
            @endif

            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-CL', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false
                });
                
                const clockElement = document.getElementById('digital-clock');
                if(clockElement) {
                    clockElement.textContent = timeString;
                }
            }
            
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
@endsection
