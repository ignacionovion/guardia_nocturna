@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    {{-- Header tipo planilla --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">Editar listados</div>
            <div class="text-sm text-slate-600 mt-1">Arrastra las filas para reordenar los ítems del checklist</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.planillas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="mb-6 rounded-2xl border border-teal-900/30 bg-sky-100 p-4">
        <div class="text-xs font-black uppercase tracking-widest text-slate-900 mb-3">Seleccionar Unidad y Sección</div>
        <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Unidad</div>
                <select name="unidad" class="w-full md:w-56 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                    <option value="">Todas</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u }}" {{ ($unidadSeleccionada ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Sección</div>
                <select name="section" class="w-full md:w-56 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                    <option value="">Todas</option>
                    @foreach($sections as $key => $label)
                        <option value="{{ $key }}" {{ ($sectionSeleccionada ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 hover:bg-black text-white font-black text-[11px] uppercase tracking-widest">Filtrar</button>
                <a href="{{ route('admin.planillas.listados.index') }}" class="px-5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-[11px] uppercase tracking-widest text-center">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Agregar nuevo ítem + Restaurar estándar (al estilo planilla) --}}
    @if($unidadSeleccionada && $sectionSeleccionada)
    <div class="mb-6 bg-white rounded-2xl border border-teal-900/20 overflow-hidden shadow-sm">
        <div class="px-4 py-3 bg-teal-800 border-b border-teal-900 flex items-center justify-between">
            <div class="text-xs font-black uppercase tracking-widest text-white flex items-center gap-2">
                <i class="fas fa-cog"></i>
                Administrar {{ $sections[$sectionSeleccionada] ?? $sectionSeleccionada }} - {{ $unidadSeleccionada }}
            </div>
            <div class="flex items-center gap-2">
                {{-- Botón Restaurar Estándar --}}
                <form method="POST" action="{{ route('admin.planillas.listados.reset') }}" class="inline" onsubmit="return confirm('¿Restaurar todos los ítems al estado estándar? Esto eliminará los ítems personalizados y restaurará la configuración original.');">
                    @csrf
                    <input type="hidden" name="unidad" value="{{ $unidadSeleccionada }}">
                    <input type="hidden" name="section" value="{{ $sectionSeleccionada }}">
                    <button type="submit" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-black py-2 px-4 rounded-lg text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                        <i class="fas fa-rotate-left"></i>
                        Restaurar Estándar
                    </button>
                </form>
                <button type="button" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2 px-4 rounded-lg text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest" onclick="toggleSection('secNuevoItem')">
                    <i class="fas fa-plus-circle"></i>
                    Agregar Ítem
                    <i class="fas fa-chevron-down text-white/80" id="iconNuevoItem"></i>
                </button>
            </div>
        </div>
        <div id="secNuevoItem" class="hidden p-4 bg-sky-50">
            <form method="POST" action="{{ route('admin.planillas.listados.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="unidad" value="{{ $unidadSeleccionada }}">
                <input type="hidden" name="section" value="{{ $sectionSeleccionada }}">
                
                <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-3">
                    <div class="col-span-12 md:col-span-5">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1 block">Key (identificador)</label>
                        <input name="item_key" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" placeholder="ej: linterna_nightstick" required>
                    </div>
                    <div class="col-span-12 md:col-span-7">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1 block">Nombre del ítem</label>
                        <input name="label" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" placeholder="ej: Linterna NIGHTSTICK" required>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                        <i class="fas fa-plus"></i>
                        Agregar ítem
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Header tipo planilla --}}
    @if($unidadSeleccionada && $sectionSeleccionada)
    <div class="mb-6 rounded-2xl border border-teal-900/30 bg-sky-100 p-4">
        <div class="text-sm font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="text-sm font-black uppercase tracking-widest text-slate-900 mt-1">{{ $unidadSeleccionada }}</div>
        <div class="text-xs text-slate-700 mt-2 font-semibold">
            Sección: {{ $sections[$sectionSeleccionada] ?? $sectionSeleccionada }}
            <span class="ml-3 text-amber-600">
                <i class="fas fa-info-circle"></i> Arrastra las filas para reordenar
            </span>
        </div>
        <div id="save-status" class="text-xs font-bold text-slate-500 mt-1"></div>
    </div>

    {{-- Lista de items estilo planilla QR --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden shadow-sm">
        <div class="p-4 bg-sky-50">
            <div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                    <i class="fas fa-list-check"></i>
                    {{ $sections[$sectionSeleccionada] ?? $sectionSeleccionada }} - {{ $items->count() }} ítems
                </div>
            </div>
            
            @if($items->count() > 0)
                <div id="sortable-list" class="grid grid-cols-1 gap-3">
                    @foreach($items as $item)
                        <div class="sortable-item {{ $item->is_active ? '' : 'opacity-50' }}" data-id="{{ $item->id }}">
                            <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2 hover:border-teal-400 transition-colors group">
                                {{-- Handle para arrastrar --}}
                                <div class="col-span-12 md:col-span-5 flex items-center gap-2">
                                    <div class="drag-handle cursor-move p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 shrink-0" title="Arrastrar para reordenar">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <div class="order-badge shrink-0 w-6 h-6 rounded bg-slate-800 text-white flex items-center justify-center text-xs font-black">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex-1 rounded-lg {{ $item->is_active ? 'bg-yellow-50' : 'bg-slate-100' }} px-3 py-2 border {{ $item->is_active ? 'border-yellow-100' : 'border-slate-200' }}">
                                        <div class="text-sm font-extrabold {{ $item->is_active ? 'text-slate-900' : 'text-slate-500' }}">{{ $item->label }}</div>
                                        @if(!$item->is_active)
                                            <div class="text-[9px] font-bold text-red-500 uppercase tracking-tight">Inactivo</div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Campos del formulario (solo visual, no funcionan en edición) --}}
                                <div class="col-span-6 md:col-span-2">
                                    <select disabled class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-400 font-semibold text-sm cursor-not-allowed">
                                        <option>¿Funciona?</option>
                                        <option>Sí</option>
                                        <option>No</option>
                                    </select>
                                </div>
                                <div class="col-span-6 md:col-span-2">
                                    <input type="text" disabled placeholder="Cant." class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-400 font-semibold text-sm cursor-not-allowed">
                                </div>
                                <div class="col-span-12 md:col-span-3 flex items-center gap-2">
                                    <input type="text" disabled placeholder="Novedades" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-slate-400 font-semibold text-sm cursor-not-allowed">
                                    
                                    {{-- Acciones --}}
                                    <div class="flex items-center gap-1">
                                        {{-- Toggle activo --}}
                                        <form method="POST" action="{{ route('admin.planillas.listados.update', $item) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="label" value="{{ $item->label }}">
                                            <input type="hidden" name="is_active" value="0">
                                            <button type="submit" name="is_active" value="1" class="p-1.5 rounded {{ $item->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }} hover:bg-emerald-200" title="{{ $item->is_active ? 'Activo' : 'Inactivo' }}">
                                                <i class="fas {{ $item->is_active ? 'fa-eye' : 'fa-eye-slash' }} text-xs"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- Editar --}}
                                        <button type="button" onclick="toggleEditItem({{ $item->id }})" class="p-1.5 rounded bg-blue-50 text-blue-600 hover:bg-blue-100" title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        
                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('admin.planillas.listados.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este ítem?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded bg-rose-50 text-rose-600 hover:bg-rose-100" title="Eliminar">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Formulario de edición inline --}}
                            <div id="edit-form-{{ $item->id }}" class="hidden mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <form method="POST" action="{{ route('admin.planillas.listados.update', $item) }}" class="grid grid-cols-12 gap-3 items-end">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-span-12 md:col-span-4">
                                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1 block">Nombre</label>
                                        <input name="label" value="{{ $item->label }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                                    </div>
                                    <div class="col-span-6 md:col-span-3">
                                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1 block">Key</label>
                                        <input name="item_key" value="{{ $item->item_key }}" disabled class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-slate-100 text-slate-500 font-mono text-sm cursor-not-allowed">
                                    </div>
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1 block">Orden</label>
                                        <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                                    </div>
                                    <div class="col-span-12 md:col-span-3 flex gap-2">
                                        <input type="hidden" name="is_active" value="{{ $item->is_active ? '1' : '0' }}">
                                        <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-slate-900 text-white font-bold text-xs">
                                            <i class="fas fa-save mr-1"></i> Guardar
                                        </button>
                                        <button type="button" onclick="toggleEditItem({{ $item->id }})" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 font-bold text-xs">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 bg-white rounded-xl border border-dashed border-slate-300">
                    <i class="fas fa-inbox text-3xl text-slate-300 mb-2"></i>
                    <p class="text-slate-500 text-sm">No hay ítems en esta sección</p>
                    <p class="text-xs text-slate-400 mt-1">Agrega un nuevo ítem usando el formulario de arriba</p>
                </div>
            @endif
        </div>
    </div>
    @else
        {{-- Mensaje cuando no se ha seleccionado unidad/sección --}}
        <div class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
            <i class="fas fa-filter text-4xl text-slate-200 mb-3"></i>
            <p class="text-slate-500 font-medium">Selecciona una unidad y sección</p>
            <p class="text-sm text-slate-400 mt-1">Para ver y editar los ítems del checklist</p>
        </div>
    @endif
</div>

{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    let sortable;
    let debounceTimer;

    function toggleSection(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('icon' + id.replace('sec', ''));
        if (el) {
            el.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        }
    }

    function toggleEditItem(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) {
            form.classList.toggle('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('sortable-list');
        if (!list) return;

        sortable = Sortable.create(list, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateOrderNumbers();
                saveOrder();
            }
        });
    });

    function updateOrderNumbers() {
        const items = document.querySelectorAll('.sortable-item');
        items.forEach((item, index) => {
            const badge = item.querySelector('.order-badge');
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }

    function saveOrder() {
        const items = document.querySelectorAll('.sortable-item');
        const ids = Array.from(items).map(item => parseInt(item.dataset.id));

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const statusEl = document.getElementById('save-status');
            statusEl.textContent = 'Guardando orden...';
            statusEl.className = 'text-xs font-bold text-amber-600';

            fetch('{{ route('admin.planillas.listados.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ items: ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusEl.innerHTML = '<i class="fas fa-check"></i> Orden guardado';
                    statusEl.className = 'text-xs font-bold text-emerald-600';
                    setTimeout(() => {
                        statusEl.textContent = '';
                    }, 2000);
                } else {
                    statusEl.textContent = 'Error al guardar';
                    statusEl.className = 'text-xs font-bold text-red-600';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusEl.textContent = 'Error de conexión';
                statusEl.className = 'text-xs font-bold text-red-600';
            });
        }, 500);
    }
</script>

<style>
    .sortable-item {
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .sortable-drag {
        opacity: 0.95;
        transform: scale(1.01);
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        z-index: 1000;
    }
    .sortable-ghost {
        opacity: 0.5;
        background: #f1f5f9 !important;
        border: 2px dashed #94a3b8 !important;
    }
    .sortable-ghost * {
        opacity: 0;
    }
</style>
@endsection
