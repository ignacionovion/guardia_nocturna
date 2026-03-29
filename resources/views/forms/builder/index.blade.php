@extends('layouts.app')

@section('title', 'Constructor de Formularios')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Constructor de Formularios</h1>
            <p class="text-gray-600 mt-2">Crea plantillas personalizadas para tu organización</p>
        </div>
        <a href="{{ route('forms.builder.create') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nueva Plantilla
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Campos
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Creado por
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $template->nombre }}</div>
                        <div class="text-sm text-gray-500">{{ $template->slug }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ count($template->estructura) }} campos</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($template->activo)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $template->creator?->name ?? 'Sistema' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('forms.execution.show', $template) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3" title="Ver formulario">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('forms.builder.edit', $template) }}" 
                           class="text-indigo-600 hover:text-indigo-900 mr-3" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('forms.builder.duplicate', $template) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-900 mr-3" title="Duplicar">
                                <i class="fas fa-copy"></i>
                            </button>
                        </form>
                        <form action="{{ route('forms.builder.destroy', $template) }}" method="POST" class="inline" 
                              onsubmit="return confirm('¿Estás seguro de eliminar esta plantilla?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No hay plantillas creadas. <a href="{{ route('forms.builder.create') }}" class="text-blue-600 hover:text-blue-900">Crea la primera</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $templates->links() }}
</div>
@endsection
