@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardias Preventivas</div>
            <div class="text-2xl font-extrabold text-slate-900">Crear Evento</div>
        </div>
        <a href="{{ route('admin.preventivas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
    </div>

    <form method="POST" action="{{ route('admin.preventivas.store') }}" class="mt-6 bg-white rounded-2xl border-2 border-slate-200 shadow-lg overflow-hidden">
        @csrf
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white text-slate-900 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Zona horaria</label>
                    <input type="text" name="timezone" value="{{ old('timezone', 'America/Santiago') }}" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white text-slate-900 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Inicio</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white text-slate-900 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Término</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white text-slate-900 font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div class="mt-8">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-black uppercase tracking-widest text-slate-700">Plantilla de Turnos (se aplica a todos los días)</div>
                        <div class="text-xs text-slate-500 mt-1">Define horarios como 08:00-10:00, etc. Si un turno cruza medianoche, pon fin menor (ej: 22:00 a 08:00).</div>
                    </div>
                    <button type="button" id="addRow" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                        <i class="fas fa-plus mr-1"></i> Agregar Turno
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Inicio</th>
                                <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Fin</th>
                                <th class="text-left px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Etiqueta (opcional)</th>
                                <th class="text-right px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-600">—</th>
                            </tr>
                        </thead>
                        <tbody id="rows" class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-3"><input type="time" name="template[0][start_time]" value="08:00" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></td>
                                <td class="px-4 py-3"><input type="time" name="template[0][end_time]" value="12:00" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></td>
                                <td class="px-4 py-3"><input type="text" name="template[0][label]" value="" class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ej: Turno 1"></td>
                                <td class="px-4 py-3 text-right"><button type="button" class="remove px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-red-50 text-red-700 font-bold text-xs"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-6 py-5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
            <a href="{{ route('admin.preventivas.index') }}" class="px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-black text-[11px] uppercase tracking-widest">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                <i class="fas fa-check"></i>
                Crear Preventiva
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    const rowsEl = document.getElementById('rows');
    const addBtn = document.getElementById('addRow');

    const renumber = () => {
        const rows = Array.from(rowsEl.querySelectorAll('tr'));
        rows.forEach((tr, idx) => {
            tr.querySelectorAll('input').forEach((inp) => {
                inp.name = inp.name.replace(/template\[\d+\]/, 'template[' + idx + ']');
            });
        });
    };

    rowsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove');
        if (!btn) return;
        const tr = btn.closest('tr');
        if (!tr) return;
        if (rowsEl.querySelectorAll('tr').length <= 1) return;
        tr.remove();
        renumber();
    });

    addBtn.addEventListener('click', () => {
        const idx = rowsEl.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-4 py-3"><input type="time" name="template[${idx}][start_time]" value="" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></td>
            <td class="px-4 py-3"><input type="time" name="template[${idx}][end_time]" value="" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></td>
            <td class="px-4 py-3"><input type="text" name="template[${idx}][label]" value="" class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl bg-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ej: Turno ${idx+1}"></td>
            <td class="px-4 py-3 text-right"><button type="button" class="remove px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-red-50 text-red-700 font-bold text-xs"><i class="fas fa-trash"></i></button></td>
        `;
        rowsEl.appendChild(tr);
    });
})();
</script>
@endsection
