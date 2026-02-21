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

        <!-- Panel de Acciones Rediseñado -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <!-- Header con Estado y Acciones Principales -->
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    @php
                        $status = strtolower((string) ($event->status ?? 'draft'));
                        if (!in_array($status, ['draft', 'active', 'closed'], true)) {
                            $status = 'draft';
                        }
                        $statusConfig = [
                            'active' => ['label' => 'Activa', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-300', 'dot' => 'text-emerald-500'],
                            'closed' => ['label' => 'Cerrada', 'bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-300', 'dot' => 'text-red-500'],
                            'draft' => ['label' => 'Borrador', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'dot' => 'text-slate-400'],
                        ];
                        $cfg = $statusConfig[$status];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider border {{ $cfg['bg'] }} {{ $cfg['text'] }} {{ $cfg['border'] }}">
                        <i class="fas fa-circle text-[8px] mr-2 {{ $cfg['dot'] }}"></i>
                        {{ $cfg['label'] }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $event->start_date?->format('d/m/Y') }} - {{ $event->end_date?->format('d/m/Y') }}</span>
                </div>
                <a href="{{ route('admin.preventivas.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <!-- Grid de Acciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Reporte -->
                <a href="{{ route('admin.preventivas.report', $event) }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-chart-pie text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-indigo-900">Reporte</div>
                        <div class="text-xs text-indigo-600">Ver estadísticas</div>
                    </div>
                </a>

                <!-- Exportar Excel -->
                <a href="{{ route('admin.preventivas.report.excel', $event) }}" class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-file-excel text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-emerald-900">Excel</div>
                        <div class="text-xs text-emerald-600">Descargar datos</div>
                    </div>
                </a>

                <!-- Exportar PDF -->
                <a href="{{ route('admin.preventivas.pdf', $event) }}" class="flex items-center gap-3 p-3 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-rose-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-file-pdf text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-rose-900">PDF</div>
                        <div class="text-xs text-rose-600">Generar reporte</div>
                    </div>
                </a>

                <!-- Código QR -->
                <a href="{{ $status === 'active' ? route('admin.preventivas.qr', $event) : '#' }}" class="flex items-center gap-3 p-3 rounded-lg {{ $status === 'active' ? 'bg-slate-50 hover:bg-slate-100 border-slate-200' : 'bg-slate-50 border-slate-200 opacity-50 cursor-not-allowed' }} border transition-all group" {{ $status === 'active' ? '' : 'onclick="return false;"' }}>
                    <div class="w-10 h-10 rounded-lg {{ $status === 'active' ? 'bg-slate-800' : 'bg-slate-400' }} text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-qrcode text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold {{ $status === 'active' ? 'text-slate-900' : 'text-slate-500' }}">QR</div>
                        <div class="text-xs {{ $status === 'active' ? 'text-slate-600' : 'text-slate-400' }}">{{ $status === 'active' ? 'Acceso público' : 'Solo guardia activa' }}</div>
                    </div>
                </a>
            </div>

            <!-- Acciones de Estado -->
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex flex-wrap items-center gap-2">
                    @if($status !== 'active' && $status !== 'closed')
                        <form method="POST" action="{{ route('admin.preventivas.status.activate', $event) }}" onsubmit="return confirm('¿Activar esta preventiva?');" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm">
                                <i class="fas fa-bolt"></i>
                                Activar Guardia
                            </button>
                        </form>
                    @endif
                    
                    @if($status !== 'closed')
                        <form method="POST" action="{{ route('admin.preventivas.status.close', $event) }}" onsubmit="return confirm('¿Cerrar esta preventiva? Quedará en solo lectura.');" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition-all shadow-sm">
                                <i class="fas fa-lock"></i>
                                Cerrar
                            </button>
                        </form>
                    @endif
                    
                    @if($status === 'closed')
                        <form method="POST" action="{{ route('admin.preventivas.status.draft', $event) }}" onsubmit="return confirm('¿Reabrir esta preventiva en modo Borrador?');" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-all shadow-sm">
                                <i class="fas fa-rotate"></i>
                                Reabrir
                            </button>
                        </form>
                    @endif
                    
                    @if($status !== 'active')
                        <div class="flex-1"></div>
                        <form method="POST" action="{{ route('admin.preventivas.destroy', $event) }}" onsubmit="return confirm('¿ELIMINAR permanentemente esta preventiva? Esta acción no se puede deshacer.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-red-300 hover:bg-red-50 text-red-600 font-bold text-xs transition-all">
                                <i class="fas fa-trash-can"></i>
                                Eliminar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
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
                                        
                                        {{-- Select simple con buscador --}}
                                        <div class="relative w-full sm:w-72 js-bombero-dropdown">
                                            <input type="hidden" name="bombero_id" class="js-selected-id" required>
                                            
                                            {{-- Botón que abre el dropdown --}}
                                            <button type="button" 
                                                    class="js-dropdown-toggle w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm text-left flex items-center justify-between {{ $status === 'closed' ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    {{ $status === 'closed' ? 'disabled' : '' }}>
                                                <span class="js-dropdown-text text-slate-400">Seleccionar bombero...</span>
                                                <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                            </button>
                                            
                                            {{-- Dropdown con buscador --}}
                                            <div class="js-dropdown-menu absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden hidden">
                                                {{-- Input de búsqueda --}}
                                                <div class="p-2 border-b border-slate-100 bg-white">
                                                    <div class="relative">
                                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                        <input type="text" 
                                                               class="js-search-input w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-slate-400 bg-white"
                                                               placeholder="Buscar bombero...">
                                                    </div>
                                                </div>
                                                
                                                {{-- Lista de opciones --}}
                                                <div class="js-options-list overflow-y-auto max-h-60">
                                                    @foreach($firefighters as $f)
                                                        @php
                                                            $rut = trim((string) ($f->rut ?? ''));
                                                            $searchText = strtolower(trim($f->apellido_paterno . ' ' . $f->nombres . ' ' . $rut));
                                                        @endphp
                                                        <div class="js-option px-3 py-2 text-sm hover:bg-slate-100 cursor-pointer border-b border-slate-50 last:border-0 {{ $status === 'closed' ? 'opacity-50 pointer-events-none' : '' }}"
                                                             data-id="{{ $f->id }}"
                                                             data-text="{{ $f->apellido_paterno }}, {{ $f->nombres }}"
                                                             data-search="{{ $searchText }}">
                                                            <div class="font-semibold text-slate-800">{{ $f->apellido_paterno }}, {{ $f->nombres }}</div>
                                                            @if($rut)
                                                                <div class="text-xs text-slate-500">{{ $rut }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                {{-- Mensaje si no hay resultados --}}
                                                <div class="js-no-results hidden px-3 py-4 text-sm text-slate-500 text-center bg-white">
                                                    No se encontraron resultados
                                                </div>
                                            </div>
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
                                                <th class="text-left py-2">Tipo</th>
                                                <th class="text-left py-2">Estado</th>
                                                <th class="text-right py-2">—</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($shift->assignments->sortBy(fn($a) => (string)($a->firefighter?->apellido_paterno ?? '')) as $a)
                                                @php
                                                    $esReemplazo = (bool) $a->reemplaza_a_bombero_id;
                                                    $reemplazaA = $a->replacedFirefighter;
                                                    // Verificar si este bombero fue reemplazado por otro
                                                    $fueReemplazado = !$a->es_refuerzo && !$esReemplazo && !$a->attendance && \App\Models\PreventiveShiftAssignment::where('preventive_shift_id', $shift->id)->where('reemplaza_a_bombero_id', $a->bombero_id)->exists();
                                                @endphp
                                                <tr class="{{ $fueReemplazado ? 'bg-rose-50/50' : '' }}">
                                                    <td class="py-2 font-bold {{ $fueReemplazado ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $a->firefighter?->apellido_paterno }} {{ $a->firefighter?->nombres }}</td>
                                                    <td class="py-2">
                                                        <div class="flex items-center gap-1">
                                                            @if($a->es_refuerzo)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-700 border border-sky-200">
                                                                    <i class="fas fa-user-plus mr-1 text-[8px]"></i>REF
                                                                </span>
                                                            @elseif($esReemplazo)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200" title="Reemplaza a: {{ $reemplazaA?->apellido_paterno ?? 'N/A' }}">
                                                                    <i class="fas fa-exchange-alt mr-1 text-[8px]"></i>REEMP
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">TIT</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="py-2">
                                                        @if($a->attendance)
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-800 border border-emerald-200">Asistió</span>
                                                                <form method="POST" action="{{ route('admin.preventivas.assignments.attendance.toggle', [$event, $a]) }}" onsubmit="return {{ $status === 'closed' ? 'false' : 'confirm(\'¿Quitar asistencia?\')' }};">
                                                                    @csrf
                                                                    <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 font-black text-[10px] uppercase tracking-widest {{ $status === 'closed' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-50' }}" {{ $status === 'closed' ? 'disabled' : '' }}>Quitar</button>
                                                                </form>
                                                            </div>
                                                        @elseif($fueReemplazado)
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-rose-50 text-rose-700 border border-rose-200">
                                                                    <i class="fas fa-exchange-alt mr-1"></i>Reemplazado
                                                                </span>
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
                                                    <td colspan="4" class="py-4 text-slate-500">Sin asignaciones.</td>
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
    // Dropdown functionality for bombero selection
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.js-bombero-dropdown').forEach(function(dropdown) {
            const toggle = dropdown.querySelector('.js-dropdown-toggle');
            const menu = dropdown.querySelector('.js-dropdown-menu');
            const searchInput = dropdown.querySelector('.js-search-input');
            const options = dropdown.querySelectorAll('.js-option');
            const noResults = dropdown.querySelector('.js-no-results');
            const hiddenInput = dropdown.querySelector('.js-selected-id');
            const textDisplay = dropdown.querySelector('.js-dropdown-text');

            // Toggle dropdown
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isHidden = menu.classList.contains('hidden');
                
                // Close all other dropdowns
                document.querySelectorAll('.js-dropdown-menu').forEach(function(m) {
                    m.classList.add('hidden');
                });
                
                if (isHidden) {
                    menu.classList.remove('hidden');
                    searchInput.focus();
                } else {
                    menu.classList.add('hidden');
                }
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchText = option.getAttribute('data-search') || '';
                    if (searchText.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0 && query !== '') {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            });

            // Select option
            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const text = this.getAttribute('data-text');
                    
                    hiddenInput.value = id;
                    textDisplay.textContent = text;
                    textDisplay.classList.remove('text-slate-400');
                    textDisplay.classList.add('text-slate-800');
                    
                    menu.classList.add('hidden');
                    searchInput.value = '';
                    
                    // Reset all options visibility
                    options.forEach(function(opt) {
                        opt.classList.remove('hidden');
                    });
                    noResults.classList.add('hidden');
                });
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        });
    });
</script>
@endsection
