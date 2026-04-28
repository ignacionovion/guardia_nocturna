@extends('layouts.modern')

@section('title', 'Gestión de Voluntarios - ' . branding()->nombre_empresa)
@section('page-title', 'Gestión de Voluntarios')

@section('content')
    <x-ui.page-header title="Gestión de Voluntarios" subtitle="Administración del personal del cuerpo de bomberos" icon="fas fa-users" iconVariant="red">
        <div class="flex flex-wrap gap-3 items-center">
            <!-- Botón de Eliminación Masiva -->
            <x-ui.button id="btn-bulk-delete" variant="danger" size="md" icon="fas fa-trash-can" onclick="confirmBulkDelete()" style="display: none;">
                Eliminar (<span id="selected-count">0</span>)
            </x-ui.button>

            @if(auth()->check() && in_array(auth()->user()->role, ['capitan', 'super_admin']))
                <x-ui.button variant="danger" size="md" icon="fas fa-bomb" onclick="openPurgeModal()">
                    Eliminar todos
                </x-ui.button>
            @endif

            @if(feature('inventario') || feature('gestion_bomberos'))
            <x-ui.button variant="success" size="md" icon="fas fa-file-excel" href="{{ route('admin.volunteers.import') }}">
                Importar Excel
            </x-ui.button>
            @endif
            <x-ui.button variant="secondary" size="md" icon="fas fa-shield-halved" href="{{ route('admin.specialties.index') }}">
                Especialidades
            </x-ui.button>
            @if(!plan_exceeded('guardias'))
            <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.volunteers.create') }}">
                Nuevo Voluntario
            </x-ui.button>
            @else
            <x-ui.alert type="warning" icon="fas fa-exclamation-triangle" class="!py-2 !px-4">
                Límite de guardias alcanzado
            </x-ui.alert>
            @endif
        </div>
    </x-ui.page-header>

    @if($showSpecialtiesSetupAlert ?? false)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">Configura las especialidades de tu compañía</p>
                    <p class="mt-1 text-sm text-amber-800">
                        Las especialidades actuales son iniciales. Puedes crear, editar o desactivar las que correspondan a tu compañía antes de cargar o editar voluntarios.
                    </p>
                </div>
                <a href="{{ route('admin.specialties.index') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    Configurar especialidades
                </a>
            </div>
        </div>
    @endif

    <!-- Buscador Mejorado -->
    <x-ui.card class="mb-8">
        <form action="{{ route('admin.volunteers.index') }}" method="GET" class="relative" id="volunteer-search-form">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#475569]"></i>
                    <input type="text" name="search" value="{{ request('search') }}" id="volunteer-search-input"
                        placeholder="Buscar por nombre, RUT o cargo..." 
                        class="bg-white border border-slate-200 text-[#1e293b] placeholder-[#475569] rounded-xl min-h-[44px] px-4 py-3 pl-11 text-sm focus:border-[#1e293b] focus:ring-2 focus:ring-[#1e293b]/10 focus:outline-none w-full"
                        autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.volunteers.index') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#475569] hover:text-[#1e293b] p-1">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-search" class="sm:w-auto w-full">
                    Buscar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-8">
            {{ session('success') }}
        </x-ui.alert>
    @endif
    
    @if(session('warning'))
        <x-ui.alert type="warning" icon="fas fa-exclamation-triangle" class="mb-8">
            {{ session('warning') }}
        </x-ui.alert>
    @endif

    @if($volunteers->isEmpty())
        <x-ui.empty-state icon="fas fa-users" title="No se encontraron voluntarios" message="Intenta ajustar los filtros de búsqueda o agrega un nuevo voluntario." action-text="Nuevo Voluntario" action-url="{{ route('admin.volunteers.create') }}" />
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#9fb0c3]">
                    <thead class="bg-white">
                        <tr>
                            <th scope="col" class="px-3 md:px-6 py-4 text-left w-12">
                                <input type="checkbox" id="select-all" class="rounded border-slate-200 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4">
                            </th>
                            <th scope="col" class="px-3 md:px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Voluntario</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider hidden md:table-cell">Cargo / Rol</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider hidden lg:table-cell">N° Registro</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider hidden lg:table-cell">Especialidades</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider hidden md:table-cell">Guardia</th>
                            <th scope="col" class="px-3 md:px-6 py-4 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#9fb0c3]" id="volunteer-table-body">
                        @foreach($volunteers as $volunteer)
                            <tr class="hover:bg-white transition-colors">
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $volunteer->id }}" class="volunteer-checkbox rounded border-slate-200 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5">
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($volunteer->photo_path)
                                            <img src="{{ route('media', $volunteer->photo_path) }}" class="flex-shrink-0 h-10 w-10 rounded-full object-cover border border-slate-200 shadow-sm" alt="Foto">
                                        @else
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-white flex items-center justify-center text-[#1e293b] font-bold border border-slate-200 shadow-sm text-sm">
                                                {{ substr($volunteer->nombres, 0, 1) }}{{ substr($volunteer->apellido_paterno, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-[#1e293b] flex items-center gap-2">
                                                <span>{{ $volunteer->nombres }} {{ $volunteer->apellido_paterno }}</span>
                                                @if($volunteer->es_permanente)
                                                    <x-ui.badge variant="success" size="xs">Permanente</x-ui.badge>
                                                @endif
                                            </div>
                                            <div class="text-xs text-[#475569] font-mono">{{ $volunteer->rut ?? 'S/RUT' }}</div>

                                            <div class="md:hidden mt-1 text-[11px] text-[#475569] font-semibold uppercase tracking-wider">
                                                {{ $volunteer->cargo_texto ?? '-' }}
                                            </div>
                                            <div class="md:hidden text-[10px] text-[#475569] font-medium uppercase tracking-wide">
                                                {{ $volunteer->es_jefe_guardia ? 'Jefe de Guardia' : '-' }}
                                            </div>

                                            <div class="md:hidden mt-1 flex flex-wrap gap-1">
                                                @foreach($volunteer->specialties as $specialty)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border" style="color: {{ $specialty->color }}; border-color: {{ $specialty->color }}33; background-color: {{ $specialty->color }}14;">
                                                        {{ $specialty->name }}
                                                    </span>
                                                @endforeach
                                                @if($volunteer->specialties->isEmpty())
                                                    <span class="text-xs text-[#475569] italic">-</span>
                                                @endif
                                            </div>

                                            <div class="md:hidden mt-1">
                                                @if($volunteer->guardia_id)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ $volunteer->guardia->name }}
                                                    </span>
                                                @else
                                                    <span class="text-[#475569] text-xs italic">Sin asignar</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                    <div class="text-sm text-[#1e293b] font-medium">{{ $volunteer->cargo_texto ?? '-' }}</div>
                                    <div class="text-xs text-[#475569]">
                                        {{ $volunteer->es_jefe_guardia ? 'Jefe de Guardia' : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                    <div class="text-sm text-[#475569] flex flex-col gap-1">
                                        @if($volunteer->numero_registro)
                                            <span class="flex items-center"><i class="fas fa-id-card text-[#475569] mr-2 w-4"></i> {{ $volunteer->numero_registro }}</span>
                                        @else
                                            <span class="text-[#475569]">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($volunteer->specialties as $specialty)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border" style="color: {{ $specialty->color }}; border-color: {{ $specialty->color }}33; background-color: {{ $specialty->color }}14;">
                                                {{ $specialty->name }}
                                            </span>
                                        @endforeach
                                        @if($volunteer->specialties->isEmpty())
                                            <span class="text-xs text-[#475569] italic">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[#475569] hidden md:table-cell">
                                    @if($volunteer->guardia_id)
                                        <x-ui.badge variant="success" size="sm">{{ $volunteer->guardia->name }}</x-ui.badge>
                                    @else
                                        <span class="text-[#475569] text-xs">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.volunteers.edit', $volunteer->id) }}" class="text-[#475569] hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.volunteers.destroy', $volunteer->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar a {{ $volunteer->nombres }}? Esta acción no se puede deshacer.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#475569] hover:text-red-600 transition-colors p-1" title="Eliminar">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación Footer -->
            <div class="bg-white px-6 py-4 border-t border-slate-200">
                {{ $volunteers->appends(request()->only('search'))->links() }}
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('volunteer-search-input');
                const form = document.getElementById('volunteer-search-form');
                if (input && form) {
                    let t = null;
                    input.addEventListener('input', function() {
                        clearTimeout(t);
                        t = setTimeout(() => {
                            form.submit();
                        }, 450);
                    });
                }

                const selectAll = document.getElementById('select-all');
                const checkboxes = document.querySelectorAll('.volunteer-checkbox');
                const btnBulkDelete = document.getElementById('btn-bulk-delete');
                const selectedCountSpan = document.getElementById('selected-count');

                function updateBulkActionUI() {
                    const selected = document.querySelectorAll('.volunteer-checkbox:checked').length;
                    if (selectedCountSpan) {
                        selectedCountSpan.textContent = selected;
                    }
                    if (btnBulkDelete) {
                        if (selected > 0) {
                            btnBulkDelete.classList.remove('hidden');
                            btnBulkDelete.style.display = 'inline-flex';
                        } else {
                            btnBulkDelete.style.display = 'none';
                        }
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        const isChecked = this.checked;
                        checkboxes.forEach(cb => {
                            cb.checked = isChecked;
                        });
                        updateBulkActionUI();
                    });
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        updateBulkActionUI();

                        if (selectAll) {
                            if (!this.checked) {
                                selectAll.checked = false;
                            } else {
                                const allChecked = Array.from(checkboxes).every(c => c.checked);
                                if (allChecked) selectAll.checked = true;
                            }
                        }
                    });
                });

                window.confirmBulkDelete = function() {
                    const selectedIds = Array.from(document.querySelectorAll('.volunteer-checkbox:checked'))
                        .map(cb => cb.value);

                    if (selectedIds.length === 0) return;

                    if (confirm('¿Estás seguro de que deseas eliminar ' + selectedIds.length + ' voluntarios seleccionados? Esta acción es irreversible.')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.volunteers.bulk_destroy") }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';
                        form.appendChild(methodField);

                        selectedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = id;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }
                };
            });
        </script>
    @endif
