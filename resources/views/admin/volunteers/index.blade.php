@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Voluntarios</h1>
            <p class="text-gray-500 text-sm mt-1">Administración del personal del cuerpo de bomberos</p>
        </div>
        
        <div class="flex flex-wrap gap-3 items-center">
            <!-- Botón de Eliminación Masiva -->
            <button type="button" id="btn-bulk-delete" style="display: none;" class="items-center bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5" onclick="confirmBulkDelete()">
                <i class="fas fa-trash-can mr-2"></i> Eliminar (<span id="selected-count">0</span>)
            </button>

            <a href="{{ route('admin.volunteers.import') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-file-excel mr-2"></i> Importar Excel
            </a>
            <a href="{{ route('admin.volunteers.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Nuevo Voluntario
            </a>
        </div>
    </div>

    <!-- Buscador -->
    <div class="bg-white p-4 rounded-xl shadow-sm mb-8 border border-gray-100">
        <form action="{{ route('admin.volunteers.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Buscar por nombre, RUT o cargo..." 
                    class="w-full pl-11 pr-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                
                @if(request('search'))
                    <a href="{{ route('admin.volunteers.index') }}" class="absolute right-20 text-gray-400 hover:text-gray-600 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                
                <button type="submit" class="ml-3 bg-slate-800 hover:bg-slate-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg mb-8 shadow-sm flex items-center animate-fade-in-down" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="block sm:inline font-medium">{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-r-lg mb-8 shadow-sm flex items-center animate-fade-in-down" role="alert">
            <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
            <span class="block sm:inline font-medium">{{ session('warning') }}</span>
        </div>
    @endif

    @if($volunteers->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
            <div class="bg-slate-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-slate-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No se encontraron voluntarios</h3>
            <p class="text-slate-500 mt-1">Intenta ajustar los filtros de búsqueda o agrega un nuevo voluntario.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-3 md:px-6 py-4 text-left">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5">
                            </th>
                            <th scope="col" class="px-3 md:px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Identificación</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">Cargo / Rol</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Contacto</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Especialidades</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">Guardia</th>
                            <th scope="col" class="px-3 md:px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($volunteers as $volunteer)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $volunteer->id }}" class="volunteer-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5">
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($volunteer->photo_path)
                                            <img src="{{ asset('storage/'.$volunteer->photo_path) }}" class="flex-shrink-0 h-10 w-10 rounded-full object-cover border border-slate-300 shadow-sm" alt="Foto">
                                        @else
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300 shadow-sm text-sm">
                                                {{ substr($volunteer->nombres, 0, 1) }}{{ substr($volunteer->apellido_paterno, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $volunteer->nombres }} {{ $volunteer->apellido_paterno }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ $volunteer->rut ?? 'S/RUT' }}</div>

                                            <div class="md:hidden mt-1 text-[11px] text-slate-600 font-black uppercase tracking-widest">
                                                {{ $volunteer->cargo_texto ?? '-' }}
                                            </div>
                                            <div class="md:hidden text-[10px] text-slate-500 font-medium uppercase tracking-wide">
                                                {{ $volunteer->es_jefe_guardia ? 'Jefe de Guardia' : '-' }}
                                            </div>

                                            <div class="md:hidden mt-1 flex flex-wrap gap-1">
                                                @if($volunteer->es_conductor)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100" title="Conductor">
                                                        <i class="fas fa-car mr-1"></i> Cond
                                                    </span>
                                                @endif
                                                @if($volunteer->es_operador_rescate)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100" title="Operador de Rescate">
                                                        <i class="fas fa-tools mr-1"></i> Resc
                                                    </span>
                                                @endif
                                                @if($volunteer->es_asistente_trauma)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-100" title="Asistente de Trauma">
                                                        <i class="fas fa-medkit mr-1"></i> Trauma
                                                    </span>
                                                @endif
                                                @if(!$volunteer->es_conductor && !$volunteer->es_operador_rescate && !$volunteer->es_asistente_trauma)
                                                    <span class="text-xs text-slate-400 italic">-</span>
                                                @endif
                                            </div>

                                            <div class="md:hidden mt-1">
                                                @if($volunteer->guardia_id)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ $volunteer->guardia->name }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-xs italic">Sin asignar</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                    <div class="text-sm text-slate-900 font-medium">{{ $volunteer->cargo_texto ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $volunteer->es_jefe_guardia ? 'Jefe de Guardia' : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                    <div class="text-sm text-slate-600 flex flex-col gap-1">
                                        @if($volunteer->numero_portatil)
                                            <span class="flex items-center"><i class="fas fa-walkie-talkie text-slate-400 mr-2 w-4"></i> {{ Str::limit($volunteer->numero_portatil, 20) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @if($volunteer->es_conductor)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100" title="Conductor">
                                                <i class="fas fa-car mr-1"></i> Cond
                                            </span>
                                        @endif
                                        @if($volunteer->es_operador_rescate)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100" title="Operador de Rescate">
                                                <i class="fas fa-tools mr-1"></i> Resc
                                            </span>
                                        @endif
                                        @if($volunteer->es_asistente_trauma)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-100" title="Asistente de Trauma">
                                                <i class="fas fa-medkit mr-1"></i> Trauma
                                            </span>
                                        @endif
                                        @if(!$volunteer->es_conductor && !$volunteer->es_operador_rescate && !$volunteer->es_asistente_trauma)
                                            <span class="text-xs text-slate-400 italic">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 hidden md:table-cell">
                                    @if($volunteer->guardia_id)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $volunteer->guardia->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-3 md:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.volunteers.edit', $volunteer->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.volunteers.destroy', $volunteer->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar a {{ $volunteer->nombres }}? Esta acción no se puede deshacer.');" class="inline">
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
            
            <!-- Paginación Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $volunteers->links() }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.volunteer-checkbox');
            const btnBulkDelete = document.getElementById('btn-bulk-delete');
            const selectedCountSpan = document.getElementById('selected-count');

            console.log('Bulk delete script loaded. Checkboxes found:', checkboxes.length);

            function updateBulkActionUI() {
                const selected = document.querySelectorAll('.volunteer-checkbox:checked').length;
                console.log('Selection updated:', selected);
                
                if (selectedCountSpan) {
                    selectedCountSpan.textContent = selected;
                }
                
                if (btnBulkDelete) {
                    if (selected > 0) {
                        btnBulkDelete.classList.remove('hidden'); // Tailwind remove
                        btnBulkDelete.style.display = 'inline-flex'; // Force display
                    } else {
                        btnBulkDelete.style.display = 'none'; // Force hide
                    }
                }
            }

            // Evento "Seleccionar Todo"
            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    const isChecked = this.checked;
                    checkboxes.forEach(cb => {
                        cb.checked = isChecked;
                    });
                    updateBulkActionUI();
                });
            }

            // Eventos individuales
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateBulkActionUI();
                    
                    // Sincronizar "Select All"
                    if(selectAll) {
                        if (!this.checked) {
                            selectAll.checked = false;
                        } else {
                            const allChecked = Array.from(checkboxes).every(c => c.checked);
                            if(allChecked) selectAll.checked = true;
                        }
                    }
                });
            });

            // Función global para el botón de eliminar
            window.confirmBulkDelete = function() {
                const selectedIds = Array.from(document.querySelectorAll('.volunteer-checkbox:checked'))
                                         .map(cb => cb.value);

                if (selectedIds.length === 0) return;

                if (confirm('¿Estás seguro de que deseas eliminar ' + selectedIds.length + ' voluntarios seleccionados? Esta acción es irreversible.')) {
                    // Crear formulario dinámico para enviar DELETE
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
            </div>
        </div>
    @endif
@endsection
