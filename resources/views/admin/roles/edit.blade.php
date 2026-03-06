@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Editar Rol</h1>
                <p class="text-slate-500 text-sm mt-1">Actualiza nombre, slug y permisos</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        @php
            $sections = [
                'dashboard' => ['Dashboard', 'fa-home', 'bg-blue-50 text-blue-600'],
                'guardias' => ['Guardias', 'fa-shield', 'bg-red-50 text-red-600'],
                'dotaciones' => ['Dotaciones', 'fa-users-gear', 'bg-indigo-50 text-indigo-600'],
                'calendario' => ['Calendario', 'fa-calendar', 'bg-amber-50 text-amber-600'],
                'voluntarios' => ['Voluntarios', 'fa-users', 'bg-emerald-50 text-emerald-600'],
                'usuarios' => ['Usuarios', 'fa-user-shield', 'bg-purple-50 text-purple-600'],
                'roles' => ['Roles', 'fa-user-tag', 'bg-pink-50 text-pink-600'],
                'emergencias' => ['Emergencias', 'fa-truck-medical', 'bg-rose-50 text-rose-600'],
                'reportes' => ['Reportes', 'fa-chart-pie', 'bg-cyan-50 text-cyan-600'],
                'planillas' => ['Planillas', 'fa-table-list', 'bg-teal-50 text-teal-600'],
                'inventario' => ['Inventario', 'fa-boxes-stacked', 'bg-orange-50 text-orange-600'],
                'preventivas' => ['Preventivas', 'fa-clipboard-list', 'bg-lime-50 text-lime-600'],
                'camas' => ['Camas', 'fa-bed', 'bg-sky-50 text-sky-600'],
                'novedades' => ['Novedades', 'fa-bullhorn', 'bg-violet-50 text-violet-600'],
                'limpieza' => ['Limpieza', 'fa-broom', 'bg-yellow-50 text-yellow-600'],
                'academias' => ['Academias', 'fa-chalkboard-user', 'bg-fuchsia-50 text-fuchsia-600'],
                'admin_system' => ['Administración del Sistema', 'fa-gear', 'bg-slate-100 text-slate-600'],
            ];
            $selected = old('permissions', is_array($role->permissions) ? $role->permissions : []);
        @endphp

        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <form method="POST" action="{{ route('admin.roles.update', $role->id) }}" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $role->slug) }}" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-lock text-blue-600"></i>
                            Permisos por secciones
                            <span class="text-xs font-normal text-slate-400 ml-2">Selecciona las áreas a las que este rol tendrá acceso</span>
                        </label>
                        
                        {{-- Categorías organizadas --}}
                        <div class="space-y-6">
                            {{-- Operación --}}
                            <div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    Operación
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach(['guardias', 'dotaciones', 'calendario', 'camas'] as $key)
                                        @php [$label, $icon, $colorClass] = $sections[$key]; @endphp
                                        <label class="group flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                                            <div class="w-8 h-8 rounded-lg {{ $colorClass }} flex items-center justify-center text-sm">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700 group-hover:text-slate-900">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Personal --}}
                            <div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Personal
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach(['voluntarios', 'usuarios', 'roles'] as $key)
                                        @php [$label, $icon, $colorClass] = $sections[$key]; @endphp
                                        <label class="group flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/30 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                                            <div class="w-8 h-8 rounded-lg {{ $colorClass }} flex items-center justify-center text-sm">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700 group-hover:text-slate-900">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Gestión --}}
                            <div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Gestión
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach(['planillas', 'inventario', 'preventivas', 'novedades', 'limpieza', 'academias'] as $key)
                                        @php [$label, $icon, $colorClass] = $sections[$key]; @endphp
                                        <label class="group flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                                            <div class="w-8 h-8 rounded-lg {{ $colorClass }} flex items-center justify-center text-sm">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700 group-hover:text-slate-900">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Reportes y Admin --}}
                            <div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                    Reportes y Administración
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach(['dashboard', 'emergencias', 'reportes', 'admin_system'] as $key)
                                        @php [$label, $icon, $colorClass] = $sections[$key]; @endphp
                                        <label class="group flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 cursor-pointer transition-all">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                class="w-5 h-5 rounded border-slate-300 text-slate-600 focus:ring-slate-500"
                                                {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                                            <div class="w-8 h-8 rounded-lg {{ $colorClass }} flex items-center justify-center text-sm">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700 group-hover:text-slate-900">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
