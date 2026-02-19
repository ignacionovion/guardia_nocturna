@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardia</div>
            <div class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center uppercase">
                <i class="fas fa-bolt mr-3 text-red-700"></i>
                Guardiapp NOW
            </div>
            <div class="text-sm text-slate-600 mt-1">Vista en vivo del estado de la guardia constituida.</div>
        </div>

        <div class="flex items-center gap-2">
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
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardia Nocturna</div>
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

        function statusBadge(status, enTurno, esReemplazante) {
            const s = (status || 'constituye').toLowerCase();
            
            // Colores más vibrantes y diferenciados
            const badges = {
                'constituye': { 
                    label: 'CONSTITUYE', 
                    cls: enTurno ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    cardCls: enTurno ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white'
                },
                'reemplazo': { 
                    label: 'REEMPLAZO', 
                    cls: 'bg-purple-500 text-white border-purple-600',
                    cardCls: 'border-purple-300 bg-purple-50/50'
                },
                'permiso': { 
                    label: 'PERMISO', 
                    cls: 'bg-amber-500 text-white border-amber-600',
                    cardCls: 'border-amber-300 bg-amber-50/50'
                },
                'ausente': { 
                    label: 'AUSENTE', 
                    cls: 'bg-slate-500 text-white border-slate-600',
                    cardCls: 'border-slate-300 bg-slate-50'
                },
                'licencia': { 
                    label: 'LICENCIA', 
                    cls: 'bg-blue-500 text-white border-blue-600',
                    cardCls: 'border-blue-300 bg-blue-50/50'
                },
                'falta': { 
                    label: 'FALTA', 
                    cls: 'bg-rose-500 text-white border-rose-600',
                    cardCls: 'border-rose-300 bg-rose-50/50'
                },
            };
            
            return badges[s] || { 
                label: s.toUpperCase(), 
                cls: 'bg-slate-500 text-white border-slate-600',
                cardCls: 'border-slate-200 bg-white'
            };
        }

        function pill(label, cls) {
            return `<span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ${cls}">${label}</span>`;
        }

        function specialtyPill(label, icon, colorClass) {
            return `<span class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider ${colorClass}">
                ${icon ? `<i class="fas ${icon} text-[8px]"></i>` : ''}${label}
            </span>`;
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
                const badge = statusBadge(b.estado_asistencia, b.en_turno, b.es_reemplazante);

                const flags = [];
                
                // Badge de confirmación en turno (no es el mismo que "confirmado con código" del dashboard)
                if (b.en_turno) {
                    flags.push(pill('EN TURNO', 'bg-emerald-100 text-emerald-700 border-emerald-300'));
                } else {
                    flags.push(pill('PENDIENTE', 'bg-amber-100 text-amber-700 border-amber-300'));
                }
                
                if (b.es_jefe_guardia) flags.push(pill('JEFE', 'bg-indigo-500 text-white border-indigo-600'));
                if (b.es_refuerzo) flags.push(pill('REFUERZO', 'bg-sky-500 text-white border-sky-600'));
                if (b.es_cambio) flags.push(pill('CAMBIO', 'bg-violet-100 text-violet-800 border-violet-300'));
                if (b.es_sancion) flags.push(pill('SANCIÓN', 'bg-rose-500 text-white border-rose-600'));
                if (b.fuera_de_servicio) flags.push(pill('FUERA SERVICIO', 'bg-slate-500 text-white border-slate-600'));
                if (b.es_permanente) flags.push(pill('PERMANENTE', 'bg-emerald-100 text-emerald-800 border-emerald-300'));

                // Especialidades técnicas
                const specialties = [];
                if (b.es_conductor) specialties.push(specialtyPill('COND', 'fa-car', 'bg-blue-100 text-blue-700 border-blue-300'));
                if (b.es_operador_rescate) specialties.push(specialtyPill('R', '', 'bg-orange-100 text-orange-700 border-orange-300'));
                if (b.es_asistente_trauma) specialties.push(specialtyPill('A.T', '', 'bg-red-100 text-red-700 border-red-300'));

                // Años de servicio
                let serviceText = '';
                if (b.service_years !== null) {
                    const yearsLabel = b.service_years === 1 ? 'año' : 'años';
                    const monthsLabel = b.service_months === 1 ? 'mes' : 'meses';
                    if (b.service_months > 0) {
                        serviceText = `${b.service_years} ${yearsLabel} ${b.service_months} ${monthsLabel}`;
                    } else {
                        serviceText = `${b.service_years} ${yearsLabel}`;
                    }
                }

                // Información de reemplazo
                let replacementInfo = '';
                if (b.es_reemplazante && b.reemplaza_a) {
                    replacementInfo = `
                        <div class="mt-2 p-2 rounded-lg bg-purple-100 border border-purple-200">
                            <div class="text-[10px] font-black text-purple-600 uppercase tracking-wider">REEMPLAZA A</div>
                            <div class="text-xs font-bold text-purple-900">${b.reemplaza_a.nombre}</div>
                        </div>
                    `;
                }
                if (b.es_reemplazado && b.reemplazado_por) {
                    replacementInfo = `
                        <div class="mt-2 p-2 rounded-lg bg-amber-100 border border-amber-200">
                            <div class="text-[10px] font-black text-amber-600 uppercase tracking-wider">REEMPLAZADO POR</div>
                            <div class="text-xs font-bold text-amber-900">${b.reemplazado_por.nombre}</div>
                        </div>
                    `;
                }

                return `
                    <div class="rounded-xl border-2 ${badge.cardCls} p-4 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-extrabold text-slate-900 truncate uppercase">${(b.apellido_paterno || '—')}</div>
                                <div class="text-xs text-slate-600 font-semibold truncate">${(b.nombres || '')}</div>
                                ${b.cargo_texto ? `<div class="mt-0.5 text-[10px] font-black text-slate-500 uppercase tracking-wider">${b.cargo_texto}</div>` : ''}
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center rounded-lg border px-2 py-1 text-[10px] font-black uppercase tracking-wider ${badge.cls}">${badge.label}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 flex flex-wrap gap-1">${flags.join('')}</div>
                        
                        ${specialties.length > 0 ? `<div class="mt-2 flex flex-wrap gap-1">${specialties.join('')}</div>` : ''}
                        
                        <div class="mt-3 pt-3 border-t border-slate-200/60 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Móvil</span>
                                <div class="font-bold text-slate-700">${b.portatil || '—'}</div>
                            </div>
                            ${serviceText ? `
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Servicio</span>
                                <div class="font-bold text-slate-700">${serviceText}</div>
                            </div>
                            ` : ''}
                        </div>
                        
                        ${replacementInfo}
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
