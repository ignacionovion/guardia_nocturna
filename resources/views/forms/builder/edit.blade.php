@extends('layouts.app')

@section('title', 'Editar Plantilla de Formulario')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Editar Plantilla</h1>
        <p class="text-gray-600 mt-2">Modifica tu formulario personalizado</p>
    </div>

    <form action="{{ route('forms.builder.update', $template) }}" method="POST" id="formBuilder">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la Plantilla
                    </label>
                    <input type="text" name="nombre" required value="{{ $template->nombre }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ej: Formulario de Incidentes">
                    @error('nombre')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Estado
                    </label>
                    <div class="flex items-center">
                        <input type="checkbox" name="activo" value="1" {{ $template->activo ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Activo</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Campos del Formulario</h2>
                <button type="button" onclick="agregarCampo()" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Agregar Campo
                </button>
            </div>

            <div id="camposContainer" class="space-y-4">
                @foreach($template->estructura as $index => $campo)
                    <div class="border border-gray-200 rounded-lg p-4 campo-item" data-index="{{ $index }}">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Campo #{{ $index + 1 }}</h3>
                            <button type="button" onclick="eliminarCampo(this)" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Campo
                                </label>
                                <input type="text" name="estructura[{{ $index }}][nombre]" required
                                       value="{{ $campo['nombre'] }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Nombre Completo">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de Campo
                                </label>
                                <select name="estructura[{{ $index }}][tipo]" onchange="actualizarOpciones(this)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="text" {{ $campo['tipo'] === 'text' ? 'selected' : '' }}>Texto</option>
                                    <option value="number" {{ $campo['tipo'] === 'number' ? 'selected' : '' }}>Número</option>
                                    <option value="textarea" {{ $campo['tipo'] === 'textarea' ? 'selected' : '' }}>Área de Texto</option>
                                    <option value="select" {{ $campo['tipo'] === 'select' ? 'selected' : '' }}>Selección</option>
                                    <option value="checkbox" {{ $campo['tipo'] === 'checkbox' ? 'selected' : '' }}>Casilla de Verificación</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="estructura[{{ $index }}][requerido]" value="1"
                                       {{ ($campo['requerid'] ?? $campo['requerido'] ?? false) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Campo obligatorio</label>
                            </div>
                        </div>
                        
                        <div id="opciones_{{ $index }}" class="mt-4 {{ $campo['tipo'] === 'select' ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Opciones de Selección
                            </label>
                            <div class="space-y-2" id="opcionesContainer_{{ $index }}">
                                @if($campo['tipo'] === 'select' && isset($campo['opciones']))
                                    @foreach($campo['opciones'] as $opcion)
                                        <div class="flex gap-2">
                                            <input type="text" name="estructura[{{ $index }}][opciones][]" 
                                                   value="{{ $opcion }}"
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Opción">
                                            <button type="button" onclick="this.parentElement.remove()" 
                                                    class="bg-red-200 text-red-700 px-3 py-2 rounded hover:bg-red-300">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex gap-2">
                                        <input type="text" name="estructura[{{ $index }}][opciones][]" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Opción 1">
                                        <button type="button" onclick="agregarOpcion({{ $index }})" 
                                                class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-8">
            <a href="{{ route('forms.builder.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Actualizar Plantilla
            </button>
        </div>
    </form>
</div>

<script>
let campoIndex = {{ count($template->estructura) }};

function agregarCampo(tipo = 'text') {
    campoIndex++;
    const container = document.getElementById('camposContainer');
    
    const campoDiv = document.createElement('div');
    campoDiv.className = 'border border-gray-200 rounded-lg p-4 campo-item';
    campoDiv.dataset.index = campoIndex;
    
    campoDiv.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-medium text-gray-900">Campo #${campoIndex + 1}</h3>
            <button type="button" onclick="eliminarCampo(this)" 
                    class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del Campo
                </label>
                <input type="text" name="estructura[${campoIndex}][nombre]" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: Nombre Completo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Campo
                </label>
                <select name="estructura[${campoIndex}][tipo]" onchange="actualizarOpciones(this)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="text" ${tipo === 'text' ? 'selected' : ''}>Texto</option>
                    <option value="number" ${tipo === 'number' ? 'selected' : ''}>Número</option>
                    <option value="textarea" ${tipo === 'textarea' ? 'selected' : ''}>Área de Texto</option>
                    <option value="select" ${tipo === 'select' ? 'selected' : ''}>Selección</option>
                    <option value="checkbox" ${tipo === 'checkbox' ? 'selected' : ''}>Casilla de Verificación</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="flex items-center">
                <input type="checkbox" name="estructura[${campoIndex}][requerido]" value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label class="ml-2 text-sm text-gray-700">Campo obligatorio</label>
            </div>
        </div>
        
        <div id="opciones_${campoIndex}" class="mt-4 ${tipo === 'select' ? '' : 'hidden'}">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Opciones de Selección
            </label>
            <div class="space-y-2" id="opcionesContainer_${campoIndex}">
                <div class="flex gap-2">
                    <input type="text" name="estructura[${campoIndex}][opciones][]" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Opción 1">
                    <button type="button" onclick="agregarOpcion(${campoIndex})" 
                            class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(campoDiv);
}

function eliminarCampo(button) {
    button.closest('.campo-item').remove();
}

function actualizarOpciones(select) {
    const campoDiv = select.closest('.campo-item');
    const index = campoDiv.dataset.index;
    const opcionesDiv = document.getElementById(`opciones_${index}`);
    const tipo = select.value;
    
    if (tipo === 'select') {
        opcionesDiv.classList.remove('hidden');
    } else {
        opcionesDiv.classList.add('hidden');
    }
}

function agregarOpcion(index) {
    const container = document.getElementById(`opcionesContainer_${index}`);
    const opcionDiv = document.createElement('div');
    opcionDiv.className = 'flex gap-2';
    opcionDiv.innerHTML = `
        <input type="text" name="estructura[${index}][opciones][]" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Nueva opción">
        <button type="button" onclick="this.parentElement.remove()" 
                class="bg-red-200 text-red-700 px-3 py-2 rounded hover:bg-red-300">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(opcionDiv);
}
</script>
@endsection
