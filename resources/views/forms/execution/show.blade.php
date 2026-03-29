@extends('layouts.app')

@section('title', $template->nombre)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('forms.execution.index') }}" 
           class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Formularios
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $template->nombre }}</h1>
        <p class="text-gray-600 mt-2">Completa el formulario a continuación</p>
    </div>

    <form action="{{ route('forms.execution.submit', $template) }}" method="POST" id="dynamicForm">
        @csrf
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="space-y-6">
                @foreach($template->estructura as $index => $campo)
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $campo['nombre'] }}
                            @if($campo['requerido'] ?? false)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @switch($campo['tipo'])
                            @case('text')
                                <input type="text" 
                                       name="campo_{{ $campo['nombre'] }}" 
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('number')
                                <input type="number" 
                                       name="campo_{{ $campo['nombre'] }}" 
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('textarea')
                                <textarea name="campo_{{ $campo['nombre'] }}" 
                                          {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Ingresa {{ $campo['nombre'] }}"></textarea>
                                @break

                            @case('select')
                                <select name="campo_{{ $campo['nombre'] }}" 
                                        {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecciona una opción</option>
                                    @if(isset($campo['opciones']) && is_array($campo['opciones']))
                                        @foreach($campo['opciones'] as $opcion)
                                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @break

                            @case('checkbox')
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="campo_{{ $campo['nombre'] }}" 
                                           value="1"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 text-sm text-gray-700">
                                        {{ $campo['requerido'] ?? false ? 'Sí' : 'Marcar si aplica' }}
                                    </label>
                                </div>
                                @break
                        @endswitch

                        @error("campo_{$campo['nombre']}")
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <button type="button" onclick="guardarBorrador()" 
                        class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-save mr-2"></i>Guardar Borrador
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Formulario
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function guardarBorrador() {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    
    // Eliminar validación requerida para guardar borrador
    Array.from(form.elements).forEach(element => {
        if (element.hasAttribute('required')) {
            element.removeAttribute('required');
        }
    });
    
    // Enviar como borrador
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route('forms.execution.draft', $template) }}';
    
    formData.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    });
    
    // Agregar CSRF
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrf);
    
    document.body.appendChild(tempForm);
    tempForm.submit();
}
</script>
@endsection
