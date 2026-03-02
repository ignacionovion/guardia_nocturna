@extends('layouts.app')

@section('content')
    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        @php
            $guardiaTz = \App\Models\SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
            $guardiaDailyEndTime = \App\Models\SystemSetting::getValue('guardia_daily_end_time', '07:00');

            $localNow = now()->copy()->setTimezone($guardiaTz);
            [$endH, $endM] = array_map('intval', explode(':', (string) $guardiaDailyEndTime));
            $dailyEndAt = $localNow->copy()->setTime($endH, $endM, 0);
            $shiftClosedForToday = $localNow->greaterThanOrEqualTo($dailyEndAt);

            // Ventana fija: 22:00 -> 07:00 (sin override)
            $attendanceEnabled = (function () use ($localNow) {
                $hour = (int) $localNow->hour;
                return $hour >= 22 || $hour < 7;
            })();
            // Filtrar personal activo (todos los de la guardia excepto fuera de servicio)
            $activeStaff = $myStaff->reject(function ($u) use ($replacementByOriginal) {
                $isReplaced = (bool) ($replacementByOriginal && $replacementByOriginal->has($u->id));
                return (bool) ($u->fuera_de_servicio ?? false) || $isReplaced;
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
        <div id="guardia-dashboard-root" class="w-full min-h-screen px-4 md:px-6 lg:px-8 py-4 pt-[calc(env(safe-area-inset-top)+1.25rem)] bg-slate-900 text-slate-100">
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
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-green-200 bg-green-50 text-green-700 shrink-0">SEMANA DE GUARDIA</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-100 text-slate-700 shrink-0">FUERA DE TURNO</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-xs font-medium text-slate-500">{{ $activeStaff->count() }} bomberos | {{ $onDutyStaff->count() }} presentes</div>
                    </div>
                </div>

                <div id="attendance-stale-banner" class="hidden fixed inset-0 z-[55] flex items-center justify-center">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAttendanceStaleBanner()"></div>
                    <div class="relative w-full max-w-lg mx-4 px-6 py-5 rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 shadow-2xl">
                        <button onclick="closeAttendanceStaleBanner()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-amber-100 hover:bg-amber-200 flex items-center justify-center border border-amber-200 text-amber-700 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 border border-amber-200">
                                <i class="fas fa-triangle-exclamation text-xl"></i>
                            </div>
                            <div class="min-w-0 pt-1">
                                <div class="text-sm font-black uppercase tracking-widest mb-1">Asistencia desactualizada</div>
                                <div class="text-base font-bold leading-snug">Se detectaron cambios después de guardar la asistencia. Debes presionar <span class="font-black text-amber-700">Guardar Asistencia</span> nuevamente para confirmar.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:flex-1 flex items-center justify-start md:justify-center">
                    <div class="flex items-center gap-2 sm:gap-3 w-full md:w-auto overflow-x-auto md:overflow-visible -mx-1 px-1">
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
                        <button id="guardia-attendance-submit" form="guardia-attendance-form" type="submit" @if(!$attendanceEnabled) disabled @endif class="w-9 h-9 sm:w-10 sm:h-10 {{ $attendanceEnabled ? 'bg-slate-800 hover:bg-slate-700 text-slate-100 border-slate-700 shadow-sm' : 'bg-slate-200 text-slate-500 border-slate-300 shadow-sm cursor-not-allowed' }} rounded-xl transition-all border flex items-center justify-center" title="Guardar Asistencia">
                            <i class="fas fa-floppy-disk text-[14px] {{ $attendanceEnabled ? 'text-emerald-300' : '' }}"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
                    @php
                        $guardiaTz = \App\Models\SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
                        $localNow = now()->copy()->setTimezone($guardiaTz);
                        $currentTime = $localNow->format('H:i');
                        $isAfter702 = $localNow->hour > 7 || ($localNow->hour === 7 && $localNow->minute >= 2);
                        $isAfter2200 = $localNow->hour >= 22;
                        
                        // Determinar mensaje apropiado
                        $attendanceMessage = '';
                        $attendanceBadgeClass = '';
                        
                        if ($shiftClosedForToday) {
                            $attendanceMessage = 'RECORDAR REGISTRAR GUARDIA A LAS 22:00';
                            $attendanceBadgeClass = 'border-amber-200 bg-amber-50 text-amber-800';
                        } elseif (isset($hasAttendanceSavedToday) && $hasAttendanceSavedToday) {
                            // Asistencia guardada correctamente - SIEMPRE mostrar verde
                            $attendanceMessage = 'ASISTENCIA REGISTRADA CORRECTAMENTE';
                            $attendanceBadgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                        } else {
                            if ($isAfter2200) {
                                $attendanceMessage = 'GUARDA LA ASISTENCIA ANTES DE IRTE';
                                $attendanceBadgeClass = 'border-red-200 bg-red-50 text-red-700';
                            } else {
                                $attendanceMessage = 'SIN REGISTRAR ASISTENCIA';
                                $attendanceBadgeClass = 'border-red-200 bg-red-50 text-red-700';
                            }
                        }
                    @endphp
                    <span id="attendance-saved-badge" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border {{ $attendanceBadgeClass }} shrink-0">{{ $attendanceMessage }}</span>

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
                                    $repAsReplacement = isset($replacementByReplacement) ? $replacementByReplacement->get($staff->id) : null;
                                    $repAsOriginal = isset($replacementByOriginal) ? $replacementByOriginal->get($staff->id) : null;
                                    $status = $repAsReplacement ? 'reemplazo' : $staff->estado_asistencia;
                                    $lockAttendanceStatus = (bool) ($repAsReplacement || $staff->es_refuerzo || $repAsOriginal);
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
                                <input type="hidden" name="users[{{ $staff->id }}][confirm_token]" id="confirm-token-{{ $staff->id }}" value="">

                                <div id="guardia-card-{{ $staff->id }}" class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col h-full min-h-[420px]" data-card-user="{{ $staff->id }}" data-requires-confirmation="{{ (in_array($status, ['constituye','reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement) ? '1' : '0' }}" data-is-confirmed="0">
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
                                        <div class="relative bg-slate-950 rounded-xl border border-slate-800 overflow-hidden flex items-stretch justify-stretch h-[120px]">
                                            @if($staff->photo_path)
                                                <img src="{{ url('media/' . ltrim($staff->photo_path, '/')) }}" class="w-full h-full object-cover" alt="Foto">
                                            @else
                                                <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-200 font-black text-[12px]">
                                                    {{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="absolute bottom-1 left-1 right-1 flex items-center justify-center gap-1">
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

                                            @if($staff->es_permanente)
                                                <div class="mt-1 text-[9px] font-black uppercase tracking-widest text-emerald-200 bg-emerald-500/15 border border-emerald-500/25 rounded px-1.5 py-0.5 w-fit">
                                                    PERMANENTE
                                                </div>
                                            @endif

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

                                    @if($repAsOriginal)
                                        <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-2 text-amber-800">
                                            <div class="text-[11px] font-black uppercase tracking-widest">Reemplazado por</div>
                                            <div class="text-sm font-black leading-tight truncate">
                                                {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="w-full bg-white hover:bg-amber-100 text-amber-800 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-amber-200">
                                                    Deshacer reemplazo
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-1.5">
                                        <div id="confirm-box-wrap-{{ $staff->id }}" class="{{ (in_array($status, ['constituye','reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement) ? '' : 'hidden' }}">
                                            <div id="confirm-box-{{ $staff->id }}" class="mb-2 rounded-xl border border-slate-800 bg-slate-950 px-2.5 py-2">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div id="confirm-status-{{ $staff->id }}" class="text-[9px] font-black uppercase tracking-widest text-rose-200">NO CONFIRMADO</div>
                                                </div>
                                                <div id="confirm-controls-{{ $staff->id }}" class="mt-1.5 flex items-center gap-2">
                                                    <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="Código" class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-100 placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
                                                    <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-100 text-[9px] font-black uppercase tracking-widest border border-slate-700">Confirmar</button>
                                                </div>
                                                <div id="confirm-msg-{{ $staff->id }}" class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400"></div>
                                            </div>
                                        </div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estado</label>
                                        @if($lockAttendanceStatus)
                                            @if($repAsReplacement)
                                                <div class="rounded-lg border border-purple-500/30 bg-purple-500/15 text-purple-200 px-3 py-2 text-center">
                                                    <div class="text-sm font-black">REEMPLAZO</div>
                                                </div>
                                            @else
                                                <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/15 text-emerald-200 px-3 py-2 text-center">
                                                    <div class="text-sm font-black">CONSTITUYE</div>
                                                </div>
                                            @endif
                                        @else
                                            @php
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
                                            @endphp
                                            <button type="button" id="status-cycle-{{ $staff->id }}" data-user-id="{{ $staff->id }}" data-status="{{ $status }}" onclick="cycleGuardiaStatus('{{ $staff->id }}')" class="w-full px-2 py-2 rounded-lg border text-[11px] font-black uppercase tracking-widest transition flex items-center justify-center gap-2 shadow-sm {{ $statusBtnClass }}">
                                                <span id="status-cycle-label-{{ $staff->id }}">{{ $statusLabel }}</span>
                                                <i class="fas fa-rotate text-[10px] opacity-80"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="mt-1.5 {{ ($repAsReplacement || $staff->es_refuerzo || $repAsOriginal) ? 'min-h-0' : 'min-h-[72px]' }}">
                                        @if(!($repAsReplacement || $staff->es_refuerzo || $repAsOriginal))
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
                                        @php
                                            $noveltyColors = [
                                                'Informativa' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-400', 'border' => 'border-blue-500'],
                                                'Incidente' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-400', 'border' => 'border-amber-500'],
                                                'Mantención' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500'],
                                                'Urgente' => ['bg' => 'bg-red-500', 'text' => 'text-red-400', 'border' => 'border-red-500'],
                                                'Permanente' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-400', 'border' => 'border-purple-500'],
                                            ];
                                            $colors = $noveltyColors[$novelty->type] ?? $noveltyColors['Informativa'];
                                        @endphp
                                        <div class="border-l-2 border-slate-700 pl-4 py-2">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs font-black {{ $colors['text'] }} uppercase tracking-wider">{{ $novelty->type }}</span>
                                                @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                                                    <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">PERMANENTE</span>
                                                @endif
                                            </div>
                                            <div class="text-sm font-black text-slate-100">{{ $novelty->title }}</div>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-2">
                                                {{ $novelty->created_at->locale('es')->diffForHumans() }}
                                                @if($novelty->user)
                                                    <span class="text-slate-600">|</span>
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
                                                {{ ($academy->created_at ?? $academy->date)?->locale('es')->diffForHumans() }}
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
        <!-- VISTA ADMIN / GENERAL - DASHBOARD PROFESIONAL -->
        
        <!-- Header Profesional -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4 pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase flex items-center gap-3">
                    <i class="fas fa-gauge-high text-red-700"></i>
                    Centro de Operaciones
                </h1>
                <p class="text-slate-500 mt-1 font-medium text-sm">Panel de control operativo del sistema</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Estado del Sistema -->
                <div class="flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-black text-green-700 uppercase tracking-wider">Sistema Operativo</span>
                </div>
                
                <!-- Reloj -->
                <div class="bg-slate-900 text-white px-5 py-2.5 rounded-lg border border-slate-700 flex items-center gap-3">
                    <i class="fas fa-clock text-slate-400 text-sm"></i>
                    <span id="digital-clock" class="text-xl font-mono font-bold tracking-wider">--:--:--</span>
                </div>
            </div>
        </div>

        <!-- Grid Principal: KPIs y Accesos -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            
            <!-- Columna Izquierda: Estado Operativo -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Tarjetas KPI Principales -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Guardia Activa -->
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-1">Guardia en Servicio</p>
                                <h3 class="text-lg font-black text-slate-900">
                                    {{ $guardiaEnServicio?->name ?? 'Sin asignar' }}
                                </h3>
                            </div>
                            <div class="bg-red-100 p-2 rounded-lg text-red-700">
                                <i class="fas fa-shield text-lg"></i>
                            </div>
                        </div>
                        @if($guardiaEnServicio)
                            <div class="mt-3 flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                <span class="text-xs font-bold text-slate-600">Operativa</span>
                            </div>
                        @endif
                    </div>

                    <!-- Personal en Turno -->
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-1">Personal en Guardia Nocturna</p>
                                <h3 class="text-2xl font-black text-slate-900">{{ $onDutyCount }}</h3>
                            </div>
                            <div class="bg-blue-100 p-2 rounded-lg text-blue-700">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Bomberos activos</p>
                    </div>

                    <!-- Camas Disponibles -->
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-1">Camas Libres</p>
                                <h3 class="text-2xl font-black text-slate-900">{{ $availableBeds }}<span class="text-sm text-slate-400 font-normal">/{{ $totalBeds }}</span></h3>
                            </div>
                            <div class="bg-emerald-100 p-2 rounded-lg text-emerald-700">
                                <i class="fas fa-bed text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">{{ $occupiedBeds }} ocupadas</p>
                    </div>

                    <!-- Novedades -->
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-1">Novedades</p>
                                <h3 class="text-2xl font-black text-slate-900">{{ $novelties->count() }}</h3>
                            </div>
                            <div class="bg-amber-100 p-2 rounded-lg text-amber-700">
                                <i class="fas fa-clipboard-list text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Últimas 24 horas</p>
                    </div>
                </div>

                <!-- Panel de Movimientos - Estilo Reporte Profesional -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-900">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-500/20 p-2 rounded-lg text-purple-400">
                                    <i class="fas fa-exchange-alt text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="font-black text-white uppercase tracking-wider text-sm">Reporte de Movimientos</h2>
                                    <p class="text-xs text-slate-400">Reemplazos, refuerzos y personal fuera de servicio</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-400 uppercase">Total reemplazos:</span>
                                <span class="text-xl font-black text-purple-400">{{ $activeReplacementsCount }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Estadísticas Rápidas -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-purple-50 rounded-xl border border-purple-200 p-4 text-center">
                                <div class="text-2xl font-black text-purple-700">{{ $activeReplacementsCount }}</div>
                                <div class="text-xs font-bold text-purple-600 uppercase tracking-wider">Reemplazos Activos</div>
                            </div>
                            <div class="bg-sky-50 rounded-xl border border-sky-200 p-4 text-center">
                                <div class="text-2xl font-black text-sky-700">{{ $activeRefuerzosCount }}</div>
                                <div class="text-xs font-bold text-sky-600 uppercase tracking-wider">Refuerzos</div>
                            </div>
                            <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
                                <div class="text-2xl font-black text-red-700">{{ $outOfServiceFirefighters }}</div>
                                <div class="text-xs font-bold text-red-600 uppercase tracking-wider">Fuera de Servicio</div>
                            </div>
                        </div>

                        <!-- Lista Detallada de Reemplazos Activos -->
                        @if(isset($replacementByOriginal) && $replacementByOriginal->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-list-ul"></i>
                                    Detalle de Reemplazos Activos
                                </h3>
                                <div class="space-y-3">
                                    @foreach($replacementByOriginal as $replacement)
                                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                                            <div class="flex items-start gap-4">
                                                <!-- Reemplazado -->
                                                <div class="flex-1">
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Titular Reemplazado</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->originalFirefighter?->nombres, 0, 1) }}{{ substr($replacement->originalFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900">{{ $replacement->originalFirefighter?->nombres }} {{ $replacement->originalFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-slate-500">{{ $replacement->originalFirefighter?->rut ?? 'Sin RUT' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Flecha -->
                                                <div class="pt-4">
                                                    <div class="bg-purple-100 p-2 rounded-full">
                                                        <i class="fas fa-arrow-right text-purple-600"></i>
                                                    </div>
                                                </div>

                                                <!-- Reemplazante -->
                                                <div class="flex-1">
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reemplazante</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->replacementFirefighter?->nombres, 0, 1) }}{{ substr($replacement->replacementFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900">{{ $replacement->replacementFirefighter?->nombres }} {{ $replacement->replacementFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-slate-500">{{ $replacement->replacementFirefighter?->rut ?? 'Sin RUT' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Info adicional -->
                                                <div class="text-right">
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Inicio</div>
                                                    <div class="text-sm font-bold text-slate-700">{{ $replacement->inicio?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                                                    <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $replacement->id) }}')" class="mt-2 text-xs font-bold text-purple-600 hover:text-purple-800 underline">
                                                        Deshacer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Lista Detallada de Refuerzos Activos -->
                        @if(isset($myStaff) && $myStaff->where('es_refuerzo', true)->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-user-friends"></i>
                                    Detalle de Refuerzos Activos
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($myStaff->where('es_refuerzo', true) as $refuerzo)
                                        <div class="bg-sky-50 rounded-xl border border-sky-200 p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">
                                                    {{ substr($refuerzo->nombres, 0, 1) }}{{ substr($refuerzo->apellido_paterno, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-slate-900 truncate">{{ $refuerzo->nombres }} {{ $refuerzo->apellido_paterno }}</div>
                                                    <div class="text-xs text-slate-500">{{ $refuerzo->rut ?? 'Sin RUT' }}</div>
                                                </div>
                                                <span class="text-[10px] font-black uppercase tracking-widest text-sky-700 bg-sky-100 px-2 py-1 rounded">REFUERZO</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if((!isset($replacementByOriginal) || $replacementByOriginal->isEmpty()) && (!isset($myStaff) || $myStaff->where('es_refuerzo', true)->isEmpty()))
                            <div class="text-center py-8 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-200 rounded-full mb-3">
                                    <i class="fas fa-check text-slate-500"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-600">Sin movimientos activos</p>
                                <p class="text-xs text-slate-400 mt-1">No hay reemplazos ni refuerzos registrados</p>
                            </div>
                        @endif

                        <!-- Acciones Rápidas -->
                        @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
                            <div class="flex gap-3 pt-4 border-t border-slate-200">
                                <button onclick="openReplacementModal()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i>
                                    Nuevo Reemplazo
                                </button>
                                <button onclick="openRefuerzoModal()" class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-user-plus"></i>
                                    Agregar Refuerzo
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Resumen de Personal -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="font-black text-slate-800 uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-users text-slate-500"></i>
                            Resumen de Personal
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <p class="text-3xl font-black text-slate-900">{{ $totalFirefighters }}</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Total Bomberos</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-black text-emerald-600">{{ $activeFirefighters }}</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Activos</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-black text-slate-900">{{ $totalGuardias }}</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Guardias</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-black text-amber-600">{{ $birthdaysMonthCount }}</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Cumpleaños Mes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos Directos -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('admin.guardias') }}" class="group bg-slate-900 hover:bg-slate-800 text-white rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-white/10 p-2 rounded-lg">
                            <i class="fas fa-shield"></i>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase">Guardias</p>
                            <p class="text-xs text-slate-400">Administrar equipos</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.dotaciones') }}" class="group bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-slate-100 p-2 rounded-lg text-slate-600">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase">Dotaciones</p>
                            <p class="text-xs text-slate-500">Asignar personal</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('camas') }}" class="group bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-emerald-100 p-2 rounded-lg text-emerald-600">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase">Camas</p>
                            <p class="text-xs text-slate-500">{{ $availableBeds }} disponibles</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.emergencies.index') }}" class="group bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-amber-100 p-2 rounded-lg text-amber-600">
                            <i class="fas fa-truck-medical"></i>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase">Emergencias</p>
                            <p class="text-xs text-slate-500">Ver historial</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Columna Derecha: Información y Alertas -->
            <div class="space-y-6">
                
                <!-- Estado del Turno -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                        <h2 class="font-black text-slate-800 uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-info-circle text-slate-500"></i>
                            Estado del Turno
                        </h2>
                    </div>
                    <div class="p-5">
                        @if($currentShift)
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <p class="font-black text-slate-900">TURNO ACTIVO</p>
                            </div>
                            <p class="text-sm text-slate-600 mb-1">Guardia constituida y operativa</p>
                            <p class="text-xs text-slate-400">Inicio: {{ $currentShift->created_at?->format('H:i') ?? '--:--' }}</p>
                        @else
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                                <p class="font-black text-amber-700">SIN CONSTITUIR</p>
                            </div>
                            <p class="text-sm text-slate-600">La guardia aún no ha sido constituida</p>
                        @endif
                    </div>
                </div>

                <!-- Próximos Cumpleaños -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h2 class="font-black text-slate-800 uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-cake-candles text-amber-500"></i>
                            Cumpleaños
                        </h2>
                        <span class="text-xs font-bold text-slate-500">{{ $birthdaysMonthCount }} este mes</span>
                    </div>
                    <div class="p-5">
                        @if($upcomingBirthdaysAll->isEmpty())
                            <p class="text-sm text-slate-500 text-center">No hay cumpleaños próximos</p>
                        @else
                            <div class="space-y-3">
                                @foreach($upcomingBirthdaysAll as $b)
                                    <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                                            {{ substr($b->nombres, 0, 1) }}{{ substr($b->apellido_paterno, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-900 text-sm truncate">{{ $b->nombres }} {{ $b->apellido_paterno }}</p>
                                            <p class="text-xs text-slate-500">{{ $b->next_birthday->format('d') }} de {{ $b->next_birthday->locale('es')->monthName }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Últimas Novedades -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h2 class="font-black text-slate-800 uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-bullhorn text-slate-500"></i>
                            Novedades Recientes
                        </h2>
                        <button onclick="openNoveltyModal()" class="text-xs font-black text-blue-600 hover:text-blue-800 uppercase">Registrar</button>
                    </div>
                    <div class="p-5">
                        @if($novelties->isEmpty())
                            <p class="text-sm text-slate-500 text-center">Sin novedades recientes</p>
                        @else
                            <div class="space-y-4">
                                @foreach($novelties->take(3) as $novelty)
                                    @php
                                        $noveltyColors = [
                                            'Informativa' => ['text' => 'text-blue-600', 'bgLight' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                            'Incidente' => ['text' => 'text-amber-600', 'bgLight' => 'bg-amber-50', 'border' => 'border-amber-200'],
                                            'Mantención' => ['text' => 'text-emerald-600', 'bgLight' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                                            'Urgente' => ['text' => 'text-red-600', 'bgLight' => 'bg-red-50', 'border' => 'border-red-200'],
                                            'Permanente' => ['text' => 'text-purple-600', 'bgLight' => 'bg-purple-50', 'border' => 'border-purple-200'],
                                        ];
                                        $colors = $noveltyColors[$novelty->type] ?? $noveltyColors['Informativa'];
                                    @endphp
                                    <div class="border-l-2 border-slate-300 pl-3 py-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-bold {{ $colors['text'] }} {{ $colors['bgLight'] }} px-2 py-0.5 rounded border {{ $colors['border'] }}">{{ $novelty->type }}</span>
                                            @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">PERMANENTE</span>
                                            @endif
                                        </div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $novelty->title }}</p>
                                        <p class="text-xs text-slate-500 mt-1">{{ $novelty->created_at->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
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
                        <option value="Permanente" {{ old('type') == 'Permanente' ? 'selected' : '' }}>Permanente (Todas las Guardias)</option>
                    </select>
                    <div class="mt-1 text-[10px] text-slate-500">
                        <i class="fas fa-info-circle mr-1"></i> Las novedades "Permanentes" son visibles para todas las guardias. Solo admin/capitán pueden eliminarlas.
                    </div>
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
                        <div class="text-[11px] text-slate-500 mt-1">No se detectó personal en la guardia nocturna.</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700' }}">Día y hora</label>
                    <input type="datetime-local" name="date" value="{{ now()->copy()->setTimezone($guardiaTz)->format('Y-m-d\\TH:i') }}" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100' : 'bg-white border-slate-300 text-slate-900' }}" required>
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
        <div id="refuerzoModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
            <div class="bg-slate-900 text-slate-100 rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-800">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-100 uppercase tracking-tight">Agregar Refuerzo</h3>
                        <p class="text-sm text-slate-400 mt-1">El refuerzo se libera automáticamente a las 10:00 AM del día siguiente.</p>
                    </div>
                    <button type="button" onclick="closeRefuerzoModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.guardias.refuerzo') }}">
                    @csrf
                    <input type="hidden" name="guardia_id" value="{{ $myGuardia?->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Voluntario</label>
                            <!-- Custom Professional Dropdown -->
                            <div class="relative" id="refuerzo-select-container">
                                <input type="hidden" name="firefighter_id" id="refuerzo_firefighter_id" required>
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="refuerzo-search-input"
                                        class="w-full text-sm border-slate-800 rounded-lg shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 pl-9 pr-10 py-2.5 bg-slate-950 text-slate-100 placeholder:text-slate-500 cursor-pointer"
                                        placeholder="Buscar voluntario..." autocomplete="off" readonly>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                                
                                <!-- Dropdown Menu -->
                                <div id="refuerzo-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50 max-h-64 overflow-y-auto">
                                    <div class="p-2 sticky top-0 bg-slate-900 border-b border-slate-800">
                                        <input type="text" id="refuerzo-filter-input" 
                                            class="w-full text-xs bg-slate-800 border-slate-700 rounded px-2 py-1.5 text-slate-200 placeholder:text-slate-500 focus:outline-none focus:border-sky-500"
                                            placeholder="Filtrar por nombre o RUT...">
                                    </div>
                                    <div id="refuerzo-options-list" class="py-1">
                                        @foreach($replacementCandidates as $cand)
                                            <div class="refuerzo-option px-3 py-2 hover:bg-slate-800 cursor-pointer transition-colors flex items-center gap-3"
                                                 data-value="{{ $cand->id }}"
                                                 data-search="{{ strtolower(trim($cand->nombres . ' ' . $cand->apellido_paterno . ' ' . ($cand->apellido_materno ?? '') . ' ' . ($cand->rut ?? ''))) }}">
                                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 text-xs font-bold">
                                                    {{ strtoupper(substr($cand->nombres, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-slate-200 truncate">
                                                        {{ trim($cand->nombres . ' ' . $cand->apellido_paterno) }}
                                                    </div>
                                                    @if($cand->rut)
                                                        <div class="text-xs text-slate-500">{{ $cand->rut }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="refuerzo-no-results" class="hidden px-3 py-4 text-center text-xs text-slate-500">
                                        No se encontraron resultados
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="closeRefuerzoModal()" class="w-1/2 py-2.5 px-4 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
                                Cancelar
                            </button>
                            <button type="submit" class="w-1/2 py-2.5 px-4 rounded-lg bg-sky-600 text-white font-bold text-sm hover:bg-sky-700 shadow-md hover:shadow-lg transition-all uppercase flex items-center justify-center gap-2">
                                <span>Confirmar</span>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="hidden border-2 border-rose-400 border-emerald-400 border-slate-800"></div>

    <div id="confirm-error-toast" class="fixed inset-0 z-[60] hidden items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeConfirmErrorToast()"></div>
        <div class="relative bg-slate-900 border border-rose-500/50 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-500/20 flex items-center justify-center">
                <i class="fas fa-circle-xmark text-rose-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-black text-white uppercase tracking-widest mb-2">Código Incorrecto</h3>
            <p id="confirm-error-text" class="text-sm text-slate-400 mb-4">El código ingresado no es válido.</p>
            <button onclick="closeConfirmErrorToast()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black uppercase tracking-widest text-xs py-3 rounded-xl border border-rose-700">
                Aceptar
            </button>
        </div>
    </div>

    <script>
        window.closeConfirmErrorToast = function() {
            const toast = document.getElementById('confirm-error-toast');
            if (toast) {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
            }
        }

        window.closeAttendanceStaleBanner = function() {
            const banner = document.getElementById('attendance-stale-banner');
            if (banner) {
                banner.classList.add('hidden');
                banner.classList.remove('flex');
            }
        }
        window.__guardiaId = @json((int) ($myGuardia->id ?? 0));
        window.__attendanceSavedToday = @json((bool) ($hasAttendanceSavedToday ?? false));
        window.__attendanceDirty = false;
        window.__attendanceSubmitting = false;

        function getLocalYmd() {
            try {
                return new Date().toLocaleDateString('sv-SE');
            } catch (e) {
                return new Date().toISOString().slice(0, 10);
            }
        }

        window.__guardiaSnapshot = {
            latest_novelty_at: @json(optional(($guardiaNovelties ?? null)?->first()?->updated_at ?? null)?->toISOString()),
            latest_bombero_at: @json(optional(($myStaff ?? collect())->max('updated_at') ?? null)?->toISOString()),
            latest_replacement_at: @json(optional(($replacementByOriginal ?? collect())->max('updated_at') ?? null)?->toISOString()),
            attendance_saved_at: @json(optional(($hasAttendanceSavedToday ?? false) ? (\App\Models\GuardiaAttendanceRecord::where('guardia_id', $myGuardia->id ?? null)->whereDate('date', \Carbon\Carbon::today()->toDateString())->value('saved_at')) : null)?->toISOString()),
            latest_draft_at: null,
        };

        function markAttendanceDirty() {
            if (!window.__attendanceSavedToday) return;
            if (window.__attendanceDirty) return;
            window.__attendanceDirty = true;

            const banner = document.getElementById('attendance-stale-banner');
            if (banner) {
                banner.classList.remove('hidden');
                banner.classList.add('flex');
            }

            const badge = document.getElementById('attendance-saved-badge');
            if (badge) {
                badge.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                badge.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
                badge.textContent = 'ASISTENCIA DESACTUALIZADA';
            }
        }

        window.addEventListener('beforeunload', function (e) {
            if (window.__attendanceSubmitting) return;
            if (!window.__attendanceDirty) return;
            e.preventDefault();
            e.returnValue = '';
        });

        window.bindAttendanceFormHandlers = function() {
            const form = document.getElementById('guardia-attendance-form');
            const submitBtn = document.getElementById('guardia-attendance-submit');
            if (!form || form.getAttribute('data-bound') === '1') return;
            form.setAttribute('data-bound', '1');

            const markSubmitting = function() {
                window.__attendanceSubmitting = true;
                window.__attendanceDirty = false;

                // Defer UI mutations to avoid cancelling the native submit in some browsers.
                setTimeout(function() {
                    try {
                        const banner = document.getElementById('attendance-stale-banner');
                        if (banner) {
                            banner.classList.add('hidden');
                            banner.classList.remove('flex');
                        }
                    } catch (e) {}

                    try {
                        const badge = document.getElementById('attendance-saved-badge');
                        if (badge) {
                            badge.textContent = 'GUARDANDO ASISTENCIA...';
                            badge.classList.remove('border-red-200', 'bg-red-50', 'text-red-700');
                            badge.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
                        }
                    } catch (e) {}

                    if (submitBtn) {
                        submitBtn.setAttribute('disabled', 'disabled');
                        submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                        submitBtn.classList.add('bg-slate-200','text-slate-500','border-slate-300','cursor-not-allowed');
                    }
                }, 0);
            };

            form.addEventListener('submit', function() {
                markSubmitting();
            });
        };

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
            if (!modal) return;
            
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

        window.closeRefuerzoModal = function() {
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

            const card = document.getElementById('guardia-card-' + userId);
            if (card) {
                const lockEl = card.querySelector('[data-lock-attendance="1"]');
                const isRefuerzo = card.textContent.includes('REFUERZO');
                const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                card.setAttribute('data-requires-confirmation', requires ? '1' : '0');
                if (!requires) {
                    card.classList.remove('ring-2','ring-rose-400','ring-emerald-400');
                    card.setAttribute('data-is-confirmed', '0');
                }
            }

            refreshAttendanceSubmitButton();
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.__draftEditable = false;

            const csrf = () => (document.querySelector('meta[name="csrf-token"]')?.content || '');

            const resetAllConfirmations = function() {
                document.querySelectorAll('[data-card-user]').forEach(card => {
                    const userId = card.getAttribute('data-card-user');
                    if (!userId) return;
                    const tokenEl = document.getElementById('confirm-token-' + userId);
                    if (tokenEl) tokenEl.value = '';
                    setConfirmState(userId, false);
                });
                refreshAttendanceSubmitButton();
            };

            const loadDraftState = async function() {
                try {
                    const res = await fetch('{{ route('draft.turno.current') }}', {
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!res.ok) return;
                    const data = await res.json().catch(() => null);
                    if (!data || !data.ok) return;

                    // Solo resetear confirmaciones cuando el draft se cargó correctamente.
                    // Esto evita perder el estado si el fetch falla (ej: luego de registrar una novedad).
                    resetAllConfirmations();

                    window.__draftEditable = !!data.editable;

                    const items = Array.isArray(data.items) ? data.items : [];

                    // Si el draft viene vacío, crear items base para todas las tarjetas visibles
                    if (items.length === 0 && window.__draftEditable) {
                        try {
                            const payloadItems = [];
                            document.querySelectorAll('[data-card-user]').forEach(card => {
                                const userId = card.getAttribute('data-card-user');
                                if (!userId) return;
                                const input = document.getElementById('attendance-status-' + userId);
                                const status = (input?.value || 'constituye').toLowerCase();
                                payloadItems.push({
                                    firefighter_id: parseInt(userId, 10),
                                    attendance_status: status,
                                });
                            });

                            if (payloadItems.length > 0) {
                                await fetch('{{ route('draft.turno.seed') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrf(),
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ items: payloadItems }),
                                });
                            }
                        } catch (e) {}

                        // Re-cargar estado ya con items creados
                        await loadDraftState();
                        return;
                    }

                    items.forEach(it => {
                        const userId = it.firefighter_id;
                        if (!userId) return;

                        // 1) Rehidratar estado (para colores/label) si viene desde el draft
                        if (it.attendance_status) {
                            const input = document.getElementById('attendance-status-' + userId);
                            if (input) {
                                input.value = String(it.attendance_status).toLowerCase();
                            }
                            updateGuardiaCardUI(userId, String(it.attendance_status).toLowerCase());
                        }

                        const tokenEl = document.getElementById('confirm-token-' + userId);
                        if (tokenEl) tokenEl.value = it.confirm_token || '';

                        // 2) Recalcular si requiere confirmación (constituye/reemplazo/refuerzo)
                        const card = document.getElementById('guardia-card-' + userId);
                        if (card) {
                            const input = document.getElementById('attendance-status-' + userId);
                            const status = (input?.value || 'constituye').toLowerCase();
                            const isRefuerzo = card.textContent.includes('REFUERZO');
                            const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                            card.setAttribute('data-requires-confirmation', requires ? '1' : '0');
                        }

                        // 3) Rehidratar confirmación solo si la tarjeta la requiere
                        const requiresConfirmation = (function() {
                            const card = document.getElementById('guardia-card-' + userId);
                            return card ? (card.getAttribute('data-requires-confirmation') === '1') : true;
                        })();

                        const confirmed = requiresConfirmation && !!(it.confirm_token && it.confirmed_at);
                        setConfirmState(userId, confirmed);
                    });

                    refreshAttendanceSubmitButton();
                } catch (e) {
                }
            };

            window.__loadDraftState = loadDraftState;
            window.__resetAllConfirmations = resetAllConfirmations;

            window.__persistDraftConfirmation = async function(userId, token) {
                if (!window.__draftEditable) return;

                try {
                    await fetch('{{ route('draft.turno.confirm') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            firefighter_id: parseInt(userId, 10),
                            confirm_token: token,
                        }),
                    });
                } catch (e) {
                }
            };

            window.__persistDraftItemStatus = async function(userId, status) {
                if (!window.__draftEditable) return;

                try {
                    await fetch('{{ route('draft.turno.item') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            firefighter_id: parseInt(userId, 10),
                            attendance_status: String(status || 'constituye').toLowerCase(),
                        }),
                    });
                } catch (e) {
                }
            };

            loadDraftState();

            window.addEventListener('pageshow', function() {
                loadDraftState();
            });

            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    setTimeout(function() {
                        loadDraftState();
                    }, 100);
                }
            });
        });

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
            if (!window.__draftEditable) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00.';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

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
            clearConfirmation(userId);
            updateGuardiaCardUI(userId, status);
            markAttendanceDirty();

            try {
                if (typeof window.__persistDraftItemStatus === 'function') {
                    window.__persistDraftItemStatus(userId, status);
                }
            } catch (e) {}
        }

        window.cycleGuardiaStatus = function(userId) {
            const input = document.getElementById('attendance-status-' + userId);
            if (!input) return;
            const current = (input.value || 'constituye').toLowerCase();

            const order = ['constituye', 'permiso', 'ausente', 'licencia', 'falta'];
            const idx = order.indexOf(current);
            const next = order[(idx === -1 ? 0 : (idx + 1) % order.length)];

            window.setGuardiaStatus(userId, next);
        }

        function refreshAttendanceSubmitButton() {
            const submitBtn = document.getElementById('guardia-attendance-submit');
            if (!submitBtn) return;

            const cards = document.querySelectorAll('[data-card-user][data-requires-confirmation="1"]');
            let hasUnconfirmed = false;
            cards.forEach(card => {
                if (card.getAttribute('data-is-confirmed') !== '1') hasUnconfirmed = true;
            });

            // Si ya se guardó y no hay cambios nuevos NI bomberos sin confirmar, bloquear el botón
            // PERO si hay bomberos sin confirmar (ej: refuerzos nuevos), habilitar aunque se haya guardado antes
            if (window.__attendanceSavedToday && !window.__attendanceDirty && !hasUnconfirmed) {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                submitBtn.classList.add('bg-slate-200','text-slate-500','border-slate-300','cursor-not-allowed');
                return;
            }

            // Si todos los que requieren confirmación están confirmados, habilitar
            if (!hasUnconfirmed) {
                submitBtn.removeAttribute('disabled');
                submitBtn.classList.remove('bg-slate-200','text-slate-500','border-slate-300','cursor-not-allowed');
                submitBtn.classList.add('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
            } else {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                submitBtn.classList.add('bg-slate-200','text-slate-500','border-slate-300','cursor-not-allowed');
            }
        }

        function setConfirmState(userId, confirmed) {
            const card = document.getElementById('guardia-card-' + userId);
            if (card) {
                card.setAttribute('data-is-confirmed', confirmed ? '1' : '0');
                card.style.borderWidth = '';
                card.style.borderColor = '';
                if (confirmed) {
                    card.style.borderWidth = '2px';
                    card.style.borderColor = '#34d399';
                }
            }

            const controls = document.getElementById('confirm-controls-' + userId);
            if (controls) {
                controls.style.display = confirmed ? 'none' : '';
            }

            const msgEl = document.getElementById('confirm-msg-' + userId);
            if (msgEl) {
                if (confirmed) {
                    msgEl.textContent = 'CONFIRMADO';
                    msgEl.classList.remove('text-rose-200','text-slate-400');
                    msgEl.classList.add('text-emerald-200');
                } else {
                    msgEl.textContent = '';
                    msgEl.classList.remove('text-emerald-200','text-rose-200');
                    msgEl.classList.add('text-slate-400');
                }
            }

            const statusEl = document.getElementById('confirm-status-' + userId);
            if (statusEl) {
                if (confirmed) {
                    statusEl.innerHTML = '<span class="flex items-center gap-1"><i class="fas fa-check-circle text-emerald-400"></i></span>';
                    statusEl.classList.remove('text-rose-200');
                    statusEl.classList.add('text-emerald-200');
                } else {
                    statusEl.textContent = 'NO CONFIRMADO';
                    statusEl.classList.remove('text-emerald-200');
                    statusEl.classList.add('text-rose-200');
                }
            }

            // Update status button to show confirmation state
            const cycleBtn = document.getElementById('status-cycle-' + userId);
            const cycleLbl = document.getElementById('status-cycle-label-' + userId);
            if (cycleBtn && cycleLbl) {
                const currentLabel = cycleLbl.textContent;
                if (confirmed) {
                    // Add checkmark to button when confirmed
                    if (!currentLabel.includes('✓')) {
                        cycleLbl.innerHTML = currentLabel + ' <i class="fas fa-check ml-1"></i>';
                    }
                    cycleBtn.classList.remove('opacity-60');
                    cycleBtn.classList.add('ring-2', 'ring-emerald-400', 'ring-offset-2', 'ring-offset-slate-900');
                } else {
                    // Remove checkmark when not confirmed
                    cycleLbl.textContent = currentLabel.replace(' ✓', '').replace(' <i class="fas fa-check ml-1"></i>', '');
                    cycleBtn.classList.remove('ring-2', 'ring-emerald-400', 'ring-offset-2', 'ring-offset-slate-900');
                }
            }
        }

        function clearConfirmation(userId) {
            const tokenEl = document.getElementById('confirm-token-' + userId);
            if (tokenEl) tokenEl.value = '';

            try {
                const input = document.getElementById('attendance-status-' + userId);
                const current = (input?.value || 'constituye').toLowerCase();
                if (typeof window.__persistDraftItemStatus === 'function') {
                    window.__persistDraftItemStatus(userId, current);
                }
            } catch (e) {}

            const msgEl = document.getElementById('confirm-msg-' + userId);
            if (msgEl) {
                msgEl.textContent = '';
                msgEl.classList.remove('text-emerald-200','text-rose-200');
                msgEl.classList.add('text-slate-400');
            }
            setConfirmState(userId, false);
            refreshAttendanceSubmitButton();
        }

        window.confirmBombero = async function(guardiaId, bomberoId) {
            const codeEl = document.getElementById('confirm-code-' + bomberoId);
            const tokenEl = document.getElementById('confirm-token-' + bomberoId);
            const btnEl = document.getElementById('confirm-btn-' + bomberoId);

            const numeroRegistro = (codeEl?.value || '').trim();
            if (!numeroRegistro) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'INGRESA EL CÓDIGO DEL BOMBERO';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

            if (!window.__draftEditable) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00.';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

            try {
                if (btnEl) {
                    btnEl.setAttribute('disabled', 'disabled');
                    btnEl.classList.add('opacity-60','cursor-not-allowed');
                }

                const res = await fetch(`/admin/guardias/${guardiaId}/bomberos/${bomberoId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ numero_registro: numeroRegistro }),
                });

                if (res.status === 401) {
                    window.location.reload();
                    return;
                }

                let data = null;
                const raw = await res.text().catch(() => '');
                try {
                    data = raw ? JSON.parse(raw) : null;
                } catch (e) {
                    data = null;
                }

                if (!res.ok || !data || !data.ok) {
                    const errMsg = (data && (data.message || data.error)) ? (data.message || data.error) : 'NO SE PUDO CONFIRMAR';
                    const toast = document.getElementById('confirm-error-toast');
                    const toastText = document.getElementById('confirm-error-text');
                    if (toast && toastText) {
                        toastText.textContent = String(errMsg).toUpperCase();
                        toast.classList.remove('hidden');
                        toast.classList.add('flex');
                    }
                    clearConfirmation(bomberoId);
                    return;
                }

                if (tokenEl) tokenEl.value = data.token || '';
                setConfirmState(bomberoId, true);

                try {
                    if (data.token && typeof window.__persistDraftConfirmation === 'function') {
                        await window.__persistDraftConfirmation(bomberoId, data.token);
                    }
                } catch (e) {}

                refreshAttendanceSubmitButton();
            } catch (e) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'ERROR AL CONFIRMAR';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                clearConfirmation(bomberoId);
            } finally {
                if (btnEl) {
                    btnEl.removeAttribute('disabled');
                    btnEl.classList.remove('opacity-60','cursor-not-allowed');
                }
            }
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

            const card = document.getElementById('guardia-card-' + userId);
            const lockEl = card ? card.querySelector('[data-lock-attendance="1"]') : null;

            const cycleBtn = document.getElementById('status-cycle-' + userId);
            const cycleLbl = document.getElementById('status-cycle-label-' + userId);
            if (cycleBtn && cycleLbl) {
                const labelMap = {
                    constituye: 'CONSTITUYE',
                    permiso: 'PERMISO',
                    ausente: 'AUSENTE',
                    licencia: 'LICENCIA',
                    falta: 'FALTA',
                };
                const themeRemove = [
                    'bg-emerald-500/80','border-emerald-400/50',
                    'bg-amber-500/80','border-amber-400/50',
                    'bg-slate-400/30','border-slate-500/30',
                    'bg-blue-600/80','border-blue-400/50',
                    'bg-red-600/80','border-red-400/50'
                ];
                cycleBtn.classList.remove(...themeRemove);

                const s = (status || 'constituye').toLowerCase();
                cycleLbl.textContent = labelMap[s] || 'CONSTITUYE';
                if (s === 'constituye') cycleBtn.classList.add('bg-emerald-500/80','border-emerald-400/50');
                else if (s === 'permiso') cycleBtn.classList.add('bg-amber-500/80','border-amber-400/50');
                else if (s === 'ausente') cycleBtn.classList.add('bg-slate-400/30','border-slate-500/30');
                else if (s === 'licencia') cycleBtn.classList.add('bg-blue-600/80','border-blue-400/50');
                else if (s === 'falta') cycleBtn.classList.add('bg-red-600/80','border-red-400/50');

                if (lockEl) {
                    lockEl.style.display = (status === 'constituye' || status === 'reemplazo') ? 'none' : '';
                }
            }

            if (card) {
                const isRefuerzo = card.textContent.includes('REFUERZO');
                const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                card.setAttribute('data-requires-confirmation', requires ? '1' : '0');

                // Show/hide confirm box based on status
                const confirmWrap = document.getElementById('confirm-box-wrap-' + userId);
                if (confirmWrap) {
                    confirmWrap.classList.toggle('hidden', !requires);
                }

                if (!requires) {
                    card.setAttribute('data-is-confirmed', '0');
                    setConfirmState(userId, false);
                } else if (card.getAttribute('data-is-confirmed') === '1') {
                    setConfirmState(userId, true);
                } else {
                    setConfirmState(userId, false);
                }
            }

            refreshAttendanceSubmitButton();
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

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.bindAttendanceFormHandlers === 'function') {
                    window.bindAttendanceFormHandlers();
                }
            });

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

                    if (typeof window.bindAttendanceFormHandlers === 'function') {
                        window.bindAttendanceFormHandlers();
                    }

                    if (typeof window.__loadDraftState === 'function') {
                        await window.__loadDraftState();
                    }

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
                    });
                    if (!res.ok) return;

                    const data = await res.json();
                    if (!data || !data.ok) return;

                    const prev = window.__guardiaSnapshot || {};
                    const rosterChanged = (
                        (data.latest_bombero_at && data.latest_bombero_at !== prev.latest_bombero_at) ||
                        (data.latest_replacement_at && data.latest_replacement_at !== prev.latest_replacement_at)
                    );
                    const changed = (
                        (data.latest_novelty_at && data.latest_novelty_at !== prev.latest_novelty_at) ||
                        (data.latest_bombero_at && data.latest_bombero_at !== prev.latest_bombero_at) ||
                        (data.latest_replacement_at && data.latest_replacement_at !== prev.latest_replacement_at) ||
                        (data.attendance_saved_at && data.attendance_saved_at !== prev.attendance_saved_at) ||
                        (data.latest_draft_at && data.latest_draft_at !== prev.latest_draft_at)
                    );

                    if (changed) {
                        await softRefreshGuardiaDashboard();

                        // Si cambió la dotación (refuerzos/reemplazos), permitir guardar nuevamente
                        if (rosterChanged) {
                            markAttendanceDirty();
                        }

                        window.__guardiaSnapshot = {
                            latest_novelty_at: data.latest_novelty_at,
                            latest_bombero_at: data.latest_bombero_at,
                            latest_replacement_at: data.latest_replacement_at,
                            attendance_saved_at: data.attendance_saved_at,
                            latest_draft_at: data.latest_draft_at,
                        };
                        return;
                    }

                    window.__guardiaSnapshot = {
                        latest_novelty_at: data.latest_novelty_at,
                        latest_bombero_at: data.latest_bombero_at,
                        latest_replacement_at: data.latest_replacement_at,
                        attendance_saved_at: data.attendance_saved_at,
                        latest_draft_at: data.latest_draft_at,
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
                            <!-- Custom Professional Dropdown -->
                            <div class="relative" id="replacement-select-container">
                                <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_firefighter_id" required>
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="replacement-search-input"
                                        class="w-full text-sm border-slate-800 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 pl-9 pr-10 py-2.5 bg-slate-950 text-slate-100 placeholder:text-slate-500 cursor-pointer"
                                        placeholder="Buscar voluntario..." autocomplete="off" readonly>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                                
                                <!-- Dropdown Menu -->
                                <div id="replacement-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50 max-h-64 overflow-y-auto">
                                    <div class="p-2 sticky top-0 bg-slate-900 border-b border-slate-800">
                                        <input type="text" id="replacement-filter-input" 
                                            class="w-full text-xs bg-slate-800 border-slate-700 rounded px-2 py-1.5 text-slate-200 placeholder:text-slate-500 focus:outline-none focus:border-blue-500"
                                            placeholder="Filtrar por nombre o RUT...">
                                    </div>
                                    <div id="replacement-options-list" class="py-1">
                                        @foreach($replacementCandidates as $cand)
                                            <div class="replacement-option px-3 py-2 hover:bg-slate-800 cursor-pointer transition-colors flex items-center gap-3"
                                                 data-value="{{ $cand->id }}"
                                                 data-search="{{ strtolower(trim($cand->nombres . ' ' . $cand->apellido_paterno . ' ' . ($cand->apellido_materno ?? '') . ' ' . ($cand->rut ?? ''))) }}">
                                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 text-xs font-bold">
                                                    {{ strtoupper(substr($cand->nombres, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-slate-200 truncate">
                                                        {{ trim($cand->nombres . ' ' . $cand->apellido_paterno) }}
                                                    </div>
                                                    @if($cand->rut)
                                                        <div class="text-xs text-slate-500">{{ $cand->rut }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="replacement-no-results" class="hidden px-3 py-4 text-center text-xs text-slate-500">
                                        No se encontraron resultados
                                    </div>
                                </div>
                            </div>
                        </div>

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

        // Custom Replacement Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('replacement-search-input');
            const dropdown = document.getElementById('replacement-dropdown');
            const filterInput = document.getElementById('replacement-filter-input');
            const optionsList = document.getElementById('replacement-options-list');
            const noResults = document.getElementById('replacement-no-results');
            const hiddenInput = document.getElementById('modal_replacement_firefighter_id');
            const container = document.getElementById('replacement-select-container');
            
            if (!searchInput || !dropdown) return;

            let isOpen = false;

            // Toggle dropdown
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!isOpen) {
                    openDropdown();
                }
            });

            function openDropdown() {
                isOpen = true;
                dropdown.classList.remove('hidden');
                filterInput.focus();
                filterInput.value = '';
                filterOptions('');
            }

            function closeDropdown() {
                isOpen = false;
                dropdown.classList.add('hidden');
            }

            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Filter functionality
            if (filterInput) {
                filterInput.addEventListener('input', function() {
                    filterOptions(this.value.toLowerCase());
                });
            }

            function filterOptions(query) {
                const options = optionsList.querySelectorAll('.replacement-option');
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchData = option.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            // Option selection
            optionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.replacement-option');
                if (!option) return;

                const value = option.getAttribute('data-value');
                const text = option.querySelector('.text-sm').textContent.trim();

                hiddenInput.value = value;
                searchInput.value = text;
                closeDropdown();
            });

            // Keyboard navigation
            filterInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    searchInput.focus();
                }
            });
        });

        // Custom Refuerzo Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('refuerzo-search-input');
            const dropdown = document.getElementById('refuerzo-dropdown');
            const filterInput = document.getElementById('refuerzo-filter-input');
            const optionsList = document.getElementById('refuerzo-options-list');
            const noResults = document.getElementById('refuerzo-no-results');
            const hiddenInput = document.getElementById('refuerzo_firefighter_id');
            const container = document.getElementById('refuerzo-select-container');
            
            if (!searchInput || !dropdown) return;

            let isOpen = false;

            // Toggle dropdown
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!isOpen) {
                    openDropdown();
                }
            });

            function openDropdown() {
                isOpen = true;
                dropdown.classList.remove('hidden');
                filterInput.focus();
                filterInput.value = '';
                filterOptions('');
            }

            function closeDropdown() {
                isOpen = false;
                dropdown.classList.add('hidden');
            }

            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Filter functionality
            if (filterInput) {
                filterInput.addEventListener('input', function() {
                    filterOptions(this.value.toLowerCase());
                });
            }

            function filterOptions(query) {
                const options = optionsList.querySelectorAll('.refuerzo-option');
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchData = option.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            // Option selection
            optionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.refuerzo-option');
                if (!option) return;

                const value = option.getAttribute('data-value');
                const text = option.querySelector('.text-sm').textContent.trim();

                hiddenInput.value = value;
                searchInput.value = text;
                closeDropdown();
            });

            // Keyboard navigation
            filterInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    searchInput.focus();
                }
            });

            // Form validation
            const refuerzoForm = searchInput.closest('form');
            if (refuerzoForm) {
                refuerzoForm.addEventListener('submit', (e) => {
                    if ((hiddenInput.value || '').trim() === '') {
                        e.preventDefault();
                        alert('Debes seleccionar un voluntario.');
                    }
                });
            }
        });
    </script>
@endsection