@endsection

@if(auth()->check() && in_array(auth()->user()->role, ['capitan', 'super_admin']))
@push('modals')
    <div id="purgeModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 sm:p-6">
            <div data-modal-dialog class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-white">
                    <div class="text-sm font-bold text-[#1e293b]">Eliminar todos los voluntarios</div>
                    <div class="mt-2 text-sm text-[#475569]">Esta acción es irreversible. Para confirmar escribe <span class="font-bold">ELIMINAR TODO</span>.</div>
                </div>
                <form method="POST" action="{{ route('admin.volunteers.purge') }}" class="p-5">
                    @csrf
                    @method('DELETE')
                    <input type="text" name="confirm_text" class="form-input" placeholder="ELIMINAR TODO" required>
                    <div class="mt-4 flex gap-2">
                        <x-ui.button type="button" variant="secondary" size="md" onclick="closePurgeModal()" class="w-1/2">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="danger" size="md" class="w-1/2">Eliminar todo</x-ui.button>
                    </div>
                </form>
            </div>
    </div>
@endpush

@push('scripts')
<script>
    window.openPurgeModal = function () {
        const modal = document.getElementById('purgeModal');
        if (!modal) return;
        modal.classList.remove('hidden');
    };

    window.closePurgeModal = function () {
        const modal = document.getElementById('purgeModal');
        if (!modal) return;
        modal.classList.add('hidden');
    };
</script>
@endpush
@endif
