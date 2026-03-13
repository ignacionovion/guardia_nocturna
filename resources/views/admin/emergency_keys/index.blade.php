@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Claves de Emergencia" subtitle="Catálogo de claves (código y descripción)" icon="fas fa-key" iconVariant="amber">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergencies.index') }}">
            Volver a Emergencias
        </x-ui.button>
        <x-ui.button variant="success" size="md" icon="fas fa-file-import" href="{{ route('admin.emergency-keys.import') }}">
            Importar
        </x-ui.button>
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.emergency-keys.create') }}">
            Nueva Clave
        </x-ui.button>
    </x-ui.page-header>

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.emergency-keys.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por código o descripción..."
                    class="form-input pl-11 flex-1">

                @if(request('search'))
                    <a href="{{ route('admin.emergency-keys.index') }}" class="absolute right-24 text-slate-400 hover:text-slate-600 dark:text-slate-400 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <x-ui.button type="submit" variant="primary" size="md" class="ml-3">
                    Buscar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($keys->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-dashed border-slate-300 dark:border-slate-600">
            <div class="bg-slate-50 dark:bg-slate-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-slate-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No hay claves registradas</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Crea una clave para poder seleccionarla en el formulario de emergencias.</p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Código</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200">
                        @foreach($keys as $key)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-slate-900 dark:text-white">{{ $key->code }}</td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $key->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.emergency-keys.edit', $key->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.emergency-keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('¿Eliminar la clave {{ $key->code }}?');" class="inline">
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

            <div class="bg-slate-50 dark:bg-slate-800 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $keys->links() }}
            </div>
        </div>
    @endif
@endsection
