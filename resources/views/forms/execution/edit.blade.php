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
        <p class="text-gray-600 mt-2">Modifica tu formulario</p>
    </div>

    <form action="{{ route('forms.execution.update', $submission) }}" method="POST" id="dynamicForm">
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
                                       name="campo_{{ $campo['nombre'] }}" 
                                       value="{{ $submission->data[$campo['nombre']] ?? '' }}"
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('number')
                                <input type="number" 
                                       name="campo_{{ $campo['nombre'] }}" 
                                       value="{{ $submission->data[$campo['nombre']] ?? '' }}"
                                       {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ingresa {{ $campo['nombre'] }}">
                                @break

                            @case('textarea')
                                <textarea name="campo_{{ $campo['nombre'] }}" 
                                          {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Ingresa {{ $campo['nombre'] }}">{{ $submission->data[$campo['nombre']] ?? '' }}</textarea>
                                @break

                            @case('select')
                                <select name="campo_{{ $campo['nombre'] }}" 
                                        {{ ($campo['requerido'] ?? false) ? 'required' : '' }}
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecciona una opción</option>
                                    @if(isset($campo['opciones']) && is_array($campo['opciones']))
                                        @foreach($campo['opciones'] as $opcion)
                                            <option value="{{ $opcion }}" {{ ($submission->data[$campo['nombre']] ?? '') === $opcion ? 'selected' : '' }}>
                                                {{ $opcion }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @break

                            @case('checkbox')
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="campo_{{ $campo['nombre'] }}" 
                                           value="1"
                                           {{ ($submission->data[$campo['nombre']] ?? false) ? 'checked' : '' }}
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
                <a href="{{ route('forms.execution.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Formulario
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
