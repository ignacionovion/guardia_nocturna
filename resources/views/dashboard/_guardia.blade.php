    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        @php
            $guardiaTz = \App\Models\SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
            $guardiaDailyEndTime = \App\Models\SystemSetting::getValue('guardia_daily_end_time', '07:00');

            $localNow = now()->copy()->setTimezone($guardiaTz);
            [$endH, $endM] = array_map('intval', explode(':', (string) $guardiaDailyEndTime));
            $dailyEndAt = $localNow->copy()->setTime($endH, $endM, 0);
            $shiftClosedForToday = $localNow->greaterThanOrEqualTo($dailyEndAt);

            // Ventana desde panel: attendance_enable_time -> attendance_disable_time
            $attendanceEnabled = (function () use ($guardiaTz) {
                $enableTime  = \App\Models\SystemSetting::getValue('attendance_enable_time', '22:00');
                $disableTime = \App\Models\SystemSetting::getValue('attendance_disable_time', '07:00');
                [$eH, $eM] = array_map('intval', explode(':', (string) $enableTime));
                [$dH, $dM] = array_map('intval', explode(':', (string) $disableTime));
                $localNow    = now()->copy()->setTimezone($guardiaTz);
                $nowMins     = $localNow->hour * 60 + $localNow->minute;
                $enableMins  = $eH * 60 + $eM;
                $disableMins = $dH * 60 + $dM;
                if ($enableMins > $disableMins) {
                    return $nowMins >= $enableMins || $nowMins < $disableMins;
                }
                return $nowMins >= $enableMins && $nowMins < $disableMins;
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

            $visibleStaffCount = $activeStaff->count();
            $presentStaffCount = $onDutyStaff->count();

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
        <!-- VISTA ESPECÍFICA PARA CUENTA DE GUARDIA -->
        <div id="guardia-dashboard-root" class="w-full min-h-screen px-4 md:px-6 lg:px-8 py-4 pt-[calc(env(safe-area-inset-top)+1.25rem)] bg-slate-950 text-slate-100">
            <div class="sticky top-0 z-40 flex flex-col md:flex-row md:items-center md:justify-between mb-5 gap-4 border-b border-slate-800 pb-4 bg-slate-950">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="icon-box icon-box-gradient-red icon-box-md">
                        <i class="fas fa-gauge-high"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-label">Panel de Control</div>
                        <div class="mt-0.5 flex items-center gap-3 min-w-0">
                            <div class="text-title-md text-white uppercase truncate">{{ $myGuardia->name }}</div>
                            @if(isset($isMyGuardiaOnDuty) && $isMyGuardiaOnDuty)
                                <x-ui.badge variant="success" size="sm">SEMANA DE GUARDIA</x-ui.badge>
                            @else
                                <x-ui.badge variant="default" size="sm">FUERA DE TURNO</x-ui.badge>
                            @endif
                        </div>
                        <div class="mt-0.5 text-body-sm text-slate-400">{{ $visibleStaffCount }} en pantalla | {{ $presentStaffCount }} presentes</div>
                    </div>
                </div>

                <div id="attendance-stale-banner" class="hidden fixed inset-0 z-[55] flex items-center justify-center">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAttendanceStaleBanner()"></div>
                    <div class="relative w-full max-w-lg mx-4 p-6 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 shadow-2xl">
                        <button onclick="closeAttendanceStaleBanner()" class="absolute top-3 right-3 w-8 h-8 rounded-lg icon-box icon-box-amber icon-box-sm">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="flex items-start gap-4">
                            <div class="icon-box icon-box-amber icon-box-md shrink-0">
                                <i class="fas fa-triangle-exclamation text-xl"></i>
                            </div>
                            <div class="min-w-0 pt-1">
                                <div class="text-title-sm text-amber-900 dark:text-amber-200 mb-2">Asistencia desactualizada</div>
                                <div class="text-body text-amber-800 dark:text-amber-300">Se detectaron cambios después de guardar la asistencia. Debes presionar <span class="font-semibold text-amber-700 dark:text-amber-200">Guardar Asistencia</span> nuevamente para confirmar.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:flex-1 flex items-center justify-start md:justify-center">
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="secondary" size="sm" onclick="toggleFullscreen()" class="!w-10 !h-10 !p-0" title="Pantalla completa">
                            <i class="fas fa-expand text-sm"></i>
                        </x-ui.button>
                        <x-ui.button variant="secondary" size="sm" href="{{ route('guardia.aseo') }}" class="!w-10 !h-10 !p-0" title="Asignación de Aseo">
                            <i class="fas fa-broom text-sm text-red-400"></i>
                        </x-ui.button>
                        <x-ui.button variant="secondary" size="sm" onclick="openCalendarPopup()" class="!w-10 !h-10 !p-0" title="Calendario de Guardias">
                            <i class="fas fa-calendar-days text-sm text-emerald-400"></i>
                        </x-ui.button>
                        @if(feature('emergencias'))
                        <x-ui.button variant="secondary" size="sm" href="{{ route('admin.emergencies.index') }}" class="!w-10 !h-10 !p-0" title="Emergencias">
                            <i class="fas fa-truck-medical text-sm text-amber-400"></i>
                        </x-ui.button>
                        @endif
                        <x-ui.button variant="secondary" size="sm" onclick="openRefuerzoModal()" class="!w-10 !h-10 !p-0" title="Refuerzo">
                            <i class="fas fa-user-plus text-sm text-sky-400"></i>
                        </x-ui.button>
                        <button id="guardia-attendance-submit" form="guardia-attendance-form" type="submit" @if(!$attendanceEnabled) disabled @endif class="w-10 h-10 rounded-xl flex items-center justify-center transition-all border {{ $attendanceEnabled ? 'bg-slate-800 hover:bg-slate-700 text-white border-slate-700' : 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed' }}">
                            <i class="fas fa-floppy-disk text-sm {{ $attendanceEnabled ? 'text-emerald-400' : 'text-slate-500' }}"></i>
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
                        $isAttendanceWindowOpen = $attendanceEnabled;
                        
                        // Determinar mensaje y variante del badge
                        $attendanceMessage = '';
                        $attendanceVariant = 'default';
                        
                        if (!$isAttendanceWindowOpen) {
                            $attendanceMessage = 'FUERA DE HORARIO DE REGISTRO';
                            $attendanceVariant = 'default';
                        } elseif (isset($hasAttendanceSavedToday) && $hasAttendanceSavedToday && !empty($attendanceIsStale)) {
                            $attendanceMessage = 'ASISTENCIA DESACTUALIZADA';
                            $attendanceVariant = 'warning';
                        } elseif (isset($hasAttendanceSavedToday) && $hasAttendanceSavedToday) {
                            $attendanceMessage = 'ASISTENCIA REGISTRADA';
                            $attendanceVariant = 'success';
                        } elseif ($shiftClosedForToday) {
                            $attendanceMessage = 'RECORDAR REGISTRO 22:00';
                            $attendanceVariant = 'warning';
                        } else {
                            $attendanceVariant = 'danger';
                            $attendanceMessage = $isAfter2200 ? 'GUARDAR ANTES DE IRTE' : 'SIN REGISTRAR';
                        }
                    @endphp
                    <x-ui.badge id="attendance-saved-badge" :variant="$attendanceVariant" size="sm">{{ $attendanceMessage }}</x-ui.badge>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button variant="secondary" size="sm" type="submit" class="!h-10" title="Cerrar sesión">
                            <i class="fas fa-right-from-bracket text-sm text-rose-400"></i>
                            <span class="hidden sm:inline">Salir</span>
                        </x-ui.button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-4">
                <x-ui.card class="!bg-slate-900 !border-slate-800">
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
                                        'ausente' => 'bg-slate-50 dark:bg-slate-8000',
                                        'licencia' => 'bg-blue-400',
                                        'falta' => 'bg-red-400',
                                        default => 'bg-slate-50 dark:bg-slate-8000',
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

                                <div id="guardia-card-{{ $staff->id }}" class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col h-full" data-card-user="{{ $staff->id }}" data-requires-confirmation="{{ (in_array($status, ['constituye','reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement) ? '1' : '0' }}" data-is-confirmed="0">
                                    <div id="card-header-{{ $staff->id }}" class="{{ $statusHeaderClass }} text-white px-2 py-1.5 flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-white leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
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

                                    {{-- FOTO ancho completo con info overlay --}}
                                    <div class="relative bg-slate-950 rounded-xl border border-slate-800 overflow-hidden w-full h-[200px] mb-2 shrink-0">
                                        @if($staff->photo_path)
                                            <img src="{{ url('media/' . ltrim($staff->photo_path, '/')) }}" class="w-full h-full object-cover object-center scale-100" alt="Foto">
                                        @else
                                            <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-200 font-black text-3xl">
                                                {{ strtoupper(substr($staff->nombres, 0, 1) . substr($staff->apellido_paterno, 0, 1)) }}
                                            </div>
                                        @endif

                                        {{-- Gradient overlay para legibilidad del texto --}}
                                        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/90 via-black/60 to-transparent"></div>

                                        {{-- INFO superpuesta en la foto --}}
                                        @php
                                            $ingreso = $staff->fecha_ingreso ? \Carbon\Carbon::parse($staff->fecha_ingreso) : null;
                                            $diff = $ingreso ? $ingreso->diff(now()) : null;
                                            $serviceYears = $diff ? (int) $diff->y : 0;
                                            $serviceMonths = $diff ? (int) $diff->m : 0;
                                            $yearsLabel = $serviceYears . ' ' . ($serviceYears === 1 ? 'año' : 'años');
                                            $monthsLabel = $serviceMonths . ' ' . ($serviceMonths === 1 ? 'm' : 'm');
                                            $serviceLabel = $diff ? trim($yearsLabel . ' ' . $monthsLabel) : '—';
                                        @endphp
                                        <div class="absolute inset-x-0 bottom-0 p-2">
                                            <div class="text-xs font-semibold text-white leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
                                                {{ $staff->nombres }} {{ $staff->apellido_paterno }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                <span class="text-[10px] font-medium text-white/90 uppercase tracking-wider">
                                                    {{ $staff->cargo_texto ?: ($staff->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero') }}
                                                </span>
                                                @if($staff->es_permanente)
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-emerald-300 bg-emerald-500/30 border border-emerald-400/30 rounded px-1 py-0 leading-none">PERM</span>
                                                @endif
                                                @if($staff->es_refuerzo)
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-emerald-300 bg-emerald-500/30 border border-emerald-400/30 rounded px-1 py-0 leading-none">REF</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[10px] font-medium text-white/70 uppercase tracking-wider">{{ $serviceLabel }}</span>
                                                <span class="text-white/40 text-[10px]">·</span>
                                                <span class="text-[10px] font-medium text-white/70 uppercase tracking-wider">{{ $staff->numero_portatil ?: '—' }}</span>
                                            </div>
                                        </div>

                                        {{-- Badge cama arriba izquierda --}}
                                        @php $bedNum = isset($bedByFirefighter) ? ($bedByFirefighter[$staff->id] ?? null) : null; @endphp
                                        @if($bedNum !== null)
                                            <div class="absolute top-1 left-1 bg-slate-900/80 backdrop-blur-sm border border-slate-600 rounded-md px-1.5 py-0.5 text-[9px] font-black text-slate-100 leading-none whitespace-nowrap">
                                                🛏 #{{ $bedNum }}
                                            </div>
                                        @endif

                                        {{-- Badges especialidades derecha, apilados --}}
                                        <div class="absolute top-1 right-1 flex flex-col gap-1">
                                            @if($staff->es_conductor)
                                                <span class="w-5 h-5 rounded-full bg-sky-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-sky-300 shadow-sm" title="Conductor">
                                                    <i class="fas fa-car text-[9px]"></i>
                                                </span>
                                            @endif
                                            @if($staff->es_operador_rescate)
                                                <span class="w-5 h-5 rounded-full bg-amber-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-amber-300 shadow-sm" title="Operador de Rescate">R</span>
                                            @endif
                                            @if($staff->es_asistente_trauma)
                                                <span class="w-5 h-5 rounded-full bg-rose-400 text-slate-900 flex items-center justify-center text-[9px] font-bold border border-rose-300 shadow-sm px-0.5" title="Asistente de Trauma">AT</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($repAsReplacement)
                                        <div class="mt-2 rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-2 text-purple-800">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-purple-700">Reemplaza a</div>
                                            <div class="text-sm font-medium text-slate-800">
                                                {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsReplacement->originalFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsReplacement->id) }}')" class="w-full bg-white dark:bg-slate-900 hover:bg-purple-50 text-purple-700 font-medium uppercase text-[10px] py-1.5 rounded-lg border border-purple-200">
                                                    Deshacer reemplazo
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if($repAsOriginal)
                                        <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-2 text-amber-800">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Reemplazado por</div>
                                            <div class="text-sm font-medium text-slate-800">
                                                {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->nombres ?? '')))[0] ?? '' }} {{ explode(' ', trim((string) ($repAsOriginal->replacementFirefighter?->apellido_paterno ?? '')))[0] ?? '' }}
                                            </div>
                                            <div class="mt-1">
                                                <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $repAsOriginal->id) }}')" class="w-full bg-white dark:bg-slate-900 hover:bg-amber-100 text-amber-800 font-medium uppercase text-[10px] py-1.5 rounded-lg border border-amber-200">
                                                    Deshacer reemplazo
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Confirmación - SIEMPRE visible cuando aplica --}}
                                    <div class="mt-1.5">
                                        <div id="confirm-box-wrap-{{ $staff->id }}" class="{{ (in_array($status, ['constituye','reemplazo'], true) || $staff->es_refuerzo || $repAsReplacement) ? '' : 'hidden' }}">
                                            <div id="confirm-box-{{ $staff->id }}" class="mb-2 rounded-xl border border-slate-800 bg-slate-950 px-2.5 py-2">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div id="confirm-status-{{ $staff->id }}" class="text-[9px] font-black uppercase tracking-widest text-rose-200">NO CONFIRMADO</div>
                                                </div>
                                                <div id="confirm-controls-{{ $staff->id }}" class="mt-1.5 flex items-center gap-2">
                                                    <input type="password" inputmode="numeric" autocomplete="one-time-code" id="confirm-code-{{ $staff->id }}" placeholder="Código" class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-900 text-[10px] font-black uppercase tracking-widest text-slate-100 placeholder:text-slate-600 dark:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20" onkeydown="if(event.key==='Enter'){event.preventDefault();confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }});}" />
                                                    <button type="button" id="confirm-btn-{{ $staff->id }}" onclick="confirmBombero({{ (int) $myGuardia->id }}, {{ (int) $staff->id }})" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-100 text-[9px] font-black uppercase tracking-widest border border-slate-700">Confirmar</button>
                                                </div>
                                                <div id="confirm-msg-{{ $staff->id }}" class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Estado - OCULTO para reemplazos --}}
                                    <div class="mt-1.5 {{ $repAsReplacement ? 'hidden' : '' }}">
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

                                    <div class="mt-1.5 flex flex-col gap-1.5">
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
                                            <button type="button" onclick="removeRefuerzo('{{ $myGuardia->id }}', '{{ $staff->id }}')" class="w-full bg-slate-950 hover:bg-slate-900 text-slate-100 font-black uppercase tracking-widest text-[10px] py-1.5 rounded-lg border border-slate-800">
                                                Quitar refuerzo
                                            </button>
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
                                    <div class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Inhabilitados</div>
                                    <div class="text-[11px] font-bold text-slate-400">{{ $outOfServiceStaff->count() }}</div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
                                    @foreach($outOfServiceStaff as $staff)
                                        <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col p-3">
                                            <div class="flex items-center justify-between">
                                                <div class="text-[10px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $staff->cargo_texto ?? 'Bombero' }}</div>
                                                <div class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-100">INHABILITADO</div>
                                            </div>
                                            <div class="mt-2 text-sm font-semibold text-slate-100 leading-tight" title="{{ $staff->nombres }} {{ $staff->apellido_paterno }}">
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
                    <x-ui.card class="!bg-slate-900 !border-slate-800">
                        <x-slot:header>
                            <div class="flex items-center justify-between w-full">
                                <div class="text-label">Hora Local</div>
                                <x-ui.badge variant="success" size="xs">EN LÍNEA</x-ui.badge>
                            </div>
                        </x-slot:header>
                        <div class="text-center">
                            @if(!$attendanceEnabled)
                                <x-ui.badge variant="warning" size="xs" class="mb-3">HABILITADO {{ $attendanceEnableTime }} - {{ $attendanceDisableTime }}</x-ui.badge>
                            @endif
                            <div class="bg-slate-950 border-2 border-slate-800 rounded-xl py-4 px-6 flex items-center justify-center">
                                <span id="digital-clock" class="text-2xl md:text-3xl font-mono font-bold tracking-widest text-white">--:--:--</span>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="!bg-slate-900 !border-slate-800">
                        <x-slot:header>
                            <div class="flex items-center justify-between w-full">
                                <div class="text-label">Próximos Cumpleaños</div>
                                <div class="text-caption">{{ mb_strtoupper(\Carbon\Carbon::now()->locale('es')->translatedFormat('F'), 'UTF-8') }}</div>
                            </div>
                        </x-slot:header>
                        @php
                            $birthdaysList = $birthdaysThisMonth ?? $birthdays;
                        @endphp
                        @if($birthdaysList->isEmpty())
                            <div class="text-body text-slate-400">Sin cumpleaños este mes.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($birthdaysList->take(5) as $user)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-body font-semibold text-white truncate">{{ $user->nombres }} {{ $user->apellido_paterno }}</div>
                                            <div class="text-caption">Bombero</div>
                                        </div>
                                        <div class="text-body font-semibold text-slate-400">
                                            {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card>

                    <x-ui.card class="!bg-slate-900 !border-slate-800">
                        <x-slot:header>
                            <div class="flex items-center justify-between w-full">
                                <div class="text-label">Bitácora de Novedades</div>
                                <button onclick="openNoveltyModal()" class="text-xs font-semibold text-blue-400 hover:text-blue-300 uppercase tracking-wider">Registrar</button>
                            </div>
                        </x-slot:header>
                        @php
                            $guardiaNoveltiesList = $guardiaNovelties ?? $novelties;
                        @endphp
                        @if($guardiaNoveltiesList->isEmpty())
                            <div class="text-body text-slate-400">Sin novedades recientes.</div>
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
                                                <span class="text-xs font-bold {{ $colors['text'] }} uppercase tracking-wider">{{ $novelty->type }}</span>
                                                @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                                                    <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">PERMANENTE</span>
                                                @endif
                                            </div>
                                            <div class="text-sm font-black text-slate-100">{{ $novelty->title }}</div>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $novelty->description }}</div>
                                            <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-2">
                                                {{ $novelty->created_at->locale('es')->diffForHumans() }}
                                                @if($novelty->user)
                                                    <span class="text-slate-600 dark:text-slate-400">|</span>
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
                                    
                                    {{-- Dark theme pagination styling --}}
                                    <style>
                                        .dark-pagination nav {
                                            background: transparent;
                                        }
                                        .dark-pagination nav a,
                                        .dark-pagination nav span {
                                            background-color: #1e293b !important;
                                            color: #94a3b8 !important;
                                            border-color: #334155 !important;
                                        }
                                        .dark-pagination nav a:hover {
                                            background-color: #334155 !important;
                                            color: #e2e8f0 !important;
                                        }
                                        .dark-pagination nav span.relative.z-10 {
                                            background-color: #3b82f6 !important;
                                            color: white !important;
                                            border-color: #3b82f6 !important;
                                        }
                                    </style>
                                    <script>
                                        // Add dark-pagination class to the parent of the nav element
                                        document.addEventListener('DOMContentLoaded', function() {
                                            var nav = document.querySelector('.mt-4 nav');
                                            if (nav) {
                                                nav.parentElement.classList.add('dark-pagination');
                                            }
                                        });
                                    </script>
                                @endif
                        @endif
                    </x-ui.card>

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
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                    <i class="fas fa-clock text-[9px]"></i>
                                                    {{ ($academy->date ?? $academy->created_at)?->format('H:i') }}
                                                </span>
                                                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                                    {{ ($academy->date ?? $academy->created_at)?->locale('es')->diffForHumans() }}
                                                </span>
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
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="sendBedReportEmail()" class="text-xs font-black text-emerald-400 hover:text-emerald-300 uppercase tracking-widest flex items-center gap-1" title="Enviar reporte por email">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <a href="{{ route('camas') }}" class="text-xs font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest">Ver</a>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="text-4xl font-black text-slate-100">{{ $availableBeds }}<span class="text-lg text-slate-400 font-black">/{{ $totalBeds }}</span></div>
                            <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Disponibles</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

