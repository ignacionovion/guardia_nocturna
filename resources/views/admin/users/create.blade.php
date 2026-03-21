@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.page-header title="Nuevo Usuario" subtitle="Crea una cuenta con acceso al sistema" icon="fas fa-user-plus" iconVariant="blue">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.users.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Usuario (opcional)</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Perfil de Sistema <span class="text-red-500">*</span>
                        </label>
                        <select name="role" required
                            class="form-input appearance-none">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Seleccionar perfil...</option>
                            <option value="capitan" {{ old('role') === 'capitan' ? 'selected' : '' }}>Capitán (Acceso administrativo completo)</option>
                            <option value="guardia" {{ old('role') === 'guardia' ? 'selected' : '' }}>Guardia (Acceso operativo)</option>
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Perfiles predefinidos con permisos generales de acceso</p>
                    </div>

                    <div>
                        <label class="form-label">
                            Rol Personalizado <span class="text-xs font-normal text-slate-400">(opcional)</span>
                        </label>
                        <select name="role_id"
                            class="form-input appearance-none">
                            <option value="" {{ old('role_id') ? '' : 'selected' }}>Sin rol personalizado</option>
                            @foreach(($roles ?? collect()) as $r)
                                <option value="{{ $r->id }}" {{ (string)old('role_id') === (string)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Roles creados en "Nuevo Rol" con permisos específicos por sección</p>
                    </div>

                    <div>
                        <label class="form-label">Guardia (opcional)</label>
                        <select name="guardia_id"
                            class="form-input appearance-none">
                            <option value="" {{ old('guardia_id') ? '' : 'selected' }}>Sin asignar</option>
                            @foreach($guardias as $guardia)
                                <option value="{{ $guardia->id }}" {{ (string)old('guardia_id') === (string)$guardia->id ? 'selected' : '' }}>
                                    {{ $guardia->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" required
                            class="form-input">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Mínimo 8 caracteres.</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.users.index') }}">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                        Guardar
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection
