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
        <div class="text-center py-16 bg-[#dde6ef] rounded-xl shadow-sm border border-dashed border-[#9fb0c3]">
            <div class="bg-[#c3cfdb] rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-[#475569] text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-[#1e293b]">No hay claves registradas</h3>
            <p class="text-[#475569] mt-1">Crea una clave para poder seleccionarla en el formulario de emergencias.</p>
        </div>
    @else
        <div class="bg-[#dde6ef] rounded-xl shadow-sm border border-[#9fb0c3] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-[#c3cfdb]">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Código</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#e7eef5] divide-y divide-[#9fb0c3]">
                        @foreach($keys as $key)
                            <tr class="hover:bg-[#c3cfdb] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-[#1e293b]">{{ $key->code }}</td>
                                <td class="px-6 py-4 text-[#1e293b]">{{ $key->description }}</td>
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

            <div class="bg-[#c3cfdb] px-6 py-4 border-t border-[#9fb0c3]">
                {{ $keys->links() }}
            </div>
        </div>
    @endif
@endsection
