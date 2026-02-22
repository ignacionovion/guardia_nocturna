@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                    <i class="fas fa-shield-halved mr-3 text-red-700"></i> Administración del Sistema
                </h1>
                <p class="text-slate-500 mt-1 font-medium">Acciones peligrosas. Usa con precaución.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-slate-200 flex items-center gap-2 uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Reset / Limpieza</div>
                <div class="text-xs text-slate-500 mt-1">Para ejecutar, escribe <span class="font-black text-slate-700">CONFIRMAR</span> y selecciona una acción.</div>
            </div>

            <form method="POST" action="{{ route('admin.system.purge') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Acción</label>
                        <select name="action" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
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

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Confirmación</label>
                        <input type="text" name="confirm_text" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" placeholder="Escribe CONFIRMAR" required>
                    </div>
                </div>

                <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="text-sm text-red-900">
                            <div class="font-black uppercase tracking-widest text-[11px]">Advertencia</div>
                            <div class="mt-1 font-semibold">Estas acciones pueden eliminar información de forma permanente.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-red-800" onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i>
                        Ejecutar
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Horarios del Sistema</div>
                <div class="text-xs text-slate-500 mt-1">Define ventanas y automatizaciones del sistema. Horario local según zona configurada.</div>
            </div>

            <form method="POST" action="{{ route('admin.system.schedule.save') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Habilitar Guardar Asistencia (HH:MM)</label>
                        <input type="time" name="attendance_enable_time" value="{{ old('attendance_enable_time', ($settings['attendance_enable_time'] ?? '21:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Deshabilitar Guardar Asistencia (HH:MM)</label>
                        <input type="time" name="attendance_disable_time" value="{{ old('attendance_disable_time', ($settings['attendance_disable_time'] ?? '10:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Constitución diaria (Lun-Sáb) (HH:MM)</label>
                        <input type="time" name="guardia_constitution_weekday_time" value="{{ old('guardia_constitution_weekday_time', ($settings['guardia_constitution_weekday_time'] ?? '23:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Constitución domingo (HH:MM)</label>
                        <input type="time" name="guardia_constitution_sunday_time" value="{{ old('guardia_constitution_sunday_time', ($settings['guardia_constitution_sunday_time'] ?? '22:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Salida diaria del turno (HH:MM)</label>
                        <input type="time" name="guardia_daily_end_time" value="{{ old('guardia_daily_end_time', ($settings['guardia_daily_end_time'] ?? '07:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Cambio guardia en turno (Semanal) (HH:MM)</label>
                        <input type="time" name="guardia_week_transition_time" value="{{ old('guardia_week_transition_time', ($settings['guardia_week_transition_time'] ?? '18:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Limpieza semanal (Domingo) (HH:MM)</label>
                        <input type="time" name="guardia_week_cleanup_time" value="{{ old('guardia_week_cleanup_time', ($settings['guardia_week_cleanup_time'] ?? '18:00')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Zona horaria scheduler</label>
                        <input type="text" name="guardia_schedule_tz" value="{{ old('guardia_schedule_tz', ($settings['guardia_schedule_tz'] ?? config('app.timezone'))) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" placeholder="America/Santiago" required>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                        <i class="fas fa-clock"></i>
                        Guardar Horarios
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Notificaciones por Correo</div>
                <div class="text-xs text-slate-500 mt-1">Configura remitente, destinatarios y qué eventos disparan envíos automáticos.</div>
            </div>

            <form method="POST" action="{{ route('admin.system.mail.save') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Correo remitente</label>
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', ($settings['mail_from_address'] ?? 'app@germaniatemuco.cl')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre remitente</label>
                        <input type="text" name="mail_from_name" value="{{ old('mail_from_name', ($settings['mail_from_name'] ?? config('app.name', 'AppGuardia'))) }}" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Destinatarios (separados por coma)</label>
                        <textarea name="mail_recipients" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold min-h-[90px]" placeholder="ej: ignacio.n12@gmail.com" required>{{ old('mail_recipients', ($settings['mail_recipients'] ?? '')) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Autorizados para gatillar envíos (emails separados por coma, opcional)</label>
                        <textarea name="mail_allowed_trigger_emails" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold min-h-[70px]" placeholder="vacío = cualquiera con acceso puede gatillar">{{ old('mail_allowed_trigger_emails', ($settings['mail_allowed_trigger_emails'] ?? '')) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="mail_enabled_cleaning" value="1" {{ old('mail_enabled_cleaning', ($settings['mail_enabled_cleaning'] ?? '0')) === '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 h-5 w-5 border-slate-300">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Aseo</div>
                            <div class="text-xs text-slate-500">Enviar al guardar asignaciones de aseo.</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="mail_enabled_novelty" value="1" {{ old('mail_enabled_novelty', ($settings['mail_enabled_novelty'] ?? '0')) === '1' ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-slate-300">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Novedades</div>
                            <div class="text-xs text-slate-500">Enviar al registrar una novedad.</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="mail_enabled_academy" value="1" {{ old('mail_enabled_academy', ($settings['mail_enabled_academy'] ?? '0')) === '1' ? 'checked' : '' }} class="rounded text-amber-600 focus:ring-amber-500 h-5 w-5 border-slate-300">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Academias</div>
                            <div class="text-xs text-slate-500">Enviar al registrar una academia.</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="checkbox" name="mail_enabled_beds" value="1" {{ old('mail_enabled_beds', ($settings['mail_enabled_beds'] ?? '0')) === '1' ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 h-5 w-5 border-slate-300">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Camas</div>
                            <div class="text-xs text-slate-500">Enviar al asignar o liberar cama.</div>
                        </div>
                    </label>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                        <i class="fas fa-envelope"></i>
                        Guardar Configuración de Correo
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Vaciar Guardias</div>
                <div class="text-xs text-slate-500 mt-1">Elimina datos operativos asociados a una guardia específica o a todas. No elimina bomberos ni usuarios del sistema.</div>
            </div>

            <form method="POST" action="{{ route('admin.system.clear_guardias') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Alcance</label>
                        <select id="clear-guardias-scope" name="scope" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" required>
                            <option value="one">Vaciar una guardia</option>
                            <option value="all">Vaciar todas las guardias</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Guardia</label>
                        <select id="clear-guardias-guardia" name="guardia_id" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold">
                            <option value="">Seleccionar...</option>
                            @foreach(($guardias ?? collect()) as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Confirmación</label>
                        <input type="text" name="confirm_text" class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold" placeholder="Escribe CONFIRMAR" required>
                    </div>
                </div>

                <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="text-sm text-red-900">
                            <div class="font-black uppercase tracking-widest text-[11px]">Advertencia</div>
                            <div class="mt-1 font-semibold">Esta acción elimina turnos/dotación/asistencia/eventos/reemplazos de la(s) guardia(s). No se puede deshacer.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-red-800" onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.');">
                        <i class="fas fa-eraser"></i>
                        Vaciar Guardias
                    </button>
                </div>
            </form>
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
        </div>
    </div>
@endsection
