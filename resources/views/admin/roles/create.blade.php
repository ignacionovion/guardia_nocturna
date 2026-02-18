@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Nuevo Rol</h1>
                <p class="text-slate-500 text-sm mt-1">Crea un rol y define permisos por secciones</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        @php
            $sections = [
                'dashboard' => 'Dashboard',
                'guardias' => 'Guardias',
                'dotaciones' => 'Dotaciones',
                'calendario' => 'Calendario',
                'voluntarios' => 'Voluntarios',
                'usuarios' => 'Usuarios',
                'roles' => 'Roles',
                'emergencias' => 'Emergencias',
                'reportes' => 'Reportes',
                'admin_system' => 'Administración del Sistema',
            ];
            $selected = old('permissions', []);
        @endphp

        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.roles.store') }}" class="p-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white"
                            placeholder="ej: operador_guardia">
                        <p class="text-xs text-slate-500 mt-2">Usa minúsculas y guiones bajos. Debe ser único.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Permisos por secciones</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($sections as $key => $label)
                                <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                        class="w-5 h-5 rounded border-slate-300 text-blue-600"
                                        {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                                    <span class="font-semibold text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Esta es una base inicial. Luego conectaremos estos permisos a la navegación y endpoints.</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
