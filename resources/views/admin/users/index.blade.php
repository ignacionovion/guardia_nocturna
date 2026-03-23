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
                                            <div class="text-xs text-[#475569] font-mono">{{ $user->username }}</div>
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
                                        @if(in_array(Auth::user()?->role, ['capitan', 'super_admin']))
                                            <form action="{{ route('admin.users.regenerate-password', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas regenerar la contraseña de {{ $user->name }}?');">
                                                @csrf
                                                <button type="submit" class="text-[#475569] hover:text-amber-600 transition-colors p-1" title="Regenerar contraseña">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>
                                        @endif
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

    @if(session('new_user_credentials') || session('regenerated_password'))
        @php
            $credentials = session('regenerated_password') ?: session('new_user_credentials');
            $isRegenerated = session('regenerated_password') ? true : false;
        @endphp
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.away="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform transition-all">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $isRegenerated ? 'Contraseña Regenerada' : 'Usuario Creado Exitosamente' }}
                        </h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-check-circle text-emerald-600"></i>
                            <p class="text-sm font-semibold text-emerald-800">Credenciales de Acceso</p>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-slate-600 mb-1">Usuario:</p>
                                <div class="bg-white border border-slate-200 rounded px-3 py-2 font-mono text-sm">{{ $credentials['username'] ?? '' }}</div>
                            </div>
                            <div>
                                <p class="text-xs text-slate-600 mb-1">Contraseña:</p>
                                <div class="bg-white border border-slate-200 rounded px-3 py-2 font-mono text-sm">{{ $credentials['password'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Importante</p>
                                <p class="text-xs text-amber-700 mt-1">
                                    Entrega esta contraseña al usuario <strong>{{ $credentials['name'] ?? '' }}</strong>. No se volverá a mostrar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button @click="open = false" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors">
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
