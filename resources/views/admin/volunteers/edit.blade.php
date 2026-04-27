@extends('layouts.modern')

@section('content')
<div class="min-h-screen bg-white dark:bg-slate-900">
    <!-- Header Superior -->
    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-200 dark:shadow-none flex items-center justify-center">
                        <i class="fas fa-user-pen text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Editar Voluntario</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $volunteer->nombres }} {{ $volunteer->apellido_paterno }} {{ $volunteer->apellido_materno }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.volunteers.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 font-medium hover:bg-white dark:hover:bg-slate-600 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                    Volver al listado
                </a>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('admin.volunteers.update', $volunteer->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Foto e Info Rapida -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Card de Foto -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                <i class="fas fa-camera text-blue-500"></i>
                                Foto de Perfil
                            </h3>
                            
                            <!-- Preview de Foto -->
                            <div class="flex flex-col items-center">
                                <div id="photoPreviewContainer" class="relative w-40 h-40 mb-4">
                                    @if($volunteer->photo_path)
                                        <img id="photoPreview" 
                                             src="{{ route('media', $volunteer->photo_path) }}" 
                                             class="w-full h-full rounded-2xl object-cover border-4 border-white dark:border-slate-700 shadow-lg"
                                             alt="Foto de {{ $volunteer->nombres }}">
                                    @else
                                        <div id="photoPlaceholder" class="w-full h-full rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 border-4 border-white dark:border-slate-700 shadow-lg flex items-center justify-center">
                                            <span class="text-4xl font-bold text-slate-400 dark:text-slate-500">
                                                {{ strtoupper(substr($volunteer->nombres, 0, 1)) }}{{ strtoupper(substr($volunteer->apellido_paterno, 0, 1)) }}
                                            </span>
                                        </div>
                                        <img id="photoPreview" 
                                             src="" 
                                             class="hidden w-full h-full rounded-2xl object-cover border-4 border-white dark:border-slate-700 shadow-lg"
                                             alt="Preview">
                                    @endif
                                    
                                    <!-- Badge de estado -->
                                    @if($volunteer->fuera_de_servicio)
                                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full shadow-md">
                                            FUERA DE SERVICIO
                                        </span>
                                    @elseif($volunteer->es_permanente)
                                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-md">
                                            PERMANENTE
                                        </span>
                                    @endif
                                </div>

                                <!-- Input de archivo estilizado -->
                                <div class="form-group w-full">
                                    <label for="photoInput" 
                                           class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-all group">
                                        <div class="flex flex-col items-center justify-center py-2">
                                            <i class="fas fa-cloud-arrow-up text-2xl text-slate-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 group-hover:text-blue-600 text-center">
                                                <span class="font-semibold">Clic para subir</span> o arrastra
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-1">PNG, JPG hasta 2MB</p>
                                        </div>
                                        <input id="photoInput" name="photo" type="file" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                                    </label>
                                    @error('photo')
                                        <p class="form-error justify-center">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Boton eliminar foto -->
                                @if($volunteer->photo_path)
                                    <button type="button" 
                                            onclick="document.getElementById('delete-photo-form').submit();"
                                            class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                        <i class="fas fa-trash-can"></i>
                                        Eliminar foto actual
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card de Informacion Rapida -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <h3 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Resumen</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">ID</span>
                                <span class="text-sm font-mono font-medium text-slate-900 dark:text-white">#{{ $volunteer->id }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">RUT</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $volunteer->rut ?: 'Sin RUT' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Cargo</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $volunteer->cargo_texto ?: 'Sin cargo' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-600 dark:text-slate-400">Guardia</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $volunteer->guardia->name ?? 'Sin asignar' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Formulario -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Card: Identificacion Personal -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-id-card text-blue-600 dark:text-blue-400 text-sm"></i>
                                </div>
                                Identificacion Personal
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Nombres -->
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">
                                        Nombres <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nombres" value="{{ old('nombres', $volunteer->nombres) }}" required
                                           class="form-input">
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="form-group">
                                    <label class="form-label">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $volunteer->apellido_paterno) }}"
                                           class="form-input">
                                </div>

                                <!-- Apellido Materno -->
                                <div class="form-group">
                                    <label class="form-label">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $volunteer->apellido_materno) }}"
                                           class="form-input">
                                </div>

                                <!-- RUT -->
                                <div class="form-group">
                                    <label class="form-label">
                                        RUT <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rut" value="{{ old('rut', $volunteer->rut) }}" placeholder="12.345.678-9"
                                           class="form-input font-medium">
                                </div>

                                <!-- Numero de Registro -->
                                <div class="form-group">
                                    <label class="form-label">N de Registro</label>
                                    <input type="text" name="numero_registro" value="{{ old('numero_registro', $volunteer->numero_registro) }}" placeholder="Ej: 611"
                                           class="form-input">
                                </div>

                                <!-- Cargo -->
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Cargo</label>
                                    <div class="relative" id="cargoComboboxEdit">
                                        <div class="relative">
                                            <input type="text" name="cargo_texto" value="{{ old('cargo_texto', $volunteer->cargo_texto) }}" autocomplete="off" id="cargoInputEdit"
                                                   class="form-input pr-10">
                                            <button type="button" id="cargoToggleEdit" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                                <i class="fas fa-chevron-down text-sm"></i>
                                            </button>
                                        </div>
                                        <div id="cargoListEdit" class="absolute z-30 mt-1 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl overflow-hidden hidden">
                                            <div class="max-h-56 overflow-auto" id="cargoOptionsEdit"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Correo Electronico</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i class="fas fa-envelope text-slate-400 text-sm"></i>
                                        </div>
                                        <input type="email" name="correo" value="{{ old('correo', $volunteer->correo) }}"
                                               class="form-input pl-10">
                                    </div>
                                </div>

                                <!-- Fecha Nacimiento -->
                                <div class="form-group">
                                    <label class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($volunteer->fecha_nacimiento)->format('Y-m-d')) }}"
                                           class="form-input">
                                </div>

                                <!-- Portatil -->
                                <div class="form-group">
                                    <label class="form-label">Portatil Asignado</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i class="fas fa-walkie-talkie text-slate-400 text-sm"></i>
                                        </div>
                                        <input type="text" name="numero_portatil" value="{{ old('numero_portatil', $volunteer->numero_portatil) }}" placeholder="364 / 37-D"
                                               class="form-input pl-10">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Datos Institucionales -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-helmet-safety text-red-600 dark:text-red-400 text-sm"></i>
                                </div>
                                Datos Institucionales
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <!-- Fecha Ingreso -->
                                <div class="form-group">
                                    <label class="form-label">Fecha de Ingreso</label>
                                    <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', optional($volunteer->fecha_ingreso)->format('Y-m-d')) }}"
                                           class="form-input">
                                </div>

                                <!-- Guardia Asignada -->
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Guardia Asignada</label>
                                    <div class="relative">
                                        <select name="guardia_id" class="form-select pr-10">
                                            <option value="">Sin Asignar</option>
                                            @foreach($guardias as $guardia)
                                                <option value="{{ $guardia->id }}" {{ old('guardia_id', $volunteer->guardia_id) == $guardia->id ? 'selected' : '' }}>
                                                    {{ $guardia->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Es Permanente -->
                                <div class="form-group">
                                    <label class="form-label">Es Guardian Permanente?</label>
                                    <div class="relative">
                                        <select name="es_permanente" class="form-select pr-10">
                                            <option value="0" {{ old('es_permanente', $volunteer->es_permanente ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('es_permanente', $volunteer->es_permanente ? '1' : '0') === '1' ? 'selected' : '' }}>Si</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fuera de Servicio -->
                                <div class="md:col-span-3">
                                    <label class="flex items-start gap-3 p-4 bg-red-50/50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/30 rounded-xl cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <input type="checkbox" name="fuera_de_servicio" value="1" {{ old('fuera_de_servicio', $volunteer->fuera_de_servicio) ? 'checked' : '' }} 
                                               class="mt-0.5 w-5 h-5 rounded border-red-300 dark:border-red-700 text-red-600 focus:ring-red-500">
                                        <div>
                                            <span class="block text-sm font-semibold text-red-800 dark:text-red-400 uppercase tracking-wide">Fuera de Servicio</span>
                                            <span class="block text-xs text-red-600 dark:text-red-400/80 mt-1">No aparecera en listas operativas (turno, emergencias, academias, reemplazos, refuerzos).</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shield-halved text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                </div>
                                Especialidades del Tenant
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @php $selectedSpecialties = old('specialty_ids', $volunteer->specialties->pluck('id')->all()); @endphp
                                @forelse($specialties as $specialty)
                                    <label class="flex items-center p-3 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-all group">
                                        <input type="checkbox" name="specialty_ids[]" value="{{ $specialty->id }}" {{ in_array($specialty->id, $selectedSpecialties) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3 flex-1 min-w-0">
                                            <span class="block text-sm font-semibold text-slate-800 dark:text-white group-hover:text-indigo-700">{{ $specialty->name }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $specialty->icon }} · {{ $specialty->color }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">No hay especialidades activas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Card: Cualidades Tecnicas -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-shield text-yellow-600 dark:text-yellow-400 text-sm"></i>
                                </div>
                                Cualidades Tecnicas Legacy
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Conductor -->
                                <label class="flex items-center p-4 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-all group">
                                    <input id="es_conductor" type="checkbox" name="es_conductor" value="1" {{ $volunteer->es_conductor ? 'checked' : '' }} 
                                           class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3 flex-1">
                                        <span class="block text-sm font-semibold text-slate-800 dark:text-white group-hover:text-blue-700">Conductor</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Autorizado para conducir maquinas</span>
                                    </div>
                                    <i class="fas fa-truck text-blue-500 text-lg"></i>
                                </label>
                                <input type="hidden" name="conductor_carros_bomba" id="conductor_carros_bomba" value="{{ $volunteer->conductor_carros_bomba ? '1' : '' }}">
                                
                                <!-- Disponibilidad conductor -->
                                <div id="conductor_disponibilidad" class="hidden md:col-span-2 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl">
                                    <div class="flex items-center gap-2 text-sm font-medium text-blue-800 dark:text-blue-300">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="conductor_disponibilidad_text"></span>
                                    </div>
                                </div>

                                <!-- Operador Rescate -->
                                <label class="flex items-center p-4 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-orange-400 hover:bg-orange-50/50 dark:hover:bg-orange-900/10 transition-all group">
                                    <input type="checkbox" name="es_operador_rescate" value="1" {{ $volunteer->es_operador_rescate ? 'checked' : '' }} 
                                           class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-orange-600 focus:ring-orange-500">
                                    <div class="ml-3 flex-1">
                                        <span class="block text-sm font-semibold text-slate-800 dark:text-white group-hover:text-orange-700">Operador Rescate</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Especialista en rescate vehicular</span>
                                    </div>
                                    <i class="fas fa-car-crash text-orange-500 text-lg"></i>
                                </label>

                                <!-- Asistente Trauma -->
                                <label class="flex items-center p-4 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-red-400 hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-all group">
                                    <input type="checkbox" name="es_asistente_trauma" value="1" {{ $volunteer->es_asistente_trauma ? 'checked' : '' }} 
                                           class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-red-600 focus:ring-red-500">
                                    <div class="ml-3 flex-1">
                                        <span class="block text-sm font-semibold text-slate-800 dark:text-white group-hover:text-red-700">Asistente Trauma</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Capacitacion prehospitalaria</span>
                                    </div>
                                    <i class="fas fa-medkit text-red-500 text-lg"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Accion -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('admin.volunteers.index') }}" 
                           class="px-5 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-white dark:hover:bg-slate-700 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all shadow-lg shadow-blue-500/25 flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Form oculto para eliminar foto -->
        <form id="delete-photo-form" method="POST" action="{{ route('admin.volunteers.photo.destroy', $volunteer->id) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<!-- Script para preview de foto -->
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<!-- Script para combobox de cargo -->
<script>
(function() {
    const cargos = [
        'Honorario', 'Director', 'Secretario', 'Tesorero', 'Capitan', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Teniente 4',
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

    const open = () => { list.classList.remove('hidden'); };
    const close = () => { list.classList.add('hidden'); activeIndex = -1; };

    const render = () => {
        options.innerHTML = '';
        if (filtered.length === 0) {
            options.innerHTML = '<div class="px-4 py-2.5 text-sm text-slate-500">Sin resultados</div>';
            return;
        }
        filtered.forEach((value, idx) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full text-left px-4 py-2.5 text-sm hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300';
            item.textContent = value;
            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = value;
                close();
            });
            item.addEventListener('mousemove', () => { activeIndex = idx; highlight(); });
            options.appendChild(item);
        });
        highlight();
    };

    const highlight = () => {
        options.querySelectorAll('button').forEach((el, i) => {
            el.classList.toggle('bg-white dark:bg-slate-800', i === activeIndex);
        });
    };

    const applyFilter = () => {
        const q = (input.value || '').trim().toLowerCase();
        filtered = q ? cargos.filter(c => c.toLowerCase().includes(q)) : cargos.slice();
        activeIndex = filtered.length ? 0 : -1;
        render();
        open();
    };

    input.addEventListener('focus', applyFilter);
    input.addEventListener('input', applyFilter);
    toggle.addEventListener('click', () => {
        list.classList.contains('hidden') ? input.focus() : close();
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'ArrowDown') { activeIndex = Math.min(filtered.length - 1, activeIndex + 1); highlight(); e.preventDefault(); return; }
        if (e.key === 'ArrowUp') { activeIndex = Math.max(0, activeIndex - 1); highlight(); e.preventDefault(); return; }
        if (e.key === 'Enter' && activeIndex >= 0) { input.value = filtered[activeIndex]; close(); e.preventDefault(); }
    });
    document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });
    render();
})();
</script>

