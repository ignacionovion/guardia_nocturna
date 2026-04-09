@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Unidades de Emergencia" subtitle="Catálogo de carros/unidades disponibles" icon="fas fa-truck" iconVariant="amber">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergencies.index') }}">
            Volver a Emergencias
        </x-ui.button>
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" onclick="openCreateUnitModal()">
            Nueva Unidad
        </x-ui.button>
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.emergency-units.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre o descripción..."
                    class="form-input pl-11 flex-1">

                @if(request('search'))
                    <a href="{{ route('admin.emergency-units.index') }}" class="absolute right-24 text-slate-400 hover:text-slate-600 dark:text-slate-400 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <x-ui.button type="submit" variant="primary" size="md" class="ml-3">
                    Buscar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($units->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-200">
            <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck text-[#475569] text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-[#1e293b]">No hay unidades registradas</h3>
            <p class="text-[#475569] mt-1">Crea una unidad para poder seleccionarla en la emergencia.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-white">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-[#475569] uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#9fb0c3]">
                        @foreach($units as $unit)
                            <tr class="hover:bg-white transition-colors {{ ($unit->status ?? 'active') !== 'active' ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-[#1e293b]">{{ $unit->name }}</td>
                                <td class="px-6 py-4 text-[#1e293b]">{{ $unit->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(($unit->status ?? 'active') === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> EN SERVICIO
                                        </span>
                                    @else
                                        <div class="flex flex-col gap-0.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200 w-fit">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> FUERA DE SERVICIO
                                            </span>
                                            @if($unit->out_of_service_reason)
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium ml-1">{{ $unit->out_of_service_reason === '6-11' ? '6-11' : 'Mantención' }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(($unit->status ?? 'active') === 'active')
                                            <button type="button"
                                                onclick="openDisableModal({{ $unit->id }}, '{{ addslashes($unit->name) }}')"
                                                class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition-colors"
                                                title="Poner fuera de servicio">
                                                <i class="fas fa-ban mr-1"></i> Deshabilitar
                                            </button>
                                        @else
                                            <form action="{{ route('admin.emergency-units.toggle-status', $unit->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs font-bold px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors"
                                                    title="Habilitar unidad">
                                                    <i class="fas fa-check mr-1"></i> Habilitar
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.emergency-units.edit', $unit->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.emergency-units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('¿Eliminar la unidad {{ $unit->name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Eliminar">
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

            <div class="bg-white dark:bg-slate-800 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $units->links() }}
            </div>
        </div>
    @endif

@endsection

@push('modals')
    <!-- Modal Deshabilitar Unidad -->
    <div id="disable-unit-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40">
        <div data-modal-dialog class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <button type="button" onclick="closeDisableModal()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-white dark:bg-slate-800 hover:bg-white flex items-center justify-center text-slate-500 dark:text-slate-400">
                <i class="fas fa-times text-xs"></i>
            </button>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-ban text-red-600"></i>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Poner fuera de servicio</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400" id="disable-unit-name"></div>
                </div>
            </div>
            <form id="disable-unit-form" method="POST" action="">
                @csrf
                <div class="mb-5">
                    <label class="form-label">Motivo</label>
                    <select name="reason" required class="form-input">
                        <option value="">Seleccione motivo...</option>
                        <option value="6-11">6-11</option>
                        <option value="mantencion">Mantención</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <x-ui.button type="button" variant="secondary" size="md" onclick="closeDisableModal()" class="flex-1">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="danger" size="md" class="flex-1">Confirmar</x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <div id="create-unit-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40">
        <div data-modal-dialog class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-xl mx-4 p-6">
            <button type="button" onclick="closeCreateUnitModal()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-white dark:bg-slate-800 hover:bg-white flex items-center justify-center text-slate-500 dark:text-slate-400">
                <i class="fas fa-times text-xs"></i>
            </button>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-truck text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Nueva Unidad</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Agrega una unidad/carro para emergencias</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.emergency-units.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-5">
                    <x-ui.form-group label="Nombre" for="unit-name" :error="$errors->first('name')">
                        <input type="text" id="unit-name" name="name" value="{{ old('name') }}" required class="form-input">
                    </x-ui.form-group>

                    <x-ui.form-group label="Descripción (opcional)" for="unit-desc" :error="$errors->first('description')">
                        <input type="text" id="unit-desc" name="description" value="{{ old('description') }}" class="form-input">
                    </x-ui.form-group>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" size="md" onclick="closeCreateUnitModal()">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">Guardar</x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openCreateUnitModal() {
            const modal = document.getElementById('create-unit-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCreateUnitModal() {
            const modal = document.getElementById('create-unit-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openDisableModal(unitId, unitName) {
            document.getElementById('disable-unit-name').textContent = unitName;
            document.getElementById('disable-unit-form').action = '/admin/emergency-units/' + unitId + '/toggle-status';
            const modal = document.getElementById('disable-unit-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDisableModal() {
            const modal = document.getElementById('disable-unit-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('disable-unit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeDisableModal();
        });

        document.getElementById('create-unit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateUnitModal();
        });

        @if($errors->has('name') || $errors->has('description'))
            openCreateUnitModal();
        @endif
    </script>
@endpush
