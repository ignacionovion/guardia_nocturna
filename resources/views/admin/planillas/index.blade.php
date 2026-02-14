@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">Historial</div>
            <div class="text-sm text-slate-600 mt-1">Registro semanal de revisión de unidades.</div>
        </div>

        <a href="{{ route('admin.planillas.create') }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
            <i class="fas fa-plus"></i>
            Nueva planilla
        </a>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-teal-900/20 bg-sky-100">
            <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Filtrar por unidad</div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <select name="unidad" class="w-full sm:w-56 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                            <option value="">Todas</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u }}" {{ ($unidadSeleccionada ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-lg bg-slate-900 hover:bg-black text-white font-black text-[11px] uppercase tracking-widest">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.planillas.index') }}" class="w-full sm:w-auto px-5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-[11px] uppercase tracking-widest text-center">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sky-50 border-b border-teal-900/20">
                    <tr class="text-xs font-black uppercase tracking-widest text-slate-700">
                        <th class="text-left px-6 py-3">Fecha</th>
                        <th class="text-left px-6 py-3">Unidad</th>
                        <th class="text-left px-6 py-3">Estado</th>
                        <th class="text-left px-6 py-3">Registrada por</th>
                        <th class="text-right px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($planillas as $p)
                        <tr class="hover:bg-sky-50">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $p->fecha_revision?->format('d-m-Y H:i') }}</td>
                            <td class="px-6 py-4 text-slate-700 font-bold">{{ $p->unidad }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.planillas.estado.update', $p) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="estado" class="px-3 py-2 rounded-full text-xs font-black uppercase tracking-widest border {{ ($p->estado ?? '') === 'finalizado' ? 'bg-emerald-100 text-emerald-900 border-emerald-200' : 'bg-amber-100 text-amber-900 border-amber-200' }}" onchange="this.form.submit()">
                                        <option value="en_edicion" {{ ($p->estado ?? '') !== 'finalizado' ? 'selected' : '' }}>En edición</option>
                                        <option value="finalizado" {{ ($p->estado ?? '') === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $p->creador?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if(($p->estado ?? '') !== 'finalizado')
                                        <a href="{{ route('admin.planillas.edit', $p) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-teal-900/20 bg-sky-50 hover:bg-sky-100 text-slate-900 font-extrabold text-xs">
                                            <i class="fas fa-pen"></i>
                                            Continuar
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.planillas.show', $p) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                                        <i class="fas fa-eye"></i>
                                        Ver
                                    </a>
                                    <form method="POST" action="{{ route('admin.planillas.destroy', $p) }}" class="inline" onsubmit="return confirm('¿Eliminar esta planilla? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-xs">
                                            <i class="fas fa-trash"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">No hay planillas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $planillas->links() }}
        </div>
    </div>
</div>
@endsection
