@extends('layouts.app')

@section('content')
    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        @php
            $attendanceEnableTime = \App\Models\SystemSetting::getValue('attendance_enable_time', '21:00');
            $attendanceDisableTime = \App\Models\SystemSetting::getValue('attendance_disable_time', '10:00');

            $attendanceEnabled = (function () use ($attendanceEnableTime, $attendanceDisableTime) {
                $now = now();
                [$enableH, $enableM] = array_map('intval', explode(':', (string) $attendanceEnableTime));
                [$disableH, $disableM] = array_map('intval', explode(':', (string) $attendanceDisableTime));

                $enableAt = $now->copy()->setTime($enableH, $enableM, 0);
                $disableAt = $now->copy()->setTime($disableH, $disableM, 0);

                // Ventana habilitada que puede cruzar medianoche: [enableAt, disableAt)
                // Si enableAt < disableAt => habilitado entre enableAt y disableAt (mismo día)
                // Si enableAt > disableAt => habilitado desde enableAt hasta disableAt del día siguiente
                if ($enableAt->lessThan($disableAt)) {
                    return $now->greaterThanOrEqualTo($enableAt) && $now->lessThan($disableAt);
                }

                return $now->greaterThanOrEqualTo($enableAt) || $now->lessThan($disableAt);
            })();
            // Filtrar personal activo (excluyendo los que están siendo reemplazados)
            $activeStaff = $myStaff->filter(function ($u) use ($replacementByOriginal) {
                return !($replacementByOriginal && $replacementByOriginal->has($u->id));
            });

            $outOfServiceStaff = $activeStaff->filter(function ($u) {
                return (bool) ($u->fuera_de_servicio ?? false);
            });

            // Excluir fuera de servicio de la operación diaria
            $activeStaff = $activeStaff->reject(function ($u) {
                return (bool) ($u->fuera_de_servicio ?? false);
            });

            $activeStaff = $activeStaff
                ->sortBy(function ($u) use ($replacementByReplacement) {
                    $isReplacement = (bool) ($replacementByReplacement && $replacementByReplacement->has($u->id));
                    $isRefuerzo = (bool) ($u->es_refuerzo ?? false);
                    $apellido = (string) ($u->apellido_paterno ?? '');
                    $nombres = (string) ($u->nombres ?? '');
                    return sprintf('%d-%s-%s', ($isReplacement || $isRefuerzo) ? 1 : 0, $apellido, $nombres);
                })
                ->values();

            $onDutyStaff = $activeStaff->filter(function ($u) {
                return in_array($u->estado_asistencia, ['constituye', 'reemplazo'], true);
            });

            $offDutyStaff = $myStaff->reject(function ($u) use ($replacementByOriginal) {
                $isReplaced = (bool) ($replacementByOriginal && $replacementByOriginal->has($u->id));
                return !$isReplaced && in_array($u->estado_asistencia, ['constituye', 'reemplazo'], true);
            });

            $statusCounts = [
                'constituye' => $activeStaff->where('estado_asistencia', 'constituye')->count(),
                'reemplazo' => $activeStaff->where('estado_asistencia', 'reemplazo')->count(),
                'permiso' => $activeStaff->where('estado_asistencia', 'permiso')->count(),
                'ausente' => $activeStaff->where('estado_asistencia', 'ausente')->count(),
                'licencia' => $activeStaff->where('estado_asistencia', 'licencia')->count(),
                'falta' => $activeStaff->where('estado_asistencia', 'falta')->count(),
            ];
        @endphp
        <!-- VISTA ESPECÍFICA PARA CUENTA DE GUARDIA (FULLSCREEN TARJETAS) -->
        <div id="guardia-dashboard-root" class="w-full min-h-screen px-4 md:px-6 lg:px-8 py-4 pt-[env(safe-area-inset-top)] bg-slate-900 text-slate-100">
            <div class="sticky top-0 z-40 flex flex-col md:flex-row md:items-center md:justify-between mb-5 gap-4 border-b border-slate-800 pb-4 bg-slate-900">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="bg-red-700 p-2 rounded-lg text-white shadow-lg border border-red-600 shrink-0">
                        <i class="fas fa-gauge-high text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Panel de Control</div>
                        <div class="mt-0.5 flex items-center gap-3 min-w-0">
                            <div class="text-2xl md:text-3xl font-black tracking-tight text-slate-100 uppercase truncate">{{ $myGuardia->name }}</div>
                            @if(isset($isMyGuardiaOnDuty) && $isMyGuardiaOnDuty)
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-green-200 bg-green-50 text-green-700 shrink-0">EN TURNO</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-100 text-slate-700 shrink-0">FUERA DE TURNO</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-xs font-medium text-slate-500">{{ $activeStaff->count() }} bomberos | {{ $onDutyStaff->count() }} presentes</div>
                    </div>
                </div>

                <div id="attendance-stale-banner" class="hidden w-full max-w-3xl mx-auto mb-4 px-4 py-3 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0 border border-amber-200">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-black uppercase tracking-widest">Asistencia desactualizada</div>
                            <div class="text-sm font-bold leading-tight">Se detectaron cambios después de guardar la asistencia. Debes presionar <span class="font-black">Guardar Asistencia</span> nuevamente para confirmar.</div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:flex-1 flex items-center justify-start md:justify-center">
                    <div class="flex items-center gap-2 sm:gap-3 w-full md:w-auto overflow-x-auto md:overflow-visible -mx-1 px-1">
                        <form method="POST" action="{{ route('admin.guardias.replacements.cleanup', $myGuardia->id) }}" class="hidden md:block" onsubmit="return confirm('¿Cerrar todos los reemplazos activos de la guardia?');">
                            @csrf
                            <button type="submit" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center" title="Limpiar Reemplazos">
                                <i class="fas fa-rotate-left text-[14px] text-purple-300"></i>
                            </button>
                        </form>
                        <button type="button" onclick="toggleFullscreen()" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center" title="Pantalla completa">
                            <i class="fas fa-expand text-[14px] text-slate-200"></i>
                        </button>
                        <a href="{{ route('guardia.aseo') }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center" title="Asignación de Aseo">
                            <i class="fas fa-broom text-[14px] text-red-300"></i>
                        </a>
                        <a href="{{ route('admin.emergencies.index') }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center" title="Emergencias">
                            <i class="fas fa-truck-medical text-[14px] text-amber-300"></i>
                        </a>
                        <button type="button" onclick="openRefuerzoModal()" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center" title="Refuerzo">
                            <i class="fas fa-user-plus text-[14px] text-sky-300"></i>
                        </button>
                        <button form="guardia-attendance-form" type="submit" @if(!$attendanceEnabled) disabled @endif class="w-9 h-9 sm:w-10 sm:h-10 {{ $attendanceEnabled ? 'bg-slate-800 hover:bg-slate-700 text-slate-100 border-slate-700 shadow-sm' : 'bg-slate-200 text-slate-500 border-slate-300 shadow-sm cursor-not-allowed' }} rounded-xl transition-all border flex items-center justify-center" title="Guardar Asistencia">
                            <i class="fas fa-floppy-disk text-[14px] {{ $attendanceEnabled ? 'text-emerald-300' : '' }}"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
                    @if(isset($hasAttendanceSavedToday) && $hasAttendanceSavedToday)
                        <span id="attendance-saved-badge" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 shrink-0">GUARDIA CONSTITUIDA</span>
                    @else
                        <span id="attendance-saved-badge" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-700 shrink-0">SIN REGISTRAR ASISTENCIA</span>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="h-9 sm:h-10 px-3 bg-slate-800 hover:bg-slate-700 text-slate-100 rounded-xl border border-slate-700 shadow-sm flex items-center justify-center gap-2" title="Cerrar sesión">
                            <i class="fas fa-right-from-bracket text-[14px] text-rose-300"></i>
                            <span class="hidden sm:inline text-[10px] font-black uppercase tracking-widest">Salir</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-4">
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 md:p-5 shadow-sm">
                    <form id="guardia-attendance-form" method="POST" action="{{ route('admin.guardias.bulk_update', $myGuardia->id) }}">
                        @csrf

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                            @forelse($activeStaff as $staff)
                                @php
                                    $status = $staff->estado_asistencia;
                                    $repAsReplacement = isset($replacementByReplacement) ? $replacementByReplacement->get($staff->id) : null;
                                    $lockAttendanceStatus = (bool) ($repAsReplacement || $staff->es_refuerzo);
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
                                        'constituye' => 'bg-emerald-950/40',
                                        'reemplazo' => 'bg-purple-950/40',
                                        'permiso' => 'bg-amber-950/35',
                                        'ausente' => 'bg-slate-950',
                                        'licencia' => 'bg-blue-950/40',
                                        'falta' => 'bg-rose-950/40',
                                        default => 'bg-slate-950',
                                    };
                                @endphp
                                <input type="hidden" name="users[{{ $staff->id }}][estado_asistencia]" id="attendance-status-{{ $staff->id }}" value="{{ $status }}">

                                <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col h-[420px]" data-card-user="{{ $staff->id }}">
                                    <div id="card-header-{{ $staff->id }}" class="{{ $statusHeaderClass }} text-white px-2 py-1.5 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <div class="text-[12px] font-black truncate" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                                {{ strtoupper($staff->apellido_paterno ?: $staff->nombres) }}
                                            </div>
                                            @if($staff->es_jefe_guardia)
                                                <div class="flex items-center gap-2 text-xs text-slate-300">
                                                    <i class="fas fa-user-group opacity-70"></i>
                                                    <span class="font-black">Jefe</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if(in_array(Auth::user()->role, ['super_admin','capitania','guardia'], true))
                                                <button type="button" onclick="toggleInhabilitado('{{ $staff->id }}')" class="h-6 px-2 rounded-md border border-slate-700 bg-slate-900/60 hover:bg-slate-900 text-[9px] font-black uppercase tracking-widest text-slate-200">
                                                    Inhabilitar
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                <div class="p-1.5 flex-1 flex flex-col">

                                    <div class="grid grid-cols-2 gap-1.5 flex-1">
                                        <div class="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden flex items-stretch justify-stretch h-[120px]">
                                            @if($staff->photo_path)
                                                <img src="{{ url('media/' . ltrim($staff->photo_path, '/')) }}" class="w-full h-full object-cover" alt="Foto">
                                            @else
                                                <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-200 font-black text-[12px]">
                                                    {{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col justify-start min-w-0">
                                            <div class="min-h-[40px] flex flex-col justify-start min-w-0">
                                                <div class="text-xs font-black text-slate-100 leading-tight truncate">
                                                    {{ $staff->nombres }}
                                                </div>
                                                <div class="text-xs font-black text-slate-100 leading-tight truncate">
                                                    {{ $staff->apellido_paterno }}
                                                </div>
                                            </div>
                                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest truncate">
                                                {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
                                            </div>

                                            @php
                                                $ingreso = $staff->fecha_ingreso ? \Carbon\Carbon::parse($staff->fecha_ingreso) : null;
                                                $diff = $ingreso ? $ingreso->diff(now()) : null;
                                                $serviceYears = $diff ? (int) $diff->y : 0;
                                                $serviceMonths = $diff ? (int) $diff->m : 0;
                                                $yearsLabel = $serviceYears . ' ' . ($serviceYears === 1 ? 'año' : 'años');
                                                $monthsLabel = $serviceMonths . ' ' . ($serviceMonths === 1 ? 'm' : 'm');
                                                $serviceLabel = $diff ? trim($yearsLabel . ' ' . $monthsLabel) : '—';
                                            @endphp
                                            <div class="mt-1 text-[10px] font-black text-slate-400 uppercase tracking-widest truncate" title="Antigüedad">
                                                {{ $serviceLabel }}
                                            </div>
                                            <div class="mt-0.5 text-[10px] font-black text-slate-400 uppercase tracking-widest truncate" title="Móvil">
                                                Móvil: {{ $staff->numero_portatil ?: '—' }}
                                            </div>
                                            @if($staff->es_refuerzo)
                                                <div class="mt-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-200 bg-emerald-500/15 border border-emerald-500/25 rounded-md px-2 py-1 w-fit">
                                                    REFUERZO
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($repAsReplacement)
                                        <div class="mt-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-2 text-purple-800">
                                            <div class="text-[11px] font-black uppercase tracking-widest">Reemplaza a</div>
                                            <div class="text-sm font-black leading-tight truncate">
                                                {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="w-full bg-white hover:bg-purple-100 text-purple-800 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-purple-200">
                                                    Deshacer reemplazo
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-1.5 min-h-[22px] flex items-center justify-center gap-1.5">
                                        @if($staff->es_conductor)
                                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[9px] font-bold border border-blue-200" title="Conductor">
                                                <i class="fas fa-car text-[9px]"></i>
                                            </span>
                                        @endif
                                        @if($staff->es_operador_rescate)
                                            <span class="w-5 h-5 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[9px] font-bold border border-orange-200" title="Operador de Rescate">R</span>
                                        @endif
                                        @if($staff->es_asistente_trauma)
                                            <span class="w-7 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[9px] font-bold border border-red-200" title="Asistente de Trauma">A.T</span>
                                        @endif
                                    </div>

                                    <div class="mt-1.5">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estado</label>
                                        @if($lockAttendanceStatus)
                                            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/15 text-emerald-200 px-3 py-2 text-center">
                                                <div class="text-sm font-black">CONSTITUYE</div>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-2 gap-1">
                                                <button type="button" data-user-id="{{ $staff->id }}" data-status="constituye" onclick="setGuardiaStatus('{{ $staff->id }}', 'constituye')" class="w-full px-1 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1 {{ $status === 'constituye' ? 'bg-emerald-500/80 text-white border-emerald-400/50 shadow-sm' : 'bg-emerald-500/15 text-emerald-200 border-emerald-500/25 hover:bg-emerald-500/20' }}">
                                                    <span class="w-2 h-2 rounded-full {{ $status === 'constituye' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                                    Constituye
                                                </button>
                                                <button type="button" data-user-id="{{ $staff->id }}" data-status="permiso" onclick="setGuardiaStatus('{{ $staff->id }}', 'permiso')" class="w-full px-1 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1 {{ $status === 'permiso' ? 'bg-amber-500/80 text-white border-amber-400/50 shadow-sm' : 'bg-amber-500/15 text-amber-200 border-amber-500/25 hover:bg-amber-500/20' }}">
                                                    <span class="w-2 h-2 rounded-full {{ $status === 'permiso' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                                    Permiso
                                                </button>
                                                <button type="button" data-user-id="{{ $staff->id }}" data-status="ausente" onclick="setGuardiaStatus('{{ $staff->id }}', 'ausente')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 {{ $status === 'ausente' ? 'bg-slate-400/30 text-white border-slate-500/30 shadow-sm' : 'bg-slate-500/10 text-slate-200 border-slate-500/20 hover:bg-slate-500/15' }}">
                                                    <span class="w-2.5 h-2.5 rounded-full {{ $status === 'ausente' ? 'bg-white' : 'bg-slate-600' }}"></span>
                                                    Ausente
                                                </button>
                                                <button type="button" data-user-id="{{ $staff->id }}" data-status="licencia" onclick="setGuardiaStatus('{{ $staff->id }}', 'licencia')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 {{ $status === 'licencia' ? 'bg-blue-600/80 text-white border-blue-400/50 shadow-sm' : 'bg-blue-600/15 text-blue-200 border-blue-500/25 hover:bg-blue-600/20' }}">
                                                    <span class="w-2.5 h-2.5 rounded-full {{ $status === 'licencia' ? 'bg-white' : 'bg-blue-600' }}"></span>
                                                    Licencia
                                                </button>
                                                <button type="button" data-user-id="{{ $staff->id }}" data-status="falta" onclick="setGuardiaStatus('{{ $staff->id }}', 'falta')" class="w-full px-2 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center gap-1.5 col-span-2 {{ $status === 'falta' ? 'bg-red-600/80 text-white border-red-400/50 shadow-sm' : 'bg-red-600/15 text-red-200 border-red-500/25 hover:bg-red-600/20' }}">
                                                    <span class="w-2.5 h-2.5 rounded-full {{ $status === 'falta' ? 'bg-white' : 'bg-red-600' }}"></span>
                                                    Falta
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-1.5 {{ ($repAsReplacement || $staff->es_refuerzo) ? 'min-h-0' : 'min-h-[72px]' }}">
                                        @if(!($repAsReplacement || $staff->es_refuerzo))
                                            <button
                                                type="button"
                                                data-open-replacement="1"
                                                data-original-firefighter-id="{{ $staff->id }}"
                                                data-original-user-name="{{ $staff->nombres }} {{ $staff->apellido_paterno }}"
                                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg flex items-center justify-center gap-2"
                                            >
                                                <i class="fas fa-user-plus"></i>
                                                Reemplazar
                                            </button>
                                        @endif

                                        @if($staff->es_refuerzo)
                                            <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="mt-1.5 w-full bg-slate-950 hover:bg-slate-900 text-slate-100 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-slate-800">
                                                Quitar refuerzo
                                            </button>
                                        @else
                                            <div class="mt-1.5 opacity-0 select-none">
                                                <button type="button" class="w-full bg-slate-950 text-slate-100 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-slate-800">
                                                    Quitar refuerzo
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full bg-slate-900 rounded-2xl border border-slate-800 p-10 text-center text-slate-300">
                                Sin dotación asignada.
                            </div>
                        @endforelse
                        </div>

                        @if(isset($outOfServiceStaff) && $outOfServiceStaff->isNotEmpty())
                            <div class="mt-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Inhabilitados</div>
                                    <div class="text-[11px] font-bold text-slate-400">{{ $outOfServiceStaff->count() }}</div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                                    @foreach($outOfServiceStaff as $staff)
                                        <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col p-3">
                                            <div class="flex items-center justify-between">
                                                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest truncate">{{ $staff->cargo_texto ?? 'Bombero' }}</div>
                                                <div class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-100">INHABILITADO</div>
                                            </div>
                                            <div class="mt-2 text-sm font-black text-slate-100 leading-tight truncate" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                                {{ $staff->apellido_paterno }}{{ $staff->apellido_materno ? ' ' . $staff->apellido_materno : '' }}, {{ $staff->nombres }}
                                            </div>
                                            @if(in_array(Auth::user()->role, ['super_admin','capitania','guardia'], true))
                                                <button type="button" onclick="toggleHabilitar('{{ $staff->id }}')" class="mt-3 w-full bg-slate-950 hover:bg-slate-900 text-green-300 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-green-900">
                                                    Habilitar
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-800 rounded-2xl shadow-sm border border-slate-700 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
                            <div class="text-sm font-black text-slate-200 uppercase tracking-widest">Hora Local</div>
                            <div class="text-xs font-black text-red-600 uppercase tracking-widest">EN LÍNEA</div>
                        </div>
                        <div class="p-5">
                            @if(!$attendanceEnabled)
                                <div class="flex justify-end mb-3">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-amber-300/30 bg-amber-500/10 text-amber-200">HABILITADO DESDE LAS {{ $attendanceEnableTime }} HASTA LAS {{ $attendanceDisableTime }}</span>
                                </div>
                            @endif
                            <div class="bg-slate-900 text-white px-5 py-3 rounded-lg shadow-lg border-2 border-slate-700 flex items-center justify-center">
                                <span id="digital-clock" class="text-2xl md:text-3xl font-mono font-bold tracking-widest text-white drop-shadow-md">--:--:--</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
                            <div class="text-sm font-black text-slate-200 uppercase tracking-widest">Próximos Cumpleaños</div>
                            <div class="text-xs font-black text-slate-400">{{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->translatedFormat('F'), 'UTF-8') }}</div>
                        </div>
                        <div class="p-5">
                            @php
                                $birthdaysList = $birthdaysThisMonth ?? $birthdays;
                            @endphp
                            @if($birthdaysList->isEmpty())
                                <div class="text-sm text-slate-400">Sin cumpleaños este mes.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($birthdaysList->take(5) as $user)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-black text-slate-100 truncate">{{ $user->nombres }} {{ $user->apellido_paterno }}</div>
                                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bombero</div>
                                            </div>
                                            <div class="text-sm font-black text-slate-600">
                                                {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
                            <div class="text-sm font-black text-slate-200 uppercase tracking-widest">Bitácora de Novedades</div>
                            <button onclick="openNoveltyModal()" class="text-xs font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest">Registrar</button>
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
                                        <div class="border-l-2 border-slate-800 pl-4">
                                            <div class="text-sm font-black text-slate-100">{{ $novelty->title }}</div>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
                                                {{ $novelty->created_at->diffForHumans() }}
                                                @if($novelty->user)
                                                    <span class="text-slate-300">|</span>
                                                    {{ $novelty->user->name ?? '-' }}
                                                @endif
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

                    <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
                            <div class="text-sm font-black text-slate-200 uppercase tracking-widest">Academias Nocturnas</div>
                            <button onclick="openAcademyModal()" class="text-xs font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest">Registrar</button>
                        </div>
                        <div class="p-5">
                            @if(!isset($academies) || $academies->isEmpty())
                                <div class="text-sm text-slate-400">Sin academias registradas.</div>
                            @else
                                <div class="space-y-4">
                                    @foreach($academies->take(5) as $academy)
                                        <div class="border-l-2 border-slate-800 pl-4">
                                            <div class="text-sm font-black text-slate-100">{{ $academy->title }}</div>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $academy->description }}</div>
                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
                                                {{ ($academy->created_at ?? $academy->date)?->diffForHumans() }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-950">
                            <div class="text-sm font-black text-slate-200 uppercase tracking-widest">Camas</div>
                            <a href="{{ route('camas') }}" class="text-xs font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest">Ver</a>
                        </div>
                        <div class="p-5">
                            <div class="text-4xl font-black text-slate-100">{{ $availableBeds }}<span class="text-lg text-slate-400 font-black">/{{ $totalBeds }}</span></div>
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
        <div class="flex items-center gap-3">
            

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
                                CONSTITUIDA
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
        <div class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }} rounded-xl shadow-sm border p-6 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-400' }} uppercase tracking-wider mb-1">Efemérides</p>
                    <h3 class="font-bold text-lg {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-200' : 'text-slate-700' }}">Cumpleaños Mes</h3>
                    <p class="text-3xl font-extrabold mt-2 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-800' }}">{{ $birthdaysMonthCount ?? $birthdays->count() }}</p>
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
        <div class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }} rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between items-center {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200' }}">
                <h2 class="font-bold flex items-center {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-200' : 'text-slate-700' }}">
                    <i class="fas fa-calendar-days text-amber-500 mr-2"></i> Próximos Cumpleaños
                </h2>
                <span class="text-xs font-bold px-2 py-1 rounded {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-800 text-slate-200' : 'bg-slate-200 text-slate-600' }}">{{ date('F') }}</span>
            </div>
            <div class="p-4">
                @php
                    $birthdaysList = $birthdaysThisMonth ?? $birthdays;
                @endphp
                @if($birthdaysList->isEmpty())
                    <div class="text-center py-8 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-400' }}">
                        <i class="fas fa-calendar-xmark text-4xl mb-3 opacity-50"></i>
                        <p>No hay cumpleaños registrados para este mes.</p>
                    </div>
                @else
                    <ul class="space-y-3 max-h-[320px] overflow-auto pr-1">
                        @foreach($birthdaysList->take(5) as $user)
                            <li class="flex items-center justify-between p-3 rounded-lg border border-transparent transition-all {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'hover:bg-slate-950 hover:border-slate-800' : 'hover:bg-slate-50 hover:border-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    @if($user->photo_path)
                                        <img src="{{ url('media/' . ltrim($user->photo_path, '/')) }}" class="w-10 h-10 rounded-full object-cover border border-amber-200 shadow-sm" alt="Foto">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                                            {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellido_paterno, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-900' }}">{{ $user->nombres }} {{ $user->apellido_paterno }}</div>
                                        <div class="text-xs {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-500' }}">{{ $user->fecha_nacimiento ? $user->fecha_nacimiento->format('d/m') : '' }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-200' : 'text-slate-700' }}">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}</span>
                                    <span class="text-xs uppercase {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-500' : 'text-slate-400' }}">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('M') }}</span>
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
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }}">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border border-slate-800' : 'bg-blue-100' }} mb-4">
                    <i class="fas fa-pen-to-square {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-blue-300' : 'text-blue-600' }} text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-800' }}">Registrar Novedad</h3>
                <p class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-500' }} text-sm mt-1">Bitácora de la Guardia</p>
            </div>
            
            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border {{ $errors->has('title') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100 placeholder:text-slate-500' : 'bg-white text-slate-900' }}" placeholder="Ej: Falla en carro B-3" required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Tipo</label>
                    <select name="type" class="w-full px-4 py-2.5 border {{ $errors->has('type') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100' : 'bg-white text-slate-900' }}">
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
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border {{ $errors->has('description') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px] {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100 placeholder:text-slate-500' : 'bg-white text-slate-900' }}" placeholder="Detalle de la novedad..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeNoveltyModal()" class="w-1/2 px-4 py-2.5 font-bold rounded-lg transition-colors uppercase text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border border-slate-800' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
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
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }}">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border border-slate-800' : 'bg-blue-100' }} mb-4">
                    <i class="fas fa-chalkboard-user {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-blue-300' : 'text-blue-600' }} text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-800' }}">Registrar Academia</h3>
                <p class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-500' }} text-sm mt-1">Academia nocturna</p>
            </div>

            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="Academia">

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">A cargo</label>
                    <select name="firefighter_id" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100' : 'bg-white border-slate-300 text-slate-900' }}" required>
                        <option value="" disabled selected>Seleccionar</option>
                        @foreach(($academyLeadersFirefighters ?? collect()) as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->apellido_paterno }} {{ $leader->apellido_materno ? $leader->apellido_materno . ',' : ',' }} {{ $leader->nombres }}</option>
                        @endforeach
                    </select>
                    @if(!isset($academyLeadersFirefighters) || ($academyLeadersFirefighters ?? collect())->isEmpty())
                        <div class="text-[11px] text-slate-500 mt-1">No se detectó personal en turno para esta guardia.</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Día y hora</label>
                    <input type="datetime-local" name="date" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100' : 'bg-white border-slate-300 text-slate-900' }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100 placeholder:text-slate-500' : 'bg-white border-slate-300 text-slate-900' }}" placeholder="Ej: RCP, Uso de ERA, etc" required>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px] {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100 placeholder:text-slate-500' : 'bg-white border-slate-300 text-slate-900' }}" placeholder="Detalle de la academia..." required>{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeAcademyModal()" class="w-1/2 px-4 py-2.5 font-bold rounded-lg transition-colors uppercase text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border border-slate-800' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Cancelar
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2.5 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition-colors shadow-md uppercase text-sm">
                        Registrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        <div id="refuerzoModal" class="fixed inset-0 bg-slate-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-slate-900 border-slate-800">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-slate-950 border border-slate-800 mb-4">
                        <i class="fas fa-user-plus text-slate-100 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-100">Agregar Refuerzo</h3>
                    <p class="text-slate-400 text-sm mt-1">El refuerzo se libera automáticamente a las 10:00 AM del día siguiente.</p>
                </div>

                <form method="POST" action="{{ route('admin.guardias.refuerzo') }}">
                    @csrf
                    <input type="hidden" name="guardia_id" value="{{ $myGuardia?->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Voluntario</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input list="refuerzo_volunteers_list" name="firefighter_id_display" required autocomplete="off"
                                    class="w-full pl-9 pr-3 py-3 border border-slate-800 bg-slate-950 text-slate-100 rounded-lg focus:ring-2 focus:ring-slate-700 focus:border-slate-500"
                                    placeholder="Buscar por nombre o RUT..." oninput="updateRefuerzoUserId(this)">
                                <input type="hidden" name="firefighter_id" id="refuerzo_firefighter_id" required>
                            </div>
                            <datalist id="refuerzo_volunteers_list">
                                @foreach($replacementCandidates as $c)
                                    <option data-value="{{ $c->id }}" value="{{ trim($c->nombres . ' ' . $c->apellido_paterno . ' ' . ($c->apellido_materno ?? '') . ($c->rut ? ' - ' . $c->rut : '')) }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="closeRefuerzoModal()" class="w-1/2 px-4 py-2.5 bg-slate-950 text-slate-100 font-bold rounded-lg hover:bg-slate-900 transition-colors uppercase text-sm border border-slate-800">
                                Cancelar
                            </button>
                            <button type="submit" class="w-1/2 px-4 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-black transition-colors shadow-md uppercase text-sm">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        window.__attendanceSavedToday = @json((bool) ($hasAttendanceSavedToday ?? false));
        window.__attendanceDirty = false;

        window.__guardiaSnapshot = {
            latest_novelty_at: @json(optional(($guardiaNovelties ?? null)?->first()?->updated_at ?? null)?->toISOString()),
            latest_bombero_at: @json(optional(($myStaff ?? collect())->max('updated_at') ?? null)?->toISOString()),
            latest_replacement_at: @json(optional(($replacementByOriginal ?? collect())->max('updated_at') ?? null)?->toISOString()),
            attendance_saved_at: @json(optional(($hasAttendanceSavedToday ?? false) ? (\App\Models\GuardiaAttendanceRecord::where('guardia_id', $myGuardia->id ?? null)->whereDate('date', \Carbon\Carbon::today()->toDateString())->value('saved_at')) : null)?->toISOString()),
        };

        function markAttendanceDirty() {
            if (!window.__attendanceSavedToday) return;
            if (window.__attendanceDirty) return;
            window.__attendanceDirty = true;

            const banner = document.getElementById('attendance-stale-banner');
            if (banner) banner.classList.remove('hidden');

            const badge = document.getElementById('attendance-saved-badge');
            if (badge) {
                badge.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                badge.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
                badge.textContent = 'ASISTENCIA DESACTUALIZADA';
            }
        }

        window.addEventListener('beforeunload', function (e) {
            if (!window.__attendanceDirty) return;
            e.preventDefault();
            e.returnValue = '';
        });

        window.openNoveltyModal = function() {
            const modal = document.getElementById('noveltyModal');
            if (modal) {
                modal.classList.remove('hidden');
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

        window.openRefuerzoModal = function() {
            const modal = document.getElementById('refuerzoModal');
            if (modal) modal.classList.remove('hidden');
        }

        window.closeRefuerzoModal = function() {
            const modal = document.getElementById('refuerzoModal');
            if (modal) modal.classList.add('hidden');
        }

        function syncDatalistHiddenId(displayInput, datalistEl, hiddenInput) {
            if (!displayInput || !datalistEl || !hiddenInput) return;

            const displayValue = (displayInput.value || '').trim();
            hiddenInput.value = '';

            if (!displayValue) return;

            const options = datalistEl.options;
            for (let i = 0; i < options.length; i++) {
                if ((options[i].value || '').trim() === displayValue) {
                    hiddenInput.value = options[i].getAttribute('data-value') || '';
                    break;
                }
            }
        }

        window.updateRefuerzoUserId = function(input) {
            const list = document.getElementById('refuerzo_volunteers_list');
            const hiddenInput = document.getElementById('refuerzo_firefighter_id');
            if (!list || !hiddenInput) return;

            syncDatalistHiddenId(input, list, hiddenInput);
        }

        window.removeRefuerzo = function(guardiaId, firefighterId) {
            if (!confirm('¿Quitar este refuerzo y devolverlo a su guardia anterior?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('admin.guardias.refuerzo.remove'));

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = @json(csrf_token());
            form.appendChild(csrf);

            const guardiaInput = document.createElement('input');
            guardiaInput.type = 'hidden';
            guardiaInput.name = 'guardia_id';
            guardiaInput.value = guardiaId;
            form.appendChild(guardiaInput);

            const firefighterInput = document.createElement('input');
            firefighterInput.type = 'hidden';
            firefighterInput.name = 'firefighter_id';
            firefighterInput.value = firefighterId;
            form.appendChild(firefighterInput);

            document.body.appendChild(form);
            form.submit();
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

        window.toggleInhabilitado = function(firefighterId) {
            if (!confirm('¿Inhabilitar este bombero?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('/') }}/admin/bomberos/' + firefighterId + '/toggle-fuera-servicio';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
        }

        window.toggleHabilitar = function(firefighterId) {
            if (!confirm('¿Volver a habilitar este bombero?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('/') }}/admin/bomberos/' + firefighterId + '/toggle-fuera-servicio';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
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

            syncDatalistHiddenId(input, list, hiddenInput);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const refuerzoDisplay = document.querySelector('input[list="refuerzo_volunteers_list"]');
            const refuerzoList = document.getElementById('refuerzo_volunteers_list');
            const refuerzoHidden = document.getElementById('refuerzo_firefighter_id');

            if (refuerzoDisplay && refuerzoList && refuerzoHidden) {
                ['change', 'blur'].forEach(evt => {
                    refuerzoDisplay.addEventListener(evt, () => syncDatalistHiddenId(refuerzoDisplay, refuerzoList, refuerzoHidden));
                });

                const refuerzoForm = refuerzoDisplay.closest('form');
                if (refuerzoForm) {
                    refuerzoForm.addEventListener('submit', (e) => {
                        syncDatalistHiddenId(refuerzoDisplay, refuerzoList, refuerzoHidden);
                        if ((refuerzoHidden.value || '').trim() === '') {
                            e.preventDefault();
                            alert('Debes seleccionar un voluntario de la lista.');
                        }
                    });
                }
            }

            const replDisplay = document.querySelector('#replacementModal input[list="modal_volunteers_list"]');
            const replList = document.getElementById('modal_volunteers_list');
            const replHidden = document.getElementById('modal_replacement_firefighter_id');
            if (replDisplay && replList && replHidden) {
                ['change', 'blur'].forEach(evt => {
                    replDisplay.addEventListener(evt, () => syncDatalistHiddenId(replDisplay, replList, replHidden));
                });

                const replForm = replDisplay.closest('form');
                if (replForm) {
                    replForm.addEventListener('submit', (e) => {
                        syncDatalistHiddenId(replDisplay, replList, replHidden);
                        if ((replHidden.value || '').trim() === '') {
                            e.preventDefault();
                            alert('Debes seleccionar un voluntario de la lista.');
                        }
                    });
                }
            }
        });

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
            markAttendanceDirty();
        }

        function updateGuardiaCardUI(userId, status) {
            const header = document.getElementById('card-header-' + userId);
            if (header) {
                header.classList.remove('bg-emerald-950/40','bg-purple-950/40','bg-amber-950/35','bg-slate-950','bg-blue-950/40','bg-rose-950/40');
                if (status === 'constituye') header.classList.add('bg-emerald-950/40');
                else if (status === 'reemplazo') header.classList.add('bg-purple-950/40');
                else if (status === 'permiso') header.classList.add('bg-amber-950/35');
                else if (status === 'ausente') header.classList.add('bg-slate-950');
                else if (status === 'licencia') header.classList.add('bg-blue-950/40');
                else if (status === 'falta') header.classList.add('bg-rose-950/40');
                else header.classList.add('bg-slate-950');
            }

            document.querySelectorAll('button[data-user-id="' + userId + '"][data-status]').forEach(btn => {
                const s = btn.getAttribute('data-status');
                btn.classList.remove(
                    'bg-emerald-500/80','text-white','border-emerald-400/50','shadow-sm',
                    'bg-amber-500/80','border-amber-400/50',
                    'bg-slate-400/30','border-slate-500/30',
                    'bg-blue-600/80','border-blue-400/50',
                    'bg-red-600/80','border-red-400/50'
                );
                btn.classList.add('shadow-none');

                // Restaurar clases base por tipo
                if (s === 'constituye') {
                    btn.classList.add('bg-emerald-500/15','text-emerald-200','border-emerald-500/25','hover:bg-emerald-500/20');
                }
                if (s === 'permiso') {
                    btn.classList.add('bg-amber-500/15','text-amber-200','border-amber-500/25','hover:bg-amber-500/20');
                }
                if (s === 'ausente') {
                    btn.classList.add('bg-slate-500/10','text-slate-200','border-slate-500/20','hover:bg-slate-500/15');
                }
                if (s === 'licencia') {
                    btn.classList.add('bg-blue-600/15','text-blue-200','border-blue-500/25','hover:bg-blue-600/20');
                }
                if (s === 'falta') {
                    btn.classList.add('bg-red-600/15','text-red-200','border-red-500/25','hover:bg-red-600/20');
                }

                if (s === status) {
                    // Quitar base y aplicar activo
                    btn.classList.remove(
                        'bg-emerald-500/15','text-emerald-200','border-emerald-500/25','hover:bg-emerald-500/20',
                        'bg-amber-500/15','text-amber-200','border-amber-500/25','hover:bg-amber-500/20',
                        'bg-slate-500/10','text-slate-200','border-slate-500/20','hover:bg-slate-500/15',
                        'bg-blue-600/15','text-blue-200','border-blue-500/25','hover:bg-blue-600/20',
                        'bg-red-600/15','text-red-200','border-red-500/25','hover:bg-red-600/20'
                    );
                    if (s === 'constituye') btn.classList.add('bg-emerald-500/80','text-white','border-emerald-400/50','shadow-sm');
                    if (s === 'permiso') btn.classList.add('bg-amber-500/80','text-white','border-amber-400/50','shadow-sm');
                    if (s === 'ausente') btn.classList.add('bg-slate-400/30','text-white','border-slate-500/30','shadow-sm');
                    if (s === 'licencia') btn.classList.add('bg-blue-600/80','text-white','border-blue-400/50','shadow-sm');
                    if (s === 'falta') btn.classList.add('bg-red-600/80','text-white','border-red-400/50','shadow-sm');
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

        @if(Auth::check() && Auth::user()->role === 'guardia')
            async function kioskPing() {
                try {
                    const res = await fetch('{{ route('kiosk.ping') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (res.status === 401) {
                        window.location.reload();
                        return;
                    }
                } catch (e) {
                    // noop
                }
            }

            kioskPing();
            setInterval(kioskPing, 60 * 1000);

            async function softRefreshGuardiaDashboard() {
                const root = document.getElementById('guardia-dashboard-root');
                if (!root) return;

                const activeEl = document.activeElement;
                const activeId = activeEl?.id;
                const activeName = activeEl?.getAttribute?.('name');
                const scrollY = window.scrollY;

                try {
                    const res = await fetch(window.location.href, {
                        method: 'GET',
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (res.status === 401) {
                        window.location.reload();
                        return;
                    }

                    if (!res.ok) return;

                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextRoot = doc.getElementById('guardia-dashboard-root');
                    if (!nextRoot) return;

                    root.innerHTML = nextRoot.innerHTML;

                    window.scrollTo(0, scrollY);

                    if (activeId) {
                        const nextActive = document.getElementById(activeId);
                        if (nextActive && typeof nextActive.focus === 'function') nextActive.focus();
                    } else if (activeName) {
                        const esc = (window.CSS && typeof window.CSS.escape === 'function') ? window.CSS.escape : (v) => String(v).replace(/"/g, '\\"');
                        const nextActive = document.querySelector('[name="' + esc(activeName) + '"]');
                        if (nextActive && typeof nextActive.focus === 'function') nextActive.focus();
                    }
                } catch (e) {
                    // noop
                }
            }

            async function checkGuardiaUpdates() {
                try {
                    if (window.__attendanceDirty) return;
                    const res = await fetch('{{ route('guardia.snapshot') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });
                    if (!res.ok) return;

                    const data = await res.json();
                    if (!data || !data.ok) return;

                    const prev = window.__guardiaSnapshot || {};
                    const changed = (
                        (data.latest_novelty_at && data.latest_novelty_at !== prev.latest_novelty_at) ||
                        (data.latest_bombero_at && data.latest_bombero_at !== prev.latest_bombero_at) ||
                        (data.latest_replacement_at && data.latest_replacement_at !== prev.latest_replacement_at) ||
                        (data.attendance_saved_at && data.attendance_saved_at !== prev.attendance_saved_at)
                    );

                    if (changed) {
                        await softRefreshGuardiaDashboard();
                        window.__guardiaSnapshot = {
                            latest_novelty_at: data.latest_novelty_at,
                            latest_bombero_at: data.latest_bombero_at,
                            latest_replacement_at: data.latest_replacement_at,
                            attendance_saved_at: data.attendance_saved_at,
                        };
                        return;
                    }

                    window.__guardiaSnapshot = {
                        latest_novelty_at: data.latest_novelty_at,
                        latest_bombero_at: data.latest_bombero_at,
                        latest_replacement_at: data.latest_replacement_at,
                        attendance_saved_at: data.attendance_saved_at,
                    };
                } catch (e) {
                    // noop
                }
            }

            setInterval(checkGuardiaUpdates, 20 * 1000);
        @endif
    </script>

    <div id="undoReplacementModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }} rounded-2xl shadow-2xl w-full max-w-sm mx-4 border overflow-hidden">
            <div class="p-4">
                <div class="text-sm font-black {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-900' }} uppercase tracking-widest">Confirmar acción</div>
                <div class="mt-2 text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-600' }}">¿Deshacer este reemplazo?</div>
            </div>
            <div class="p-4 pt-0 flex gap-2">
                <button type="button" onclick="closeUndoReplacementModal()" class="w-1/2 font-black uppercase tracking-widest text-[10px] py-2 rounded-xl border {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border-slate-800' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200' }}">
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
            <div class="bg-slate-900 text-slate-100 rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-800">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-100 uppercase tracking-tight">Asignar Reemplazo</h3>
                        <p class="text-sm text-slate-400 mt-1">Selecciona el voluntario que cubrirá el turno.</p>
                    </div>
                    <button type="button" onclick="closeReplacementModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 mb-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-blue-300 font-bold shrink-0">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-wide">Reemplazando a:</span>
                        <p id="modal_original_user_name" class="text-sm font-bold text-slate-100">Usuario Original</p>
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
                                <input list="modal_volunteers_list" name="replacement_firefighter_id_display" autocomplete="off"
                                class="w-full text-sm border-slate-800 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-slate-700 pl-9 py-2.5 bg-slate-950 text-slate-100 placeholder:text-slate-500"
                                placeholder="Buscar voluntario..." required
                                oninput="updateModalReplacementUserId(this)">
                            <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_firefighter_id" required>
                        </div>
                            <datalist id="modal_volunteers_list">
                                @foreach($replacementCandidates as $cand)
                                    <option data-value="{{ $cand->id }}" value="{{ trim($cand->nombres . ' ' . $cand->apellido_paterno . ' ' . ($cand->apellido_materno ?? '') . ($cand->rut ? ' - ' . $cand->rut : '')) }}"></option>
                                @endforeach
                            </datalist>

                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="closeReplacementModal()" class="w-1/2 py-2.5 px-4 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
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

    <script>
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
