@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto pt-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Editar Usuario</h1>
                <p class="text-gray-500 text-sm mt-1">Actualiza información y permisos</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario (opcional)</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Rol</label>
                        <select name="role" required
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                            <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="capitania" {{ old('role', $user->role) === 'capitania' ? 'selected' : '' }}>Capitanía</option>
                            <option value="guardia" {{ old('role', $user->role) === 'guardia' ? 'selected' : '' }}>Cuenta de Guardia</option>
                            <option value="jefe_guardia" {{ old('role', $user->role) === 'jefe_guardia' ? 'selected' : '' }}>Jefe de Guardia</option>
                            <option value="inventario" {{ old('role', $user->role) === 'inventario' ? 'selected' : '' }}>Inventario</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Rol del sistema (opcional)</label>
                        <select name="role_id"
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                            <option value="" {{ old('role_id', $user->role_id) ? '' : 'selected' }}>Sin asignar</option>
                            @foreach(($roles ?? collect()) as $r)
                                <option value="{{ $r->id }}" {{ (string)old('role_id', $user->role_id) === (string)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->slug }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Guardia (opcional)</label>
                        <select name="guardia_id"
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                            <option value="" {{ old('guardia_id', $user->guardia_id) ? '' : 'selected' }}>Sin asignar</option>
                            @foreach($guardias as $guardia)
                                <option value="{{ $guardia->id }}" {{ (string)old('guardia_id', $user->guardia_id) === (string)$guardia->id ? 'selected' : '' }}>
                                    {{ $guardia->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nueva contraseña (opcional)</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-colors text-gray-700">
                        <p class="text-xs text-slate-500 mt-2">Dejar vacío para mantener la contraseña actual (mínimo 8 caracteres si se cambia).</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg shadow-sm transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
