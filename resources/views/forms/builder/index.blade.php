@extends('layouts.modern')

@section('title', 'Constructor de Formularios')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Constructor de Formularios</h1>
        <p class="text-gray-600 mt-2">Crea y gestiona plantillas de formularios dinámicos</p>
    </div>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Plantillas de Formularios</h2>
        <a href="{{ route('forms.builder.create') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nueva Plantilla
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

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
                        Fecha
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $template->creator?->name ?? 'Sistema' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $template->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('forms.execution.show', ['template' => $template->id]) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('forms.builder.edit', ['template' => $template->id]) }}" 
                           class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('forms.builder.toggle', ['template' => $template->id]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-{{ $template->activo ? 'yellow' : 'green' }}-600 hover:text-{{ $template->activo ? 'yellow' : 'green' }}-900 mr-3"
                                    title="{{ $template->activo ? 'Desactivar' : 'Activar' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                        <form action="{{ route('forms.builder.duplicate', ['template' => $template->id]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-purple-600 hover:text-purple-900 mr-3"
                                    title="Duplicar">
                                <i class="fas fa-copy"></i>
                            </button>
                        </form>
                        <form action="{{ route('forms.builder.destroy', ['template' => $template->id]) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Estás seguro de eliminar esta plantilla?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-900"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay plantillas creadas aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $templates->links() }}
</div>
@endsection
