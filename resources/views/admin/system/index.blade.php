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
                            <option value="attendance_records">Reiniciar Estado "Guardia Constituida"</option>
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
    </div>
@endsection
