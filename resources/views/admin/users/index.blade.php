@extends('layouts.modern')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div class="flex items-center gap-4">
            <div class="icon-box icon-box-indigo icon-box-lg">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h1 class="text-title-lg">Usuarios del Sistema</h1>
                <p class="text-body-sm">Administración de cuentas con acceso al sistema</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            @if(!plan_exceeded('users'))
            <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.users.create') }}">
                Nuevo Usuario
            </x-ui.button>
            @else
            <x-ui.alert type="warning" icon="fas fa-exclamation-triangle" class="!py-2 !px-4">
                Límite de usuarios alcanzado
            </x-ui.alert>
            @endif
            <x-ui.button variant="secondary" size="md" icon="fas fa-user-gear" href="{{ route('admin.roles.index') }}">
                Roles
            </x-ui.button>
        </div>
    </div>

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.users.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre o email..."
                    class="form-input pl-11 flex-1">

                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="absolute right-24 text-slate-400 hover:text-slate-600 dark:text-slate-400 p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <x-ui.button type="submit" variant="primary" size="md" class="ml-3">
                    Buscar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($users->isEmpty())
        <x-ui.empty-state icon="fas fa-user-shield" title="No se encontraron usuarios" message="Intenta ajustar el buscador o crea un nuevo usuario." />
    @else
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr class="text-label">
                            <th scope="col" class="px-6 py-4 text-left">Usuario</th>
                            <th scope="col" class="px-6 py-4 text-left">Rol</th>
                            <th scope="col" class="px-6 py-4 text-left">Guardia</th>
                            <th scope="col" class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="icon-box icon-box-slate icon-box-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                    @if($user->roleEntity)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-100 dark:border-slate-800">
                                                {{ $user->roleEntity->name }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    @if($user->guardia)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $user->guardia->name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar a {{ $user->name }}? Esta acción no se puede deshacer.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Eliminar" @if((int)$user->id === (int)Auth::id()) disabled @endif>
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
                {{ $users->links() }}
            </div>
        </div>
    @endif
@endsection
