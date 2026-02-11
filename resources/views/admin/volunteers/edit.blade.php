@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8">
        <!-- Header con navegación -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Editar Voluntario</h1>
                <p class="text-gray-500 mt-1 text-sm">Actualizando información de: <span class="font-semibold text-blue-600">{{ $volunteer->nombres }} {{ $volunteer->apellido_paterno }}</span></p>
            </div>
            <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 font-medium transition-colors bg-white px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver al listado
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <!-- Barra superior decorativa -->
            <div class="h-2 bg-red-700"></div>

            <form action="{{ route('admin.volunteers.update', $volunteer->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('PUT')

                <!-- Sección 1: Identificación Personal -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-700">
                            <i class="fas fa-id-card text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Identificación Personal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="nombres" value="{{ old('nombres', $volunteer->nombres) }}" required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $volunteer->apellido_paterno) }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Apellido Materno</label>
                            <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $volunteer->apellido_materno) }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">RUT <span class="text-red-500">*</span></label>
                            <input type="text" name="rut" value="{{ old('rut', $volunteer->rut) }}" placeholder="12.345.678-9"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-700 font-medium">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Cargo</label>
                            <div class="relative" id="cargoComboboxEdit">
                                <div class="relative">
                                    <input type="text" name="cargo_texto" value="{{ old('cargo_texto', $volunteer->cargo_texto) }}" autocomplete="off" id="cargoInputEdit"
                                        class="w-full px-4 py-2.5 pr-11 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                                    <button type="button" id="cargoToggleEdit" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div id="cargoListEdit" class="absolute z-30 mt-2 w-full bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden hidden">
                                    <div class="max-h-56 overflow-auto" id="cargoOptionsEdit"></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Portátil</label>
                            <input type="text" name="numero_portatil" value="{{ old('numero_portatil', $volunteer->numero_portatil) }}" placeholder="364 / 37-D"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Fecha Cumpleaños</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($volunteer->fecha_nacimiento)->format('Y-m-d')) }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-600">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Email</label>
                            <input type="email" name="correo" value="{{ old('correo', $volunteer->correo) }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        </div>

                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Foto</label>
                            <div class="flex items-center gap-4">
                                @if($volunteer->photo_path)
                                    <img src="{{ asset('storage/'.$volunteer->photo_path) }}" class="w-14 h-14 rounded-full object-cover border border-slate-200" alt="Foto">
                                @else
                                    <div class="w-14 h-14 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300 shadow-sm text-sm">
                                        {{ substr($volunteer->nombres, 0, 1) }}{{ substr($volunteer->apellido_paterno, 0, 1) }}
                                    </div>
                                @endif

                                <input type="file" name="photo" accept="image/*"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            </div>
                            @if($volunteer->photo_path)
                                <button type="submit" form="delete-volunteer-photo-form" onclick="return confirm('¿Eliminar la foto?');" class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 bg-red-50 text-red-700 font-bold hover:bg-red-100 text-xs">
                                    <i class="fas fa-trash-can"></i>
                                    Eliminar foto
                                </button>
                            @endif
                            @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos Institucionales -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-red-100 p-2 rounded-lg text-red-700">
                            <i class="fas fa-helmet-safety text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Datos Institucionales</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Fecha Ingreso</label>
                            <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', optional($volunteer->fecha_ingreso)->format('Y-m-d')) }}" 
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all text-slate-600">
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-wide">Guardia Asignada</label>
                            <div class="relative">
                                <select name="guardia_id" class="w-full appearance-none px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all bg-white">
                                    <option value="">Sin Asignar</option>
                                    @foreach($guardias as $guardia)
                                        <option value="{{ $guardia->id }}" {{ old('guardia_id', $volunteer->guardia_id) == $guardia->id ? 'selected' : '' }}>
                                            {{ $guardia->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                                <input type="checkbox" name="fuera_de_servicio" value="1" {{ old('fuera_de_servicio', $volunteer->fuera_de_servicio) ? 'checked' : '' }} class="rounded text-red-600 focus:ring-red-500 h-5 w-5 border-slate-300">
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Fuera de servicio</div>
                                    <div class="text-xs text-slate-500">No aparecerá en listas operativas (turno, emergencias, academias, reemplazos, refuerzos).</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Permisos y Roles Técnicos -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-3">
                        <div class="bg-yellow-100 p-2 rounded-lg text-yellow-700">
                            <i class="fas fa-user-shield text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Cualidades Técnicas</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h4 class="font-semibold text-slate-700 mb-4 flex items-center">
                                <i class="fas fa-tools mr-2 text-slate-400"></i> Especialidades Técnicas
                            </h4>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-blue-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="es_conductor" value="1" {{ $volunteer->es_conductor ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-700">Conductor</span>
                                        <span class="block text-xs text-slate-500">Autorizado para conducir máquinas</span>
                                    </div>
                                    <i class="fas fa-truck ml-auto text-blue-500"></i>
                                </label>
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-orange-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="es_operador_rescate" value="1" {{ $volunteer->es_operador_rescate ? 'checked' : '' }} class="rounded text-orange-600 focus:ring-orange-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-orange-700">Operador Rescate</span>
                                        <span class="block text-xs text-slate-500">Especialista en rescate vehicular</span>
                                    </div>
                                    <i class="fas fa-car-crash ml-auto text-orange-500"></i>
                                </label>
                                <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-red-400 cursor-pointer transition-all shadow-sm group">
                                    <input type="checkbox" name="es_asistente_trauma" value="1" {{ $volunteer->es_asistente_trauma ? 'checked' : '' }} class="rounded text-red-600 focus:ring-red-500 h-5 w-5 border-slate-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-slate-800 group-hover:text-red-700">Asistente Trauma</span>
                                        <span class="block text-xs text-slate-500">Capacitación prehospitalaria</span>
                                    </div>
                                    <i class="fas fa-medkit ml-auto text-red-500"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('admin.volunteers.index') }}" class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center uppercase tracking-wide text-sm">
                        <i class="fas fa-save mr-2"></i> Actualizar Voluntario
                    </button>
                </div>
            </form>

            <form id="delete-volunteer-photo-form" method="POST" action="{{ route('admin.volunteers.photo.destroy', $volunteer->id) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        (function() {
            const cargos = [
                'Honorario', 'Director', 'Secretario', 'Tesorero', 'Capitán', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Teniente 4',
                'Ayudante', 'Ayudante 1', 'Ayudante 2', 'Ayudante 3', 'Pro Secretario', 'Pro Tesorero', 'Administrativo'
            ];

            const root = document.getElementById('cargoComboboxEdit');
            if (!root) return;

            const input = document.getElementById('cargoInputEdit');
            const toggle = document.getElementById('cargoToggleEdit');
            const list = document.getElementById('cargoListEdit');
            const options = document.getElementById('cargoOptionsEdit');

            let filtered = cargos.slice();
            let activeIndex = -1;

            const open = () => {
                list.classList.remove('hidden');
            };

            const close = () => {
                list.classList.add('hidden');
                activeIndex = -1;
            };

            const render = () => {
                options.innerHTML = '';

                if (filtered.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'px-4 py-2.5 text-sm text-slate-500';
                    empty.textContent = 'Sin resultados';
                    options.appendChild(empty);
                    return;
                }

                filtered.forEach((value, idx) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 focus:bg-slate-50 focus:outline-none text-slate-700';
                    item.textContent = value;
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        input.value = value;
                        close();
                    });
                    item.addEventListener('mousemove', () => {
                        activeIndex = idx;
                        highlight();
                    });
                    options.appendChild(item);
                });

                highlight();
            };

            const highlight = () => {
                const children = options.querySelectorAll('button');
                children.forEach((el, i) => {
                    el.classList.toggle('bg-slate-50', i === activeIndex);
                });
            };

            const applyFilter = () => {
                const q = (input.value || '').trim().toLowerCase();
                filtered = q ? cargos.filter(c => c.toLowerCase().includes(q)) : cargos.slice();
                activeIndex = filtered.length ? 0 : -1;
                render();
                open();
            };

            input.addEventListener('focus', () => {
                applyFilter();
            });

            input.addEventListener('input', () => {
                applyFilter();
            });

            toggle.addEventListener('click', () => {
                if (list.classList.contains('hidden')) {
                    input.focus();
                    applyFilter();
                } else {
                    close();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (list.classList.contains('hidden') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    applyFilter();
                    e.preventDefault();
                    return;
                }

                if (e.key === 'Escape') {
                    close();
                    return;
                }

                if (e.key === 'ArrowDown') {
                    if (filtered.length) {
                        activeIndex = Math.min(filtered.length - 1, activeIndex + 1);
                        highlight();
                    }
                    e.preventDefault();
                    return;
                }

                if (e.key === 'ArrowUp') {
                    if (filtered.length) {
                        activeIndex = Math.max(0, activeIndex - 1);
                        highlight();
                    }
                    e.preventDefault();
                    return;
                }

                if (e.key === 'Enter') {
                    if (!list.classList.contains('hidden') && filtered.length && activeIndex >= 0) {
                        input.value = filtered[activeIndex];
                        close();
                        e.preventDefault();
                    }
                }
            });

            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) close();
            });

            render();
        })();
    </script>
@endsection
