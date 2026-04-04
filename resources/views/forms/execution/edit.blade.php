@extends('layouts.modern')

@section('title', 'Editar Formulario')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('forms.execution.index') }}" 
           class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Formularios
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Editar {{ $submission->template->nombre }}</h1>
        <p class="text-gray-600 mt-2">Guarda los cambios de tu borrador</p>
    </div>

    <form id="draft-form" method="POST" action="{{ route('forms.execution.update', ['submission' => $submission->id]) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="space-y-6">
                @foreach($submission->template->estructura as $index => $campo)
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
                                       name="campo_{{ $index }}" 
                                       value="{{ old('campo_'.$index, $submission->data_json[$campo['nombre']] ?? '') }}"
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('number')
                                <input type="number" 
                                       name="campo_{{ $index }}" 
                                       value="{{ old('campo_'.$index, $submission->data_json[$campo['nombre']] ?? '') }}"
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('textarea')
                                <textarea name="campo_{{ $index }}" 
                                          {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Ingresa {{ $campo['nombre'] }}">{{ old('campo_'.$index, $submission->data_json[$campo['nombre']] ?? '') }}</textarea>
                                @break

                            @case('select')
                                <select name="campo_{{ $index }}" 
                                        {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecciona una opción</option>
                                    @if(isset($campo['opciones']) && is_array($campo['opciones']))
                                        @foreach($campo['opciones'] as $opcion)
                                            <option value="{{ $opcion }}" @selected(old('campo_'.$index, $submission->data_json[$campo['nombre']] ?? '') == $opcion)>
                                                {{ $opcion }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @break

                            @case('checkbox')
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="campo_{{ $index }}" 
                                           value="1"
                                           @checked(old('campo_'.$index, $submission->data_json[$campo['nombre']] ?? false))
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 text-sm text-gray-700">
                                        {{ $campo['requerido'] ?? false ? 'Sí' : 'Marcar si aplica' }}
                                    </label>
                                </div>
                                @break
                        @endswitch

                        @error("campo_{$index}")
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('forms.execution.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-white transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Guardar Borrador
                </button>
                <button type="button" 
                        id="finalize-btn"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar formulario
                </button>
            </div>
        </div>
    </form>

    <!-- Formulario oculto para envío final -->
    <form id="finalize-form" method="POST" action="{{ route('forms.execution.finalize', ['submission' => $submission->id]) }}" class="hidden">
        @csrf
    </form>

    <script>
    document.getElementById('finalize-btn').addEventListener('click', function () {
        if (!confirm('¿Estás seguro que deseas enviar este formulario definitivamente? Una vez enviado, no podrá ser modificado.')) {
            return;
        }

        const draftForm = document.getElementById('draft-form');
        const finalizeForm = document.getElementById('finalize-form');

        // Limpiar inputs clonados anteriores
        finalizeForm.querySelectorAll('input[type="hidden"][data-cloned="true"]').forEach(el => el.remove());

        // Clonar todos los inputs válidos
        const elements = draftForm.querySelectorAll('input, textarea, select');

        elements.forEach((el) => {
            // Ignorar campos que no deben enviarse
            if (!el.name || el.name === '_token' || el.name === '_method') {
                return;
            }

            // Ignorar botones
            if ((el.type === 'submit') || (el.type === 'button')) {
                return;
            }

            // Ignorar checkboxes no marcados
            if ((el.type === 'checkbox') && !el.checked) {
                return;
            }

            // Crear input hidden con el valor
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = el.name;
            hidden.value = (el.type === 'checkbox') ? '1' : el.value;
            hidden.setAttribute('data-cloned', 'true');

            finalizeForm.appendChild(hidden);
        });

        // Enviar formulario de finalización
        finalizeForm.submit();
    });
    </script>
</div>
@endsection
