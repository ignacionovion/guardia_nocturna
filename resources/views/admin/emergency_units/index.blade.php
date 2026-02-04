@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Unidades de Emergencia</h1>
            <p class="text-gray-500 text-sm mt-1">Catálogo de carros/unidades disponibles</p>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            <a href="{{ route('admin.emergencies.index') }}" class="inline-flex items-center bg-slate-700 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Emergencias
            </a>
            <a href="{{ route('admin.emergency-units.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Nueva Unidad
            </a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm mb-8 border border-gray-100">
        <form action="{{ route('admin.emergency-units.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre o descripción..."
                    class="w-full pl-11 pr-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">

                @if(request('search'))
                    <a href="{{ route('admin.emergency-units.index') }}" class="absolute right-20 text-gray-400 hover:text-gray-600 p-2">
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
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
            <div class="bg-slate-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck text-slate-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No hay unidades registradas</h3>
            <p class="text-slate-500 mt-1">Crea una unidad para poder seleccionarla en la emergencia.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($units as $unit)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">{{ $unit->name }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $unit->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
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

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $units->links() }}
            </div>
        </div>
    @endif
@endsection
