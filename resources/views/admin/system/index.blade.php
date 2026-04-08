@extends('layouts.modern')

@section('content')
    <div class="max-w-7xl mx-auto">
        <x-ui.page-header title="Administración del Sistema" subtitle="Panel de configuración y operaciones del sistema" icon="fas fa-shield-halved" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('dashboard') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="space-y-6">
            {{-- SECCIÓN 1: SISTEMA --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-blue-50 to-slate-50 dark:from-slate-800 dark:to-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Sistema</h2>
                            <p class="text-sm text-gray-500 dark:text-slate-400">Horarios y automatizaciones del sistema</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.system.schedule.save') }}" class="p-6 bg-white dark:bg-slate-800/50">
                    @csrf
                    <div class="grid grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label">Habilitar Guardar Asistencia</label>
                            <input type="time" name="attendance_enable_time" value="{{ old('attendance_enable_time', ($settings['attendance_enable_time'] ?? '22:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deshabilitar Guardar Asistencia</label>
                            <input type="time" name="attendance_disable_time" value="{{ old('attendance_disable_time', ($settings['attendance_disable_time'] ?? '07:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Constitución diaria (Lun-Sáb)</label>
                            <input type="time" name="guardia_constitution_weekday_time" value="{{ old('guardia_constitution_weekday_time', ($settings['guardia_constitution_weekday_time'] ?? '23:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Constitución domingo</label>
                            <input type="time" name="guardia_constitution_sunday_time" value="{{ old('guardia_constitution_sunday_time', ($settings['guardia_constitution_sunday_time'] ?? '22:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Salida diaria del turno</label>
                            <input type="time" name="guardia_daily_end_time" value="{{ old('guardia_daily_end_time', ($settings['guardia_daily_end_time'] ?? '07:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cambio guardia en turno (Semanal)</label>
                            <input type="time" name="guardia_week_transition_time" value="{{ old('guardia_week_transition_time', ($settings['guardia_week_transition_time'] ?? '18:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Limpieza semanal (Domingo)</label>
                            <input type="time" name="guardia_week_cleanup_time" value="{{ old('guardia_week_cleanup_time', ($settings['guardia_week_cleanup_time'] ?? '18:00')) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Zona horaria scheduler</label>
                            <input type="text" name="guardia_schedule_tz" value="{{ old('guardia_schedule_tz', ($settings['guardia_schedule_tz'] ?? config('app.timezone'))) }}" class="form-input" placeholder="America/Santiago" required>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                            Guardar Horarios
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- SECCIÓN 3: OPERACIÓN --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border-2 border-orange-300 dark:border-orange-700/50 overflow-hidden">
                <div class="px-6 py-5 border-b border-orange-200 dark:border-orange-700/30 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Operación</h2>
                            <p class="text-sm text-gray-500 dark:text-slate-400">Reset y limpieza de datos del sistema</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.system.purge') }}" class="p-6 bg-orange-50/30 dark:bg-slate-800/50">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Acción</label>
                            <select name="action" class="form-select" required>
                                <option value="" disabled selected>Seleccionar...</option>
                                <option value="novelties">Eliminar Novedades + Academias</option>
                                <option value="shifts">Reiniciar Turnos (Shift + ShiftUsers)</option>
                                <option value="attendance_records">Reiniciar Estado "Asistencia Registrada"</option>
                                <option value="emergencies">Eliminar Emergencias</option>
                                <option value="cleaning">Eliminar Asignaciones de Aseo</option>
                                <option value="staff_events">Eliminar Eventos de Personal</option>
                                <option value="all">VACIAR TODO (NO RECOMENDADO)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmación</label>
                            <input type="text" name="confirm_text" class="form-input" placeholder="Escribe CONFIRMAR" required>
                        </div>
                    </div>

                    <div class="mt-5 bg-orange-50 dark:bg-orange-900/20 border border-orange-300 dark:border-orange-700/50 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-orange-600 dark:text-orange-400 text-xl mt-0.5"></i>
                            <div class="text-sm text-orange-900 dark:text-orange-200 font-medium">
                                Estas acciones pueden eliminar información de forma permanente.
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-ui.button type="submit" variant="danger" size="md" icon="fas fa-trash" onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.');">
                            Ejecutar
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- SECCIÓN 2: NOTIFICACIONES --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-purple-50 to-slate-50 dark:from-slate-800 dark:to-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Notificaciones</h2>
                            <p class="text-sm text-gray-500 dark:text-slate-400">Configuración de correos automáticos</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.system.mail.save') }}" class="p-6 bg-white dark:bg-slate-800/50">
                    @csrf
                    
                    {{-- Bloque 1: Remitente --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Remitente</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Correo remitente</label>
                                <input type="email" name="mail_from_address" value="{{ old('mail_from_address', ($settings['mail_from_address'] ?? 'no-responder@dev-app.cl')) }}" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nombre remitente</label>
                                <input type="text" name="mail_from_name" value="{{ old('mail_from_name', ($settings['mail_from_name'] ?? config('app.name', 'AppGuardia'))) }}" class="form-input" required>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 2: Destinatarios --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Destinatarios</h3>
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="form-label">Destinatarios (separados por coma)</label>
                                <textarea name="mail_recipients" class="form-textarea" placeholder="ej: ignacio.n12@gmail.com" required>{{ old('mail_recipients', ($settings['mail_recipients'] ?? '')) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Autorizados para gatillar envíos (opcional)</label>
                                <textarea name="mail_allowed_trigger_emails" class="form-textarea" placeholder="vacío = cualquiera con acceso puede gatillar">{{ old('mail_allowed_trigger_emails', ($settings['mail_allowed_trigger_emails'] ?? '')) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 3: Eventos (Grid) --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Eventos</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_cleaning" value="1" {{ old('mail_enabled_cleaning', ($settings['mail_enabled_cleaning'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Aseo</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Enviar al guardar asignaciones de aseo.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_novelty" value="1" {{ old('mail_enabled_novelty', ($settings['mail_enabled_novelty'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Novedades</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Enviar al registrar una novedad.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_academy" value="1" {{ old('mail_enabled_academy', ($settings['mail_enabled_academy'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Academias</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Enviar al registrar una academia.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_beds" value="1" {{ old('mail_enabled_beds', ($settings['mail_enabled_beds'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Camas</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Habilitar envío manual de reporte PDF.</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_planilla" value="1" {{ old('mail_enabled_planilla', ($settings['mail_enabled_planilla'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-sky-600 focus:ring-sky-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Planillas</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Enviar al presionar "Enviar correo".</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-white dark:hover:bg-slate-750 transition">
                                <input type="checkbox" name="mail_enabled_rotation" value="1" {{ old('mail_enabled_rotation', ($settings['mail_enabled_rotation'] ?? '0')) === '1' ? 'checked' : '' }} class="mt-0.5 rounded text-purple-600 focus:ring-purple-500 h-5 w-5 border-slate-300 dark:border-slate-600">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">Rotación</div>
                                    <div class="text-sm text-gray-500 dark:text-slate-400">Enviar resumen al generar rotación semanal.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                            Guardar Configuración de Correo
                        </x-ui.button>
                    </div>
                </form>
            </div>

            {{-- SECCIÓN 4: SEGURIDAD --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border-2 border-red-300 dark:border-red-700/50 overflow-hidden">
                <div class="px-6 py-5 border-b border-red-200 dark:border-red-700/30 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Seguridad</h2>
                            <p class="text-sm text-gray-500 dark:text-slate-400">Acciones críticas de eliminación de datos</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.system.clear_guardias') }}" class="p-6 bg-red-50/30 dark:bg-slate-800/50">
                    @csrf
                    <div class="grid grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Alcance</label>
                            <select id="clear-guardias-scope" name="scope" class="form-select" required>
                                <option value="one">Vaciar una guardia</option>
                                <option value="all">Vaciar todas las guardias</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Guardia</label>
                            <select id="clear-guardias-guardia" name="guardia_id" class="form-select">
                                <option value="">Seleccionar...</option>
                                @foreach(($guardias ?? collect()) as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirmación</label>
                            <input type="text" name="confirm_text" class="form-input" placeholder="Escribe CONFIRMAR" required>
                        </div>
                    </div>

                    <div class="mt-5 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700/50 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-skull-crossbones text-red-600 dark:text-red-400 text-xl mt-0.5"></i>
                            <div class="text-sm text-red-900 dark:text-red-200 font-medium">
                                Esta acción elimina turnos/dotación/asistencia/eventos/reemplazos de la(s) guardia(s). No se puede deshacer.
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-ui.button type="submit" variant="danger" size="md" icon="fas fa-eraser" onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.');">
                            Vaciar Guardias
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scope = document.getElementById('clear-guardias-scope');
            const guardia = document.getElementById('clear-guardias-guardia');
            if (!scope || !guardia) return;

            function syncClearGuardiasUI() {
                const isAll = scope.value === 'all';
                guardia.disabled = isAll;
                if (isAll) {
                    guardia.value = '';
                    guardia.removeAttribute('required');
                } else {
                    guardia.setAttribute('required', 'required');
                }
            }

            scope.addEventListener('change', syncClearGuardiasUI);
            syncClearGuardiasUI();
        });
    </script>
@endsection
