@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardia</div>
            <div class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center uppercase">
                <i class="fas fa-bolt mr-3 text-red-700"></i>
                Now
            </div>
            <div class="text-sm text-slate-600 mt-1">Vista en vivo del estado de la guardia constituida.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guardia') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                <i class="fas fa-arrow-left"></i>
                Ir a libro
            </a>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-extrabold">
                <span class="w-2 h-2 rounded-full bg-emerald-600" id="now-live-dot"></span>
                <span id="now-last-update">Actualizando...</span>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div id="now-shift" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <div class="text-xs font-black uppercase tracking-widest text-slate-600">Guardia constituida</div>
                <div class="text-sm text-slate-600 mt-1">Información del turno activo.</div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500">Estado</div>
                    <div class="mt-1 text-lg font-extrabold text-slate-900" id="now-shift-status">—</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500">Líder</div>
                    <div class="mt-1 text-lg font-extrabold text-slate-900" id="now-shift-leader">—</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500">Hora servidor</div>
                    <div class="mt-1 text-lg font-extrabold text-slate-900" id="now-server-time">—</div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-teal-900/20 bg-sky-100">
                <div class="text-xs font-black uppercase tracking-widest text-slate-600">Dotación</div>
                <div class="text-sm text-slate-600 mt-1">Estados actuales de los bomberos.</div>
            </div>

            <div class="p-6">
                <div id="now-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const grid = document.getElementById('now-grid');
        const lastUpdate = document.getElementById('now-last-update');
        const liveDot = document.getElementById('now-live-dot');

        const shiftStatus = document.getElementById('now-shift-status');
        const shiftLeader = document.getElementById('now-shift-leader');
        const serverTime = document.getElementById('now-server-time');

        function fmtTime(iso) {
            if (!iso) return '—';
            const d = new Date(iso);
            if (Number.isNaN(d.getTime())) return '—';
            return d.toLocaleString();
        }

        function statusBadge(status) {
            const s = (status || 'constituye').toLowerCase();
            if (s === 'constituye') return { label: 'Constituye', cls: 'bg-emerald-600/10 text-emerald-800 border-emerald-600/20' };
            if (s === 'permiso') return { label: 'Permiso', cls: 'bg-amber-600/10 text-amber-900 border-amber-600/20' };
            if (s === 'ausente') return { label: 'Ausente', cls: 'bg-slate-600/10 text-slate-800 border-slate-600/20' };
            if (s === 'licencia') return { label: 'Licencia', cls: 'bg-blue-600/10 text-blue-900 border-blue-600/20' };
            if (s === 'falta') return { label: 'Falta', cls: 'bg-rose-600/10 text-rose-900 border-rose-600/20' };
            if (s === 'reemplazo') return { label: 'Reemplazo', cls: 'bg-purple-600/10 text-purple-900 border-purple-600/20' };
            return { label: s, cls: 'bg-slate-600/10 text-slate-800 border-slate-600/20' };
        }

        function pill(label, cls) {
            return `<span class="inline-flex items-center rounded-full border px-2 py-1 text-[10px] font-black uppercase tracking-widest ${cls}">${label}</span>`;
        }

        function render(payload) {
            if (!payload || typeof payload !== 'object') return;

            serverTime.textContent = fmtTime(payload.server_time);

            if (!payload.shift) {
                shiftStatus.textContent = 'No constituida';
                shiftLeader.textContent = '—';
            } else {
                shiftStatus.textContent = (payload.shift.status || 'active').toUpperCase();
                shiftLeader.textContent = payload.shift.leader || '—';
            }

            const bomberos = Array.isArray(payload.bomberos) ? payload.bomberos : [];
            grid.innerHTML = bomberos.map((b) => {
                const badge = statusBadge(b.estado_asistencia);

                const flags = [];
                if (b.en_turno) flags.push(pill('En turno', 'bg-slate-950 text-white border-slate-800'));
                if (b.es_jefe_guardia) flags.push(pill('Jefe', 'bg-indigo-600/10 text-indigo-900 border-indigo-600/20'));
                if (b.es_refuerzo) flags.push(pill('Refuerzo', 'bg-sky-600/10 text-sky-900 border-sky-600/20'));
                if (b.es_cambio) flags.push(pill('Cambio', 'bg-purple-600/10 text-purple-900 border-purple-600/20'));
                if (b.es_sancion) flags.push(pill('Sanción', 'bg-rose-600/10 text-rose-900 border-rose-600/20'));
                if (b.fuera_de_servicio) flags.push(pill('Fuera servicio', 'bg-slate-600/10 text-slate-900 border-slate-600/20'));

                return `
                    <div class="rounded-xl border border-slate-200 bg-white p-4 hover:bg-sky-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-slate-900 truncate">${(b.nombre || '—')}</div>
                                <div class="mt-1 text-xs text-slate-600 font-semibold">${b.portatil ? ('Portátil: ' + b.portatil) : 'Portátil: —'}</div>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center rounded-full border px-2 py-1 text-[10px] font-black uppercase tracking-widest ${badge.cls}">${badge.label}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-1.5">${flags.join('')}</div>
                    </div>
                `;
            }).join('');
        }

        async function tick() {
            try {
                liveDot.classList.remove('bg-rose-600');
                liveDot.classList.add('bg-emerald-600');

                const res = await fetch('{{ route('guardia.now.data') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!res.ok) throw new Error('bad_status');
                const json = await res.json();
                render(json);

                lastUpdate.textContent = 'Actualizado: ' + new Date().toLocaleTimeString();
            } catch (e) {
                liveDot.classList.remove('bg-emerald-600');
                liveDot.classList.add('bg-rose-600');
                lastUpdate.textContent = 'Sin conexión';
            }
        }

        tick();
        setInterval(tick, 10000);
    })();
</script>
@endpush
