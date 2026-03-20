@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Usuarios del Sistema" subtitle="Administración de cuentas con acceso al sistema" icon="fas fa-user-shield" iconVariant="red">
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
    </x-ui.page-header>

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.users.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#475569]"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre o email..."
                    class="bg-[#e7eef5] border border-[#9fb0c3] text-[#1e293b] placeholder-[#475569] rounded-xl min-h-[44px] px-4 py-3 pl-11 text-sm focus:border-[#1e293b] focus:ring-2 focus:ring-[#1e293b]/10 focus:outline-none flex-1">

                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="absolute right-24 top-1/2 -translate-y-1/2 text-[#475569] hover:text-[#1e293b] p-2">
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
                <table class="min-w-full divide-y divide-[#9fb0c3]">
                    <thead class="bg-[#c3cfdb]">
                        <tr class="text-label">
                            <th scope="col" class="px-6 py-4 text-left">Usuario</th>
                            <th scope="col" class="px-6 py-4 text-left">Rol</th>
                            <th scope="col" class="px-6 py-4 text-left">Guardia</th>
                            <th scope="col" class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#e7eef5] divide-y divide-[#9fb0c3]">
                        @foreach($users as $user)
                            <tr class="hover:bg-[#c3cfdb] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="icon-box icon-box-slate icon-box-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-[#1e293b]">{{ $user->name }}</div>
                                            <div class="text-xs text-[#475569] font-mono">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="danger" size="sm">{{ str_replace('_', ' ', $user->role) }}</x-ui.badge>
                                    @if($user->roleEntity)
                                        <div class="mt-1">
                                            <x-ui.badge variant="default" size="xs">{{ $user->roleEntity->name }}</x-ui.badge>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[#475569]">
                                    @if($user->guardia)
                                        <x-ui.badge variant="success" size="sm">{{ $user->guardia->name }}</x-ui.badge>
                                    @else
                                        <span class="text-[#475569] text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-[#475569] hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar a {{ $user->name }}? Esta acción no se puede deshacer.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#475569] hover:text-red-600 transition-colors p-1" title="Eliminar" @if((int)$user->id === (int)Auth::id()) disabled @endif>
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
                {{ $users->links() }}
            </div>
        </x-ui.card>
    @endif
@endsection
