@extends('layouts.modern')

@section('content')
<div class="w-full">
    <x-ui.page-header title="Camas" subtitle="Gestión de camas y espacios de descanso" icon="fas fa-bed" iconVariant="primary">
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.beds.create') }}">
            Nueva cama
        </x-ui.button>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" icon="fas fa-exclamation-circle" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    {{-- Banner Guardia Activa --}}
    @if($activeGuardia)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-900">Guardia Activa: {{ $activeGuardia->name }}</h3>
                    <p class="text-xs text-blue-700">Solo se pueden asignar camas a voluntarios presentes en esta guardia</p>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-900">Sin Guardia Activa</h3>
                    <p class="text-xs text-amber-700">No se pueden asignar camas sin una guardia activa</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <x-ui.card class="mb-6">
        <div class="p-5 border-b border-[#9fb0c3] bg-[#c3cfdb]">
            <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1">
                    <label class="form-label mb-2">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                        <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Ocupada</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Mantención</option>
                        <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Deshabilitada</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="form-label mb-2">Género</label>
                    <select name="gender" class="form-select">
                        <option value="">Todos</option>
                        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Masculino</option>
                        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Femenino</option>
                        <option value="mixed" {{ request('gender') === 'mixed' ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <x-ui.button type="submit" variant="primary" size="md">Filtrar</x-ui.button>
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.beds.index') }}">Limpiar</x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>

    {{-- Grid de Camas --}}
    @if($beds->isEmpty())
        <x-ui.card>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-[#c3cfdb] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bed text-[#475569] text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1e293b] mb-2">No hay camas registradas</h3>
                <p class="text-[#475569] mb-6">Comienza creando tu primera cama para gestionar los espacios de descanso.</p>
                <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.beds.create') }}">
                    Crear primera cama
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($beds as $bed)
                <x-ui.card class="hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        {{-- Header --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-[#1e293b] mb-1">{{ $bed->name }}</h3>
                                @if($bed->location)
                                    <p class="text-sm text-[#475569] flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-xs"></i>
                                        {{ $bed->location }}
                                    </p>
                                @endif
                            </div>
                            <div class="w-10 h-10 rounded-full bg-[#c3cfdb] flex items-center justify-center">
                                <i class="fas fa-bed text-[#1e293b]"></i>
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            <x-ui.badge variant="{{ $bed->status_color }}" size="sm">
                                {{ $bed->status_label }}
                            </x-ui.badge>
                            <x-ui.badge variant="{{ $bed->gender_color }}" size="sm">
                                {{ $bed->gender_label }}
                            </x-ui.badge>
                        </div>

                        {{-- Ocupante Actual --}}
                        @if($bed->is_occupied && $bed->current_occupant_name)
                            <div class="mb-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-user text-amber-600 text-xs"></i>
                                    <p class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Ocupada por</p>
                                </div>
                                <p class="text-sm font-bold text-amber-900">{{ $bed->current_occupant_name }}</p>
                                @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                    <p class="text-xs text-amber-700 mt-1">
                                        Desde {{ $bed->currentAssignment->started_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @elseif($bed->notes)
                            <div class="mb-4 p-3 bg-[#e7eef5] rounded-lg border border-[#9fb0c3]">
                                <p class="text-xs text-[#475569] line-clamp-2">{{ $bed->notes }}</p>
                            </div>
                        @endif

                        {{-- Acciones Principales --}}
                        <div class="space-y-2 mb-4">
                            @if($bed->is_occupied)
                                <button type="button" onclick="openReleaseModal({{ $bed->id }}, '{{ $bed->name }}')" 
                                    class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    Liberar cama
                                </button>
                            @elseif($bed->canBeAssigned())
                                <button type="button" onclick="openAssignModal({{ $bed->id }}, '{{ $bed->name }}', '{{ $bed->gender }}')" 
                                    class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-user-plus"></i>
                                    Asignar cama
                                </button>
                            @else
                                <div class="w-full px-3 py-2 bg-[#c3cfdb] text-[#475569] font-semibold rounded-lg text-xs text-center">
                                    <i class="fas fa-ban mr-1"></i>
                                    No asignable
                                </div>
                            @endif
                        </div>

                        {{-- Control de Estado --}}
                        @if($bed->status !== 'occupied')
                        <div class="flex gap-1.5 mb-3">
                            @if($bed->status === 'disabled')
                                <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 transition-colors">
                                        <i class="fas fa-check mr-1"></i>Reactivar
                                    </button>
                                </form>
                            @else
                                @if($bed->status !== 'available')
                                <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 transition-colors">
                                        <i class="fas fa-check mr-1"></i>Disponible
                                    </button>
                                </form>
                                @endif
                                @if($bed->status !== 'maintenance')
                                <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="maintenance">
                                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 transition-colors">
                                        <i class="fas fa-tools mr-1"></i>Mantenimiento
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.beds.status', $bed) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="disabled">
                                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg bg-red-100 hover:bg-red-200 text-red-800 border border-red-300 transition-colors">
                                        <i class="fas fa-ban mr-1"></i>Deshabilitar
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endif

                        {{-- Acciones Secundarias --}}
                        <div class="flex gap-2 pt-4 border-t border-[#9fb0c3]">
                            <x-ui.button variant="ghost" size="sm" icon="fas fa-history" href="{{ route('admin.beds.history', $bed) }}" title="Historial" />
                            <x-ui.button variant="ghost" size="sm" icon="fas fa-pen" href="{{ route('admin.beds.edit', $bed) }}" title="Editar" />
                            <x-ui.button variant="ghost" size="sm" icon="fas fa-qrcode" href="{{ route('admin.beds.qr', $bed) }}" title="Ver QR" />
                            <x-ui.button variant="ghost" size="sm" icon="fas fa-print" href="{{ route('admin.beds.qr.print', $bed) }}" title="Imprimir" target="_blank" />
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif

    {{-- Modal Asignar Cama --}}
    <div id="assignModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-xl max-w-md w-full">
            <form id="assignForm" method="POST">
                @csrf
                <div class="p-6 border-b border-[#9fb0c3]">
                    <h3 class="text-lg font-bold text-[#1e293b]">Asignar Cama</h3>
                    <p class="text-sm text-[#475569]" id="assignBedName"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Voluntario <span class="text-red-500">*</span></label>
                        <select name="volunteer_id" required class="form-select" id="volunteerSelect">
                            <option value="">Seleccionar voluntario...</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Notas opcionales..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-[#9fb0c3] flex gap-3">
                    <button type="button" onclick="closeAssignModal()" class="flex-1 px-4 py-2 bg-[#c3cfdb] hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Liberar Cama --}}
    <div id="releaseModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#e7eef5] border border-[#9fb0c3] rounded-2xl shadow-xl max-w-md w-full">
            <form id="releaseForm" method="POST">
                @csrf
                <div class="p-6 border-b border-[#9fb0c3]">
                    <h3 class="text-lg font-bold text-[#1e293b]">Liberar Cama</h3>
                    <p class="text-sm text-[#475569]" id="releaseBedName"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Notas opcionales sobre la liberación..."></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-[#9fb0c3] flex gap-3">
                    <button type="button" onclick="closeReleaseModal()" class="flex-1 px-4 py-2 bg-[#c3cfdb] hover:bg-[#9fb0c3] text-[#1e293b] font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors">
                        Liberar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allVolunteers = [];

// Cargar voluntarios al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("admin.beds.api.volunteers") }}')
        .then(response => response.json())
        .then(data => {
            allVolunteers = data;
        })
        .catch(error => console.error('Error cargando voluntarios:', error));
});

function openAssignModal(bedId, bedName, bedGender = null) {
    const modal = document.getElementById('assignModal');
    const form = document.getElementById('assignForm');
    const select = document.getElementById('volunteerSelect');
    
    // Configurar formulario
    form.action = `/admin/beds/${bedId}/assign`;
    document.getElementById('assignBedName').textContent = bedName;
    
    // Cargar voluntarios filtrados por género de la cama
    const url = bedGender ? `{{ route("admin.beds.api.volunteers") }}?gender=${bedGender}` : '{{ route("admin.beds.api.volunteers") }}';
    
    fetch(url)
        .then(response => response.json())
        .then(volunteers => {
            select.innerHTML = '<option value="">Seleccionar voluntario...</option>';
            
            if (volunteers.length === 0) {
                select.innerHTML = '<option value="">No hay voluntarios disponibles</option>';
                select.disabled = true;
            } else {
                select.disabled = false;
                volunteers.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.id;
                    
                    let label = v.name;
                    if (v.rut) label += ` (${v.rut})`;
                    if (v.cargo) label += ` - ${v.cargo}`;
                    if (v.has_active_bed) label += ' ⚠️ Ya tiene cama';
                    
                    option.textContent = label;
                    if (v.has_active_bed) {
                        option.style.color = '#d97706';
                        option.style.fontWeight = 'bold';
                    }
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error cargando voluntarios:', error);
            select.innerHTML = '<option value="">Error al cargar voluntarios</option>';
            select.disabled = true;
        });
    
    modal.classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignForm').reset();
}

function openReleaseModal(bedId, bedName) {
    const modal = document.getElementById('releaseModal');
    const form = document.getElementById('releaseForm');
    
    form.action = `/admin/beds/${bedId}/release`;
    document.getElementById('releaseBedName').textContent = bedName;
    
    modal.classList.remove('hidden');
}

function closeReleaseModal() {
    document.getElementById('releaseModal').classList.add('hidden');
    document.getElementById('releaseForm').reset();
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAssignModal();
        closeReleaseModal();
    }
});
</script>
@endsection
