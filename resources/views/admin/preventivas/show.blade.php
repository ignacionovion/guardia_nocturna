@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardias Preventivas</div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $event->title }}</div>
            <div class="text-sm text-slate-600 mt-1">
                {{ $event->start_date?->format('d-m-Y') }} → {{ $event->end_date?->format('d-m-Y') }} · TZ {{ $event->timezone }}
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <!-- Fila 1: Estado y Acciones principales -->
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $status = strtolower((string) ($event->status ?? 'draft'));
                    if (!in_array($status, ['draft', 'active', 'closed'], true)) {
                        $status = 'draft';
                    }
                    $statusLabel = $status === 'active' ? 'Activa' : ($status === 'closed' ? 'Cerrada' : 'Borrador');
                    $statusCls = $status === 'active'
                        ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                        : ($status === 'closed' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-slate-100 text-slate-700 border border-slate-300');
                @endphp

                <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-black uppercase tracking-wider border {{ $statusCls }}">
                    <i class="fas fa-circle text-[8px] mr-2 {{ $status === 'active' ? 'text-emerald-500' : ($status === 'closed' ? 'text-red-500' : 'text-slate-400') }}"></i>
                    {{ $statusLabel }}
                </span>

                <div class="h-6 w-px bg-slate-300 mx-1"></div>

                <a href="{{ route('admin.preventivas.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-all">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>

                <a href="{{ route('admin.preventivas.report', $event) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                    <i class="fas fa-chart-pie"></i>
                    Reporte
                </a>
            </div>

            <!-- Fila 2: Exportaciones -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Exportar:</span>
                
                <a href="{{ route('admin.preventivas.report.excel', $event) }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                    <i class="fas fa-file-excel"></i>
                    Excel
                </a>
                <a href="{{ route('admin.preventivas.pdf', $event) }}" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </a>
                <a href="{{ $status === 'active' ? route('admin.preventivas.qr', $event) : '#' }}" class="inline-flex items-center gap-2 {{ $status === 'active' ? 'bg-slate-800 hover:bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }} font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm {{ $status === 'active' ? 'hover:shadow' : '' }}" {{ $status === 'active' ? '' : 'aria-disabled=true tabindex=-1' }}>
                    <i class="fas fa-qrcode"></i>
                    QR
                </a>
            </div>

            <!-- Fila 3: Gestión de estado -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Estado:</span>
                
                @if($status !== 'active' && $status !== 'closed')
                    <form method="POST" action="{{ route('admin.preventivas.status.activate', $event) }}" onsubmit="return confirm('¿Activar esta preventiva?');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                            <i class="fas fa-bolt"></i>
                            Activar
                        </button>
                    </form>
                @endif
                
                @if($status !== 'closed')
                    <form method="POST" action="{{ route('admin.preventivas.status.close', $event) }}" onsubmit="return confirm('¿Cerrar esta preventiva? Quedará en solo lectura.');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                            <i class="fas fa-lock"></i>
                            Cerrar
                        </button>
                    </form>
                @endif
                
                @if($status === 'closed')
                    <form method="POST" action="{{ route('admin.preventivas.status.draft', $event) }}" onsubmit="return confirm('¿Reabrir esta preventiva en modo Borrador?');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow">
                            <i class="fas fa-rotate"></i>
                            Reabrir
                        </button>
                    </form>
                @endif
            </div>

            <!-- Fila 4: Zona peligro (eliminar) -->
            @if($status !== 'active')
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200 mt-1">
                    <span class="text-xs font-bold text-red-400 uppercase tracking-wider mr-1">Zona peligro:</span>
                    
                    <form method="POST" action="{{ route('admin.preventivas.destroy', $event) }}" onsubmit="return confirm('¿ELIMINAR permanentemente esta preventiva? Esta acción no se puede deshacer.');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 bg-white border border-red-300 hover:bg-red-50 text-red-600 font-bold py-2 px-3 rounded-lg text-xs transition-all">
                            <i class="fas fa-trash-can"></i>
                            Eliminar Evento
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('warning'))
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 text-amber-900">
            <div class="text-sm font-extrabold">{{ session('warning') }}</div>
        </div>
    @endif

    <div class="mt-6 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black uppercase tracking-widest text-slate-700">Plantilla de turnos</div>
                <div class="text-xs text-slate-500 mt-1">Si cambias la plantilla, se regeneran los turnos y se pierden asignaciones.</div>
            </div>

            <form method="POST" action="{{ route('admin.preventivas.templates.save', $event) }}" class="p-6" {{ in_array($status, ['active','closed'], true) ? 'onsubmit=return false;' : '' }}>
                @csrf
                <div class="mt-6">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Turnos</div>
                    <div id="tplRows" class="space-y-2">
                        @foreach($event->templates as $i => $tpl)
                            <div class="grid grid-cols-12 gap-2">
                                <div class="col-span-5">
                                    <input type="time" name="template[{{ $i }}][start_time]" value="{{ substr((string) $tpl->start_time, 0, 5) }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" {{ in_array($status, ['active','closed'], true) ? 'disabled' : '' }}>
                                </div>
                                <div class="col-span-5">
                                    <input type="time" name="template[{{ $i }}][end_time]" value="{{ substr((string) $tpl->end_time, 0, 5) }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" {{ in_array($status, ['active','closed'], true) ? 'disabled' : '' }}>
                                </div>
                                <div class="col-span-2">
                                    <input type="text" name="template[{{ $i }}][label]" value="{{ $tpl->label }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold" placeholder="#" {{ in_array($status, ['active','closed'], true) ? 'disabled' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end">
                    @if(in_array($status, ['active','closed'], true))
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-red-800 opacity-50 cursor-not-allowed" disabled>
                            <i class="fas fa-rotate"></i>
                            Regenerar
                        </button>
                    @else
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-red-800" onclick="return confirm('Esto regenerará los turnos y eliminará las asignaciones actuales. ¿Continuar?');">
                            <i class="fas fa-rotate"></i>
                            Regenerar
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div>
            @foreach($shiftsByDate as $date => $shifts)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-black uppercase tracking-widest text-slate-700">{{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd D [de] MMMM YYYY') }}</div>
                            <div class="text-xs text-slate-500 mt-1">Asignación por turno</div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($shifts as $shift)
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-black text-slate-900">
                                            {{ substr((string) $shift->start_time, 0, 5) }} a {{ substr((string) $shift->end_time, 0, 5) }}
                                            @if($shift->label)
                                                <span class="ml-2 text-xs font-black uppercase tracking-widest text-slate-500">{{ $shift->label }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">Asignados: {{ $shift->assignments->count() }}</div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.preventivas.assignments.add', $event) }}" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="preventive_shift_id" value="{{ $shift->id }}">
                                        <div class="flex items-center gap-2">
                                            <input type="text" class="js-bombero-search px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm w-full sm:w-56" placeholder="Buscar por nombre o RUT..." {{ $status === 'closed' ? 'disabled' : '' }}>
                                            <select name="bombero_id" class="js-bombero-select px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm w-full sm:w-72" required {{ $status === 'closed' ? 'disabled' : '' }}>
                                                <option value="">Seleccionar bombero...</option>
                                                @foreach($firefighters as $f)
                                                    @php
                                                        $rut = trim((string) ($f->rut ?? ''));
                                                        $label = trim((string) $f->apellido_paterno . ' ' . (string) $f->nombres);
                                                        $full = trim($label . ($rut !== '' ? ' · ' . $rut : ''));
                                                        $haystack = strtolower($label . ' ' . $rut);
                                                    @endphp
                                                    <option value="{{ $f->id }}" data-search="{{ $haystack }}">{{ $full }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-black text-white font-black text-[11px] uppercase tracking-widest {{ $status === 'closed' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $status === 'closed' ? 'disabled' : '' }}>
                                            <i class="fas fa-plus mr-1"></i> Agregar
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-xs font-black uppercase tracking-widest text-slate-500">
                                                <th class="text-left py-2">Bombero</th>
                                                <th class="text-left py-2">Estado</th>
                                                <th class="text-right py-2">—</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($shift->assignments->sortBy(fn($a) => (string)($a->firefighter?->apellido_paterno ?? '')) as $a)
                                                <tr>
                                                    <td class="py-2 font-bold text-slate-900">{{ $a->firefighter?->apellido_paterno }} {{ $a->firefighter?->nombres }}</td>
                                                    <td class="py-2">
                                                        @if($a->attendance)
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-800 border border-emerald-200">Asistió</span>
                                                                <form method="POST" action="{{ route('admin.preventivas.assignments.attendance.toggle', [$event, $a]) }}" onsubmit="return {{ $status === 'closed' ? 'false' : 'confirm(\'¿Quitar asistencia?\')' }};">
                                                                    @csrf
                                                                    <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest {{ $status === 'closed' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50' }}" {{ $status === 'closed' ? 'disabled' : '' }}>Quitar</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-slate-100 text-slate-700 border border-slate-200">Pendiente</span>
                                                                <form method="POST" action="{{ route('admin.preventivas.assignments.attendance.toggle', [$event, $a]) }}" onsubmit="return {{ $status === 'closed' ? 'false' : 'confirm(\'¿Marcar asistencia manualmente?\')' }};">
                                                                    @csrf
                                                                    <button type="submit" class="px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 font-black text-[10px] uppercase tracking-widest {{ $status === 'closed' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-emerald-100' }}" {{ $status === 'closed' ? 'disabled' : '' }}>Marcar</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 text-right">
                                                        @if($status === 'closed')
                                                            <form method="POST" action="{{ route('admin.preventivas.assignments.remove', [$event, $a]) }}" onsubmit="return false;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-red-700 font-bold text-xs opacity-50 cursor-not-allowed" disabled><i class="fas fa-trash"></i></button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('admin.preventivas.assignments.remove', [$event, $a]) }}" onsubmit="return confirm('¿Eliminar asignación?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-red-50 text-red-700 font-bold text-xs"><i class="fas fa-trash"></i></button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-4 text-slate-500">Sin asignaciones.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function () {
        const rows = document.querySelectorAll('form .js-bombero-search');
        rows.forEach((searchInput) => {
            const form = searchInput.closest('form');
            if (!form) return;
            const select = form.querySelector('.js-bombero-select');
            if (!select) return;

            const options = Array.from(select.querySelectorAll('option'));

            function applyFilter() {
                const q = (searchInput.value || '').trim().toLowerCase();
                options.forEach((opt) => {
                    if (!opt.value) {
                        opt.hidden = false;
                        return;
                    }
                    const hay = (opt.getAttribute('data-search') || '').toLowerCase();
                    opt.hidden = q !== '' && !hay.includes(q);
                });

                if (select.selectedOptions.length && select.selectedOptions[0].hidden) {
                    select.value = '';
                }
            }

            searchInput.addEventListener('input', applyFilter);
        });
    })();
</script>
@endsection