<!-- Script para conductor -->
<script>
(function() {
    const driverCheckbox = document.getElementById('es_conductor');
    const driverTypeInput = document.getElementById('conductor_carros_bomba');
    const availabilityWrap = document.getElementById('conductor_disponibilidad');
    const availabilityText = document.getElementById('conductor_disponibilidad_text');

    if (!driverCheckbox || !driverTypeInput || !availabilityWrap || !availabilityText) return;

    const setAvailability = (isCarrosBomba) => {
        driverTypeInput.value = isCarrosBomba ? '1' : '';
        availabilityWrap.classList.remove('hidden');
        availabilityText.textContent = isCarrosBomba 
            ? 'Disponible para unidades B-3 / BR-3 / RX-3' 
            : 'Habilitado solo para vehiculos de comandancia';
    };

    const clearAvailability = () => {
        driverTypeInput.value = '';
        availabilityWrap.classList.add('hidden');
        availabilityText.textContent = '';
    };

    const syncFromCurrent = () => {
        if (!driverCheckbox.checked) { clearAvailability(); return; }
        setAvailability(driverTypeInput.value === '1');
    };

    driverCheckbox.addEventListener('change', () => {
        if (!driverCheckbox.checked) { clearAvailability(); return; }
        showDriverModal();
    });

    let driverModal = document.getElementById('driverModal');
    if (!driverModal) {
        driverModal = document.createElement('div');
        driverModal.id = 'driverModal';
        driverModal.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm';
        driverModal.innerHTML = `
            <div data-modal-dialog class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-truck text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Confirmar Especialidad</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Conductor de carros bomba</p>
                        </div>
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 mb-6">
                        Este voluntario esta <strong>habilitado para conducir carros bomba</strong> (unidades B-3, BR-3, RX-3)?
                    </p>
                    <div class="flex gap-3">
                        <button type="button" id="driverModalNo" class="flex-1 px-4 py-2.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-white dark:hover:bg-slate-800 transition-colors">
                            Solo comandancia
                        </button>
                        <button type="button" id="driverModalYes" class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">
                            Si, carros bomba
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(driverModal);
        document.getElementById('driverModalYes').addEventListener('click', () => { setAvailability(true); hideModal(); });
        document.getElementById('driverModalNo').addEventListener('click', () => { setAvailability(false); hideModal(); });
        driverModal.addEventListener('click', (e) => {
            if (e.target === driverModal) { driverCheckbox.checked = false; clearAvailability(); hideModal(); }
        });
    }

    const showDriverModal = () => { driverModal.classList.remove('hidden'); driverModal.classList.add('flex'); };
    const hideModal = () => { driverModal.classList.add('hidden'); driverModal.classList.remove('flex'); };

    syncFromCurrent();
})();
</script>
@endsection