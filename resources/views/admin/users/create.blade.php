@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto pt-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Nuevo Usuario</h1>
                <p class="text-slate-500 text-sm mt-1">Crea una cuenta con acceso al sistema</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Usuario (opcional)</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Rol</label>
                        <select name="role" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Seleccionar rol...</option>
                            <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="capitania" {{ old('role') === 'capitania' ? 'selected' : '' }}>Capitanía</option>
                            <option value="guardia" {{ old('role') === 'guardia' ? 'selected' : '' }}>Cuenta de Guardia</option>
                            <option value="jefe_guardia" {{ old('role') === 'jefe_guardia' ? 'selected' : '' }}>Jefe de Guardia</option>
                            <option value="inventario" {{ old('role') === 'inventario' ? 'selected' : '' }}>Inventario</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Rol del sistema (opcional)</label>
                        <select name="role_id"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                            <option value="" {{ old('role_id') ? '' : 'selected' }}>Sin asignar</option>
                            @foreach(($roles ?? collect()) as $r)
                                <option value="{{ $r->id }}" {{ (string)old('role_id') === (string)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->slug }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Guardia (opcional)</label>
                        <select name="guardia_id"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                            <option value="" {{ old('guardia_id') ? '' : 'selected' }}>Sin asignar</option>
                            @foreach($guardias as $guardia)
                                <option value="{{ $guardia->id }}" {{ (string)old('guardia_id') === (string)$guardia->id ? 'selected' : '' }}>
                                    {{ $guardia->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Contraseña</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                        <p class="text-xs text-slate-500 mt-2">Mínimo 8 caracteres.</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
