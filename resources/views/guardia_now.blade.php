@extends('layouts.now-fullscreen')

@section('title', 'GuardiAPP NOW - ' . branding()->nombre_empresa)

@section('header-center')
<div class="flex items-center gap-6">
    {{-- Status Badge --}}
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800">
        <span class="w-2 h-2 rounded-full bg-emerald-500 pulse-live" id="now-live-dot"></span>
        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400" id="now-last-update">Conectando...</span>
    </div>
    
    {{-- Server Time --}}
    <div class="hidden lg:flex items-center gap-2 text-sm">
        <i class="fas fa-server text-slate-400 text-xs"></i>
        <span class="font-mono font-medium text-slate-600 dark:text-slate-400" id="now-server-time">--:--:--</span>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-[1800px] mx-auto space-y-6">
    {{-- Header con acciones --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center shadow-lg shadow-red-500/25">
                    <i class="fas fa-bolt text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">GuardiAPP NOW</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Panel operativo en tiempo real</p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guardia.now.snapshot.pdf') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-sm"
               target="_blank">
                <i class="fas fa-file-pdf text-red-500"></i>
                <span>Exportar PDF</span>
            </a>
            
            <button type="button" 
                    onclick="sendSnapshotEmail()"
                    id="btn-send-email"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-100 transition-all shadow-sm">
                <i class="fas fa-paper-plane"></i>
                <span>Enviar por Email</span>
            </button>
        </div>
    </div>
    
    {{-- KPIs Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</p>
                    <p class="mt-1 text-xl font-black text-slate-900 dark:text-white" id="now-shift-status">—</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <i class="fas fa-shield-halved text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Guardia</p>
                    <p class="mt-1 text-xl font-black text-slate-900 dark:text-white truncate" id="now-shift-leader">—</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Presentes</p>
                    <p class="mt-1 text-xl font-black text-emerald-600 dark:text-emerald-400" id="now-count-present">0</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <i class="fas fa-user-check text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</p>
                    <p class="mt-1 text-xl font-black text-slate-900 dark:text-white" id="now-count-total">0</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fas fa-users-line text-slate-600 dark:text-slate-400"></i>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Dotación Grid --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                    <i class="fas fa-id-badge text-white text-sm"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Dotación Actual</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Estado en tiempo real del personal</p>
                </div>
            </div>
            
            {{-- Filter Pills --}}
            <div class="hidden md:flex items-center gap-2" id="filter-pills">
                <button onclick="filterGrid('all')" class="filter-pill active px-3 py-1.5 rounded-lg text-xs font-bold transition-all" data-filter="all">
                    Todos
                </button>
                <button onclick="filterGrid('present')" class="filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all" data-filter="present">
                    Presentes
                </button>
                <button onclick="filterGrid('absent')" class="filter-pill px-3 py-1.5 rounded-lg text-xs font-bold transition-all" data-filter="absent">
                    Ausentes
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div id="now-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4"></div>
            
            {{-- Empty State --}}
            <div id="now-empty" class="hidden py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                    <i class="fas fa-users-slash text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">No hay personal registrado</p>
            </div>
        </div>
    </div>
</div>

<style>
    .filter-pill {
        background: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .dark .filter-pill {
        border-color: #334155;
        color: #94a3b8;
    }
    .filter-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .dark .filter-pill:hover {
        background: #1e293b;
        border-color: #475569;
    }
    .filter-pill.active {
        background: #0f172a;
        color: white;
        border-color: #0f172a;
    }
    .dark .filter-pill.active {
        background: white;
        color: #0f172a;
        border-color: white;
    }
    
    .person-card {
        transition: all 0.2s ease;
    }
    .person-card:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@push('scripts')
<script>
(function () {
    const grid = document.getElementById('now-grid');
    const emptyState = document.getElementById('now-empty');
    const lastUpdate = document.getElementById('now-last-update');
    const footerUpdate = document.getElementById('footer-last-update');
    const liveDot = document.getElementById('now-live-dot');
    const headerIndicator = document.getElementById('header-live-indicator');

    const shiftStatus = document.getElementById('now-shift-status');
    const shiftLeader = document.getElementById('now-shift-leader');
    const serverTime = document.getElementById('now-server-time');
    const countPresent = document.getElementById('now-count-present');
    const countTotal = document.getElementById('now-count-total');

    let allBomberos = [];
    let currentFilter = 'all';

    function fmtTime(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '—';
        return d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function statusConfig(status, enTurno, confirmado) {
        const s = (status || 'constituye').toLowerCase();
        const isPresent = ['constituye', 'reemplazo'].includes(s);
        
        const configs = {
            'constituye': { 
                label: 'CONSTITUYE', 
                badgeCls: confirmado 
                    ? 'bg-emerald-600 text-white' 
                    : (enTurno ? 'bg-emerald-500 text-white' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400'),
                cardBorder: confirmado ? 'border-emerald-500' : (enTurno ? 'border-emerald-400' : 'border-slate-200 dark:border-slate-700'),
                cardBg: confirmado ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-white dark:bg-slate-800',
                isPresent: true
            },
            'reemplazo': { 
                label: 'REEMPLAZO', 
                badgeCls: 'bg-violet-500 text-white',
                cardBorder: confirmado ? 'border-emerald-500' : 'border-violet-400',
                cardBg: confirmado ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-violet-50 dark:bg-violet-900/20',
                isPresent: true
            },
            'permiso': { 
                label: 'PERMISO', 
                badgeCls: 'bg-amber-500 text-white',
                cardBorder: 'border-amber-300 dark:border-amber-700',
                cardBg: 'bg-amber-50 dark:bg-amber-900/20',
                isPresent: false
            },
            'ausente': { 
                label: 'AUSENTE', 
                badgeCls: 'bg-slate-500 text-white',
                cardBorder: 'border-slate-300 dark:border-slate-600',
                cardBg: 'bg-slate-50 dark:bg-slate-800/50',
                isPresent: false
            },
            'licencia': { 
                label: 'LICENCIA', 
                badgeCls: 'bg-blue-500 text-white',
                cardBorder: 'border-blue-300 dark:border-blue-700',
                cardBg: 'bg-blue-50 dark:bg-blue-900/20',
                isPresent: false
            },
            'falta': { 
                label: 'FALTA', 
                badgeCls: 'bg-rose-500 text-white',
                cardBorder: 'border-rose-300 dark:border-rose-700',
                cardBg: 'bg-rose-50 dark:bg-rose-900/20',
                isPresent: false
            },
        };
        
        return configs[s] || { 
            label: s.toUpperCase(), 
            badgeCls: 'bg-slate-500 text-white',
            cardBorder: 'border-slate-200 dark:border-slate-700',
            cardBg: 'bg-white dark:bg-slate-800',
            isPresent: false
        };
    }

    function renderCard(b) {
        const config = statusConfig(b.estado_asistencia, b.en_turno, b.confirmado);
        
        // Tags
        const tags = [];
        if (b.confirmado) tags.push({ label: 'Confirmado', cls: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' });
        if (b.es_jefe_guardia) tags.push({ label: 'Jefe', cls: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400' });
        if (b.es_refuerzo) tags.push({ label: 'Refuerzo', cls: 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-400' });
        if (b.es_permanente) tags.push({ label: 'Permanente', cls: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' });
        
        // Specialties
        const specs = [];
        if (b.es_conductor) specs.push({ icon: 'fa-car', label: 'Conductor', cls: 'text-blue-600 dark:text-blue-400' });
        if (b.es_operador_rescate) specs.push({ icon: 'fa-life-ring', label: 'Rescate', cls: 'text-orange-600 dark:text-orange-400' });
        if (b.es_asistente_trauma) specs.push({ icon: 'fa-heart-pulse', label: 'Trauma', cls: 'text-red-600 dark:text-red-400' });

        // Service years
        let serviceYears = '';
        if (b.service_years !== null && b.service_years !== undefined) {
            serviceYears = b.service_years + (b.service_years === 1 ? ' año' : ' años');
        }

        // Replacement info
        let replacementHtml = '';
        if (b.es_reemplazante && b.reemplaza_a) {
            replacementHtml = `
                <div class="mt-3 p-2 rounded-lg bg-violet-100 dark:bg-violet-900/30 border border-violet-200 dark:border-violet-800">
                    <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase">Reemplaza a:</span>
                    <span class="text-xs font-semibold text-violet-800 dark:text-violet-300 ml-1">${b.reemplaza_a.nombre}</span>
                </div>`;
        }
        if (b.es_reemplazado && b.reemplazado_por) {
            replacementHtml = `
                <div class="mt-3 p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase">Reemplazado por:</span>
                    <span class="text-xs font-semibold text-amber-800 dark:text-amber-300 ml-1">${b.reemplazado_por.nombre}</span>
                </div>`;
        }

        return `
            <div class="person-card rounded-xl border-2 ${config.cardBorder} ${config.cardBg} p-4 shadow-sm" data-present="${config.isPresent}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">${b.apellido_paterno || '—'}</p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 truncate">${b.nombres || ''}</p>
                        ${b.cargo_texto ? `<p class="mt-0.5 text-[10px] font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wide">${b.cargo_texto}</p>` : ''}
                    </div>
                    <span class="shrink-0 px-2 py-1 rounded-lg text-[10px] font-bold uppercase ${config.badgeCls}">${config.label}</span>
                </div>
                
                ${tags.length > 0 ? `
                <div class="mt-3 flex flex-wrap gap-1">
                    ${tags.map(t => `<span class="px-2 py-0.5 rounded-md text-[10px] font-semibold ${t.cls}">${t.label}</span>`).join('')}
                </div>` : ''}
                
                ${specs.length > 0 ? `
                <div class="mt-2 flex items-center gap-3">
                    ${specs.map(s => `<span class="flex items-center gap-1 text-xs ${s.cls}" title="${s.label}"><i class="fas ${s.icon} text-[10px]"></i></span>`).join('')}
                </div>` : ''}
                
                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Móvil</span>
                        <p class="font-semibold text-slate-700 dark:text-slate-300">${b.portatil || '—'}</p>
                    </div>
                    ${serviceYears ? `
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Servicio</span>
                        <p class="font-semibold text-slate-700 dark:text-slate-300">${serviceYears}</p>
                    </div>` : ''}
                </div>
                
                ${replacementHtml}
            </div>
        `;
    }

    function renderGrid() {
        let filtered = allBomberos;
        if (currentFilter === 'present') {
            filtered = allBomberos.filter(b => ['constituye', 'reemplazo'].includes((b.estado_asistencia || '').toLowerCase()));
        } else if (currentFilter === 'absent') {
            filtered = allBomberos.filter(b => !['constituye', 'reemplazo'].includes((b.estado_asistencia || '').toLowerCase()));
        }

        if (filtered.length === 0) {
            grid.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            grid.innerHTML = filtered.map(renderCard).join('');
        }
    }

    window.filterGrid = function(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-pill').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === filter);
        });
        renderGrid();
    };

    function render(payload) {
        if (!payload || typeof payload !== 'object') return;

        // Server time
        if (serverTime) serverTime.textContent = fmtTime(payload.server_time);

        // Shift info
        if (!payload.shift) {
            if (shiftStatus) shiftStatus.textContent = 'Sin turno';
            if (shiftLeader) shiftLeader.textContent = '—';
        } else {
            if (shiftStatus) shiftStatus.textContent = (payload.shift.status || 'Activo').toUpperCase();
            if (shiftLeader) shiftLeader.textContent = payload.shift.leader || '—';
        }

        // Bomberos
        allBomberos = Array.isArray(payload.bomberos) ? payload.bomberos : [];
        
        // Counts
        const presentCount = allBomberos.filter(b => ['constituye', 'reemplazo'].includes((b.estado_asistencia || '').toLowerCase())).length;
        if (countPresent) countPresent.textContent = presentCount;
        if (countTotal) countTotal.textContent = allBomberos.length;

        renderGrid();
    }

    async function tick() {
        try {
            if (liveDot) {
                liveDot.classList.remove('bg-rose-500');
                liveDot.classList.add('bg-emerald-500');
            }
            if (headerIndicator) {
                headerIndicator.classList.remove('bg-rose-500');
                headerIndicator.classList.add('bg-emerald-500');
            }

            const res = await fetch('{{ route('guardia.now.data') }}', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (!res.ok) throw new Error('bad_status');
            const json = await res.json();
            render(json);

            const timeStr = new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
            if (lastUpdate) lastUpdate.textContent = 'En vivo · ' + timeStr;
            if (footerUpdate) footerUpdate.textContent = 'Última actualización: ' + timeStr;
        } catch (e) {
            if (liveDot) {
                liveDot.classList.remove('bg-emerald-500');
                liveDot.classList.add('bg-rose-500');
            }
            if (headerIndicator) {
                headerIndicator.classList.remove('bg-emerald-500');
                headerIndicator.classList.add('bg-rose-500');
            }
            if (lastUpdate) lastUpdate.textContent = 'Sin conexión';
        }
    }

    tick();
    setInterval(tick, 10000);

    // Send snapshot email
    window.sendSnapshotEmail = async function() {
        const btn = document.getElementById('btn-send-email');
        if (!btn) return;
        
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route('guardia.now.snapshot.email') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin'
            });

            const json = await res.json();
            alert(json.success ? '✓ ' + json.message : '✗ ' + (json.error || 'Error al enviar'));
        } catch (e) {
            alert('✗ Error de conexión');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    };
})();
</script>
@endpush
