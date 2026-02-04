@extends('layouts.app')

@section('content')
    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        @php
            // Filtrar personal activo (excluyendo los que están siendo reemplazados)
            $activeStaff = $myStaff->filter(function ($u) use ($replacementByOriginal) {
                return !($replacementByOriginal && $replacementByOriginal->has($u->id));
            });

            $onDutyStaff = $activeStaff->filter(function ($u) {
                return in_array($u->attendance_status, ['constituye', 'reemplazo'], true);
            });

            $offDutyStaff = $myStaff->reject(function ($u) use ($replacementByOriginal) {
                $isReplaced = (bool) ($replacementByOriginal && $replacementByOriginal->has($u->id));
                return !$isReplaced && in_array($u->attendance_status, ['constituye', 'reemplazo'], true);
            });

            $statusCounts = [
                'constituye' => $activeStaff->where('attendance_status', 'constituye')->count(),
                'reemplazo' => $activeStaff->where('attendance_status', 'reemplazo')->count(),
                'permiso' => $activeStaff->where('attendance_status', 'permiso')->count(),
                'ausente' => $activeStaff->where('attendance_status', 'ausente')->count(),
                'licencia' => $activeStaff->where('attendance_status', 'licencia')->count(),
                'falta' => $activeStaff->where('attendance_status', 'falta')->count(),
            ];
        @endphp
        <!-- VISTA ESPECÍFICA PARA CUENTA DE GUARDIA (FULLSCREEN TARJETAS) -->
        <div class="w-full min-h-[calc(100vh-4rem)] px-4 md:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between mb-5 gap-4 border-b border-slate-200 pb-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="bg-red-700 p-2 rounded-lg text-white shadow-lg border border-red-600 shrink-0">
                        <i class="fas fa-gauge-high text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Panel de Control</div>
                        <div class="mt-0.5 flex items-center gap-3 min-w-0">
                            <div class="text-2xl md:text-3xl font-black tracking-tight text-slate-800 uppercase truncate">{{ $myGuardia->name }}</div>
                            @if(isset($isMyGuardiaOnDuty) && $isMyGuardiaOnDuty)
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-green-200 bg-green-50 text-green-700 shrink-0">EN TURNO</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-100 text-slate-700 shrink-0">FUERA DE TURNO</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-xs font-medium text-slate-500">{{ $activeStaff->count() }} bomberos | {{ $onDutyStaff->count() }} presentes</div>
                    </div>
                </div>

                <div class="flex-1 flex items-center justify-center">
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('admin.guardias.replacements.cleanup', $myGuardia->id) }}" class="hidden md:block" onsubmit="return confirm('¿Cerrar todos los reemplazos activos de la guardia?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-900 font-black py-2.5 px-5 rounded-full text-[10px] transition-all shadow-sm hover:shadow-md uppercase tracking-widest border border-slate-200">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-600 text-white">
                                    <i class="fas fa-rotate-left text-[10px]"></i>
                                </span>
                                <span>Limpiar Reemplazos</span>
                            </button>
                        </form>
                        <a href="{{ route('guardia.aseo') }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-2.5 px-5 rounded-full text-[10px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 text-white">
                                <i class="fas fa-broom text-[10px]"></i>
                            </span>
                            <span>Asignación de Aseo</span>
                        </a>
                        <button form="guardia-attendance-form" type="submit" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-2.5 px-5 rounded-full text-[10px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 text-white">
                                <i class="fas fa-floppy-disk text-[10px]"></i>
                            </span>
                            <span>Guardar Asistencia</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    @if(isset($hasAttendanceSavedToday) && $hasAttendanceSavedToday)
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 shrink-0">GUARDIA CONSTITUIDA</span>
                    @else
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-700 shrink-0">SIN REGISTRAR ASISTENCIA</span>
                    @endif
                    <div class="bg-slate-900 text-white px-5 py-2.5 rounded-lg shadow-lg border-2 border-slate-700 flex items-center gap-4">
                        <div class="flex flex-col items-end border-r border-slate-600 pr-4">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Hora Local</span>
                            <span class="text-[10px] text-red-500 font-bold animate-pulse">EN LÍNEA</span>
                        </div>
                        <div class="flex items-center">
                            <span id="digital-clock" class="text-2xl md:text-3xl font-mono font-bold tracking-widest text-white drop-shadow-md">--:--:--</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-white hover:bg-slate-100 text-slate-700 h-10 px-3 rounded-lg border border-slate-200 shadow-sm flex items-center justify-center gap-2" title="Cerrar sesión">
                            <i class="fas fa-right-from-bracket"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-4 md:p-5 shadow-sm">
                    <form id="guardia-attendance-form" method="POST" action="{{ route('admin.guardias.bulk_update', $myGuardia->id) }}">
                        @csrf

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                            @forelse($activeStaff as $staff)
                                @php
                                    $status = $staff->attendance_status;
                                    $repAsReplacement = isset($replacementByReplacement) ? $replacementByReplacement->get($staff->id) : null;
                                    $statusDotClass = match ($status) {
                                        'constituye' => 'bg-emerald-400',
                                        'reemplazo' => 'bg-purple-400',
                                        'permiso' => 'bg-amber-400',
                                        'ausente' => 'bg-slate-500',
                                        'licencia' => 'bg-blue-400',
                                        'falta' => 'bg-red-400',
                                        default => 'bg-slate-500',
                                    };

                                    $statusHeaderClass = match ($status) {
                                        'constituye' => 'bg-emerald-700',
                                        'reemplazo' => 'bg-purple-700',
                                        'permiso' => 'bg-amber-700',
                                        'ausente' => 'bg-slate-800',
                                        'licencia' => 'bg-blue-700',
                                        'falta' => 'bg-red-700',
                                        default => 'bg-slate-900',
                                    };
                                @endphp
                                <input type="hidden" name="users[{{ $staff->id }}][attendance_status]" id="attendance-status-{{ $staff->id }}" value="{{ $status }}">

                                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col" data-card-user="{{ $staff->id }}">
                                    <div id="card-header-{{ $staff->id }}" class="{{ $statusHeaderClass }} text-white px-2 py-1.5 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <div class="text-[12px] font-black truncate" title="{{ $staff->name }} {{ $staff->last_name_paternal }}">
                                                {{ strtoupper($staff->last_name_paternal ?: $staff->name) }}
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                                <i class="fas fa-user-group opacity-70"></i>
                                                <span class="font-black">{{ $staff->is_shift_leader ? 'Jefe' : 'Bombero' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div id="card-dot-{{ $staff->id }}" class="w-3 h-3 rounded-full {{ $statusDotClass }}"></div>
                                        </div>
                                    </div>

                                <div class="p-1.5 flex-1 flex flex-col">
                                    @if($repAsReplacement)
                                        <div class="mb-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-2 text-purple-800">
                                            <div class="text-[11px] font-black uppercase tracking-widest">Reemplaza a</div>
                                            <div class="text-sm font-black leading-tight">
                                                {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->name ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->last_name_paternal ?? '')))[0] ?? '' }}
                                            </div>
                                            <div class="mt-1">
                                                <form method="POST" action="{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}" data-undo-replacement-form="1">
                                                    @csrf
                                                    <button type="button" onclick="openUndoReplacementModal(this.closest('form').action)" class="w-full bg-white hover:bg-purple-100 text-purple-800 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-purple-200">
                                                        Deshacer reemplazo
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-1.5 flex items-center justify-center">
                                            <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-slate-600 font-black border border-slate-200 shadow-sm text-[12px]">
                                                {{ strtoupper(substr($staff->name, 0, 1) . substr($staff->last_name_paternal, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col justify-center min-w-0">
                                            <div class="h-10 flex flex-col justify-center min-w-0">
                                                <div class="text-xs font-black text-slate-900 leading-tight truncate">
                                                    {{ $staff->name }}
                                                </div>
                                                <div class="text-xs font-black text-slate-900 leading-tight truncate">
                                                    {{ $staff->last_name_paternal }}
                                                </div>
                                            </div>
                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest truncate">
                                                {{ $staff->is_shift_leader ? 'Jefe de Guardia' : 'Bombero' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-1.5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estado</label>
                                        <div class="grid grid-cols-2 gap-1">
                                            <button type="button" data-user-id="{{ $staff->id }}" data-status="constituye" onclick="setGuardiaStatus('{{ $staff->id }}', 'constituye')" class="w-full px-1 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1 {{ $status === 'constituye' ? 'bg-emerald-500 text-white border-emerald-600 shadow-sm' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                <span class="w-2 h-2 rounded-full {{ $status === 'constituye' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                                Constituye
                                            </button>
                                            <button type="button" data-user-id="{{ $staff->id }}" data-status="permiso" onclick="setGuardiaStatus('{{ $staff->id }}', 'permiso')" class="w-full px-1 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1 {{ $status === 'permiso' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                                                <span class="w-2 h-2 rounded-full {{ $status === 'permiso' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                                Permiso
                                            </button>
                                            <button type="button" data-user-id="{{ $staff->id }}" data-status="ausente" onclick="setGuardiaStatus('{{ $staff->id }}', 'ausente')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 {{ $status === 'ausente' ? 'bg-slate-700 text-white border-slate-800 shadow-sm' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200' }}">
                                                <span class="w-2.5 h-2.5 rounded-full {{ $status === 'ausente' ? 'bg-white' : 'bg-slate-600' }}"></span>
                                                Ausente
                                            </button>
                                            <button type="button" data-user-id="{{ $staff->id }}" data-status="licencia" onclick="setGuardiaStatus('{{ $staff->id }}', 'licencia')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 {{ $status === 'licencia' ? 'bg-blue-600 text-white border-blue-700 shadow-sm' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' }}">
                                                <span class="w-2.5 h-2.5 rounded-full {{ $status === 'licencia' ? 'bg-white' : 'bg-blue-600' }}"></span>
                                                Licencia
                                            </button>
                                            <button type="button" data-user-id="{{ $staff->id }}" data-status="falta" onclick="setGuardiaStatus('{{ $staff->id }}', 'falta')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 col-span-2 {{ $status === 'falta' ? 'bg-red-600 text-white border-red-700 shadow-sm' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' }}">
                                                <span class="w-2.5 h-2.5 rounded-full {{ $status === 'falta' ? 'bg-white' : 'bg-red-600' }}"></span>
                                                Falta
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        data-open-replacement="1"
                                        data-original-firefighter-id="{{ $staff->id }}"
                                        data-original-user-name="{{ $staff->name }} {{ $staff->last_name_paternal }}"
                                        class="mt-1.5 w-full bg-purple-600 hover:bg-purple-700 text-white font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg flex items-center justify-center gap-2"
                                    >
                                        <i class="fas fa-user-plus"></i>
                                        Reemplazar
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                                Sin dotación asignada.
                            </div>
                        @endforelse
                    </div>

                    </form>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Próximos Cumpleaños</div>
                            <div class="text-xs font-black text-slate-400">{{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->translatedFormat('F'), 'UTF-8') }}</div>
                        </div>
                        <div class="p-5">
                            @if($birthdays->isEmpty())
                                <div class="text-sm text-slate-400">Sin cumpleaños.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($birthdays->take(5) as $user)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-black text-slate-800 truncate">{{ $user->name }} {{ $user->last_name_paternal }}</div>
                                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bombero</div>
                                            </div>
                                            <div class="text-sm font-black text-slate-600">
                                                {{ \Carbon\Carbon::parse($user->birthdate)->format('d') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Bitácora de Novedades</div>
                            <button onclick="openNoveltyModal()" class="text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest">Registrar</button>
                        </div>
                        <div class="p-5">
                            @php
                                $guardiaNoveltiesList = $guardiaNovelties ?? $novelties;
                            @endphp
                            @if($guardiaNoveltiesList->isEmpty())
                                <div class="text-sm text-slate-400">Sin novedades recientes.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($guardiaNoveltiesList as $novelty)
                                        <div class="border-l-2 border-slate-200 pl-4">
                                            <div class="text-sm font-black text-slate-800">{{ $novelty->title }}</div>
                                            <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
                                                {{ $novelty->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($guardiaNovelties)
                                    <div class="mt-4">
                                        {{ $guardiaNovelties->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Academias Nocturnas</div>
                            <button onclick="openAcademyModal()" class="text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest">Registrar</button>
                        </div>
                        <div class="p-5">
                            @if(!isset($academies) || $academies->isEmpty())
                                <div class="text-sm text-slate-400">Sin academias registradas.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($academies->take(5) as $academy)
                                        <div class="border-l-2 border-slate-200 pl-4">
                                            <div class="text-sm font-black text-slate-800">{{ $academy->title }}</div>
                                            <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $academy->description }}</div>
                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
                                                {{ ($academy->created_at ?? $academy->date)?->diffForHumans() }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Camas</div>
                            <a href="{{ route('camas') }}" class="text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest">Ver</a>
                        </div>
                        <div class="p-5">
                            <div class="text-4xl font-black text-slate-900">{{ $availableBeds }}<span class="text-lg text-slate-400 font-black">/{{ $totalBeds }}</span></div>
                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Disponibles</div>
                        </div>
                    </div>
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
                    <i class="fas fa-calendar-week text-2xl"></i>
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

    @if(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'capitania'], true))
        <div class="mb-10">
            <a href="{{ route('admin.system.index') }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-xs transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-600 text-white">
                    <i class="fas fa-shield-halved"></i>
                </span>
                <span>Administración del Sistema</span>
            </a>
        </div>
    @endif

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
            <div class="p-4">
                @if($birthdays->isEmpty())
                    <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-calendar-xmark text-4xl mb-3 opacity-50"></i>
                        <p>No hay cumpleaños registrados para este mes.</p>
                    </div>
                @else
                    <ul class="space-y-3 max-h-[320px] overflow-auto pr-1">
                        @foreach($birthdays->take(5) as $user)
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
                <button type="button" onclick="openNoveltyModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800">Registrar</button>
            </div>
            <div class="p-4">
                @if($novelties->isEmpty())
                     <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-clipboard-list text-4xl mb-3 opacity-50"></i>
                        <p>No se han registrado novedades recientes.</p>
                    </div>
                @else
                    <div class="relative border-l-2 border-slate-200 ml-3 space-y-6 max-h-[320px] overflow-auto pr-1">
                        @foreach($novelties->take(5) as $novelty)
                            <div class="ml-6 relative">
                                <span class="absolute -left-[31px] top-0 flex items-center justify-center w-4 h-4 bg-white rounded-full ring-4 ring-white">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                </span>
                                <h3 class="text-sm font-bold text-slate-800">{{ $novelty->title }}</h3>
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $novelty->description }}</p>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-slate-400 font-medium">
                                        <i class="fas fa-clock mr-1"></i> {{ $novelty->created_at->diffForHumans() }}
                                    </span>
                                    <span class="text-xs text-slate-600 font-bold truncate">{{ $novelty->user?->name ?? 'Sistema' }}</span>
                                </div>
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

    <div id="academyModal" class="fixed inset-0 bg-slate-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-chalkboard-user text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800">Registrar Academia</h3>
                <p class="text-slate-500 text-sm mt-1">Academia nocturna</p>
            </div>

            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="Academia">

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">A cargo</label>
                    <select name="user_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" required>
                        <option value="" disabled selected>Seleccionar</option>
                        @foreach(($academyLeaders ?? collect()) as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->name }} {{ $leader->last_name_paternal }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Día y hora</label>
                    <input type="datetime-local" name="date" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" placeholder="Ej: RCP, Uso de ERA, etc" required>
                </div>

                <div class="mb-6">
                    <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px]" placeholder="Detalle de la academia..." required>{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeAcademyModal()" class="w-1/2 px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors uppercase text-sm">
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

        window.openAcademyModal = function() {
            const modal = document.getElementById('academyModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeAcademyModal = function() {
            const modal = document.getElementById('academyModal');
            if (modal) modal.classList.add('hidden');
        }

        window.openReplacementModal = function(originalUserId, originalUserName) {
            const modal = document.getElementById('replacementModal');
            const guardiaIdInput = document.getElementById('modal_guardia_id');
            const originalIdInput = document.getElementById('modal_original_firefighter_id');
            const nameEl = document.getElementById('modal_original_user_name');
            const displayInput = document.querySelector('#replacementModal input[list="modal_volunteers_list"]');
            const replacementIdInput = document.getElementById('modal_replacement_firefighter_id');

            if (!modal || !guardiaIdInput || !originalIdInput || !nameEl || !replacementIdInput) {
                console.error('Modal de reemplazo no encontrado o incompleto', {
                    hasModal: !!modal,
                    hasGuardiaIdInput: !!guardiaIdInput,
                    hasOriginalIdInput: !!originalIdInput,
                    hasNameEl: !!nameEl,
                    hasReplacementIdInput: !!replacementIdInput,
                });
                return;
            }

            guardiaIdInput.value = {!! json_encode($myGuardia?->id) !!};
            originalIdInput.value = originalUserId;
            nameEl.textContent = originalUserName;

            if (displayInput) displayInput.value = '';
            replacementIdInput.value = '';

            const content = modal.firstElementChild;
            modal.classList.remove('hidden');

            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            });
        }

        window.closeReplacementModal = function() {
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

        window.updateModalReplacementUserId = function(input) {
            const list = document.getElementById('modal_volunteers_list');
            const hiddenInput = document.getElementById('modal_replacement_firefighter_id');
            if (!list || !hiddenInput) return;

            const options = list.options;
            hiddenInput.value = '';

            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
                    hiddenInput.value = options[i].getAttribute('data-value') || '';
                    break;
                }
            }
        }

        document.addEventListener('click', function(event) {
            const btn = event.target.closest('[data-open-replacement="1"]');
            if (!btn) return;

            const originalUserId = btn.getAttribute('data-original-firefighter-id');
            const originalUserName = btn.getAttribute('data-original-user-name') || '';
            window.openReplacementModal(originalUserId, originalUserName);
        });
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                window.closeNoveltyModal();
                window.closeReplacementModal();
                window.closeAcademyModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Si hay errores de validación, reabrir el modal automáticamente
            @if($errors->has('title') || $errors->has('type') || $errors->has('description'))
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

        window.setGuardiaStatus = function(userId, status) {
            if (status === 'falta') {
                if (!confirm('¿Cambiar estado a FALTA?')) {
                    return;
                }
            }

            const input = document.getElementById('attendance-status-' + userId);
            if (!input) {
                console.error('Input de estado no encontrado', { userId });
                return;
            }

            input.value = status;
            updateGuardiaCardUI(userId, status);
        }

        function updateGuardiaCardUI(userId, status) {
            const dot = document.getElementById('card-dot-' + userId);
            if (dot) {
                dot.classList.remove('bg-emerald-400','bg-purple-400','bg-amber-400','bg-slate-500','bg-blue-400','bg-red-400');
                if (status === 'constituye') dot.classList.add('bg-emerald-400');
                else if (status === 'reemplazo') dot.classList.add('bg-purple-400');
                else if (status === 'permiso') dot.classList.add('bg-amber-400');
                else if (status === 'ausente') dot.classList.add('bg-slate-500');
                else if (status === 'licencia') dot.classList.add('bg-blue-400');
                else if (status === 'falta') dot.classList.add('bg-red-400');
                else dot.classList.add('bg-slate-500');
            }

            const header = document.getElementById('card-header-' + userId);
            if (header) {
                header.classList.remove('bg-emerald-700','bg-purple-700','bg-amber-700','bg-slate-800','bg-blue-700','bg-red-700','bg-slate-900');
                if (status === 'constituye') header.classList.add('bg-emerald-700');
                else if (status === 'reemplazo') header.classList.add('bg-purple-700');
                else if (status === 'permiso') header.classList.add('bg-amber-700');
                else if (status === 'ausente') header.classList.add('bg-slate-800');
                else if (status === 'licencia') header.classList.add('bg-blue-700');
                else if (status === 'falta') header.classList.add('bg-red-700');
                else header.classList.add('bg-slate-900');
            }

            document.querySelectorAll('button[data-user-id="' + userId + '"][data-status]').forEach(btn => {
                const s = btn.getAttribute('data-status');
                btn.classList.remove(
                    'bg-emerald-500','text-white','border-emerald-600','shadow-sm',
                    'bg-amber-500','border-amber-600',
                    'bg-slate-700','border-slate-800',
                    'bg-blue-600','border-blue-700',
                    'bg-red-600','border-red-700'
                );
                btn.classList.add('shadow-none');

                if (s === status) {
                    if (s === 'constituye') btn.classList.add('bg-emerald-500','text-white','border-emerald-600','shadow-sm');
                    if (s === 'permiso') btn.classList.add('bg-amber-500','text-white','border-amber-600','shadow-sm');
                    if (s === 'ausente') btn.classList.add('bg-slate-700','text-white','border-slate-800','shadow-sm');
                    if (s === 'licencia') btn.classList.add('bg-blue-600','text-white','border-blue-700','shadow-sm');
                    if (s === 'falta') btn.classList.add('bg-red-600','text-white','border-red-700','shadow-sm');
                } else {
                    btn.classList.remove('text-white');
                }
            });
        }

        window.openUndoReplacementModal = function(actionUrl) {
            const modal = document.getElementById('undoReplacementModal');
            const form = document.getElementById('undoReplacementModalForm');
            if (!modal || !form) return;
            form.action = actionUrl;
            modal.classList.remove('hidden');
        }

        window.closeUndoReplacementModal = function() {
            const modal = document.getElementById('undoReplacementModal');
            if (!modal) return;
            modal.classList.add('hidden');
        }
    </script>

    <div id="undoReplacementModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 border border-slate-200 overflow-hidden">
            <div class="p-4">
                <div class="text-sm font-black text-slate-900 uppercase tracking-widest">Confirmar acción</div>
                <div class="mt-2 text-sm text-slate-600">¿Deshacer este reemplazo?</div>
            </div>
            <div class="p-4 pt-0 flex gap-2">
                <button type="button" onclick="closeUndoReplacementModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-black uppercase tracking-widest text-[10px] py-2 rounded-xl border border-slate-200">
                    Cancelar
                </button>
                <form id="undoReplacementModalForm" method="POST" class="w-1/2">
                    @csrf
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black uppercase tracking-widest text-[10px] py-2 rounded-xl border border-purple-700">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(isset($myGuardia) && $myGuardia)
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
                        <p id="modal_original_user_name" class="text-sm font-bold text-slate-700"></p>
                    </div>
                </div>

                <form action="{{ route('admin.guardias.replacement') }}" method="POST">
                    @csrf
                    <input type="hidden" name="guardia_id" id="modal_guardia_id" value="{{ $myGuardia?->id }}">
                    <input type="hidden" name="original_firefighter_id" id="modal_original_firefighter_id">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Voluntario Reemplazante</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input list="modal_volunteers_list" name="replacement_firefighter_id_display"
                                    class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-9 py-2.5 bg-slate-50"
                                    placeholder="Buscar voluntario..." required
                                    oninput="updateModalReplacementUserId(this)">
                                <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_firefighter_id" required>
                            </div>
                            <datalist id="modal_volunteers_list">
                                @foreach($replacementCandidates as $cand)
                                    <option data-value="{{ $cand->id }}" value="{{ trim($cand->last_name_paternal . ' ' . ($cand->last_name_maternal ?? '') . ', ' . $cand->name . ($cand->rut ? ' - ' . $cand->rut : '')) }}"></option>
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
    @endif
@endsection
