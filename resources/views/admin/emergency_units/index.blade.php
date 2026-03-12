@extends('layouts.modern')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Unidades de Emergencia</h1>
            <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Catálogo de carros/unidades disponibles</p>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            <a href="{{ route('admin.emergencies.index') }}" class="inline-flex items-center bg-slate-700 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Emergencias
            </a>
            <button type="button" onclick="openCreateUnitModal()" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Nueva Unidad
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm mb-8 border border-gray-100 dark:border-slate-800">
        <form action="{{ route('admin.emergency-units.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre o descripción..."
                    class="w-full pl-11 pr-4 py-3 border-gray-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700 dark:text-slate-300">

                @if(request('search'))
                    <a href="{{ route('admin.emergency-units.index') }}" class="absolute right-20 text-gray-400 hover:text-gray-600 dark:text-slate-400 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <button type="submit" class="ml-3 bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    @if($units->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-dashed border-slate-300 dark:border-slate-600">
            <div class="bg-slate-50 dark:bg-slate-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck text-slate-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No hay unidades registradas</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Crea una unidad para poder seleccionarla en la emergencia.</p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200">
                        @foreach($units as $unit)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800 transition-colors {{ ($unit->status ?? 'active') !== 'active' ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">{{ $unit->name }}</td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $unit->description ?? '-' }}</td>
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

            <div class="bg-slate-50 dark:bg-slate-800 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $units->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Deshabilitar Unidad -->
    <div id="disable-unit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <button type="button" onclick="closeDisableModal()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <i class="fas fa-times text-xs"></i>
            </button>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-ban text-red-600"></i>
                </div>
                <div>
                    <div class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wide">Poner fuera de servicio</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400" id="disable-unit-name"></div>
                </div>
            </div>
            <form id="disable-unit-form" method="POST" action="">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Motivo</label>
                    <select name="reason" required class="w-full px-4 py-3 border-2 border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-sm">
                        <option value="">Seleccione motivo...</option>
                        <option value="6-11">6-11</option>
                        <option value="mantencion">Mantención</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeDisableModal()" class="flex-1 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-50 dark:bg-slate-800 transition-colors text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition-colors text-sm">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="create-unit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-xl mx-4 p-6">
            <button type="button" onclick="closeCreateUnitModal()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <i class="fas fa-times text-xs"></i>
            </button>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-truck text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wide">Nueva Unidad</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Agrega una unidad/carro para emergencias</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.emergency-units.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border-2 border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 dark:text-slate-300 bg-white">
                        @error('name')
                            <div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Descripción (opcional)</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="w-full px-4 py-3 border-2 border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 dark:text-slate-300 bg-white">
                        @error('description')
                            <div class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateUnitModal()" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-800 font-semibold transition-colors">Cancelar</button>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

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
@endsection
