@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.page-header title="Editar Usuario" subtitle="Actualiza información y permisos" icon="fas fa-user-pen" iconVariant="blue">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.users.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <form method="POST"
      action="{{ route('admin.users.update', $user->id) }}"
      class="space-y-8"
      x-data="userEditForm({
          initialName: @js(old('name', $user->name)),
          initialUsername: @js(old('username', $user->username)),
          initialRole: @js(old('role', $user->role)),
          initialRoleId: @js(old('role_id', $user->role_id)),
      })">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- IZQUIERDA --}}
        <div class="space-y-6">
            <div class="form-group">
                <label for="name" class="form-label">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input id="name"
                       name="name"
                       type="text"
                       x-model="name"
                       @input="handleNameInput"
                       value="{{ old('name', $user->name) }}"
                       class="form-input"
                       required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="text-red-500">*</span>
                </label>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email', $user->email) }}"
                       class="form-input"
                       required>
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="username" class="form-label">
                    Usuario
                </label>
                <input id="username"
                       name="username"
                       type="text"
                       x-model="username"
                       @input="usernameManuallyEdited = true"
                       class="form-input">
                <p class="form-hint">
                    Se sugiere automáticamente desde el nombre, pero puedes ajustarlo manualmente.
                </p>
                @error('username')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- DERECHA --}}
        <div class="space-y-6">
            <div class="form-group">
                <label for="role" class="form-label">
                    Perfil de Sistema
                </label>
                <select id="role"
                        name="role"
                        x-model="role"
                        @change="handleRoleChange"
                        :disabled="roleId !== '' && roleId !== null"
                        :class="roleId !== '' && roleId !== null ? 'bg-white text-slate-400 cursor-not-allowed' : 'bg-white text-slate-900'"
                        class="form-select">
                    <option value="">Seleccionar perfil...</option>
                    <option value="capitan">CAPITAN</option>
                    <option value="guardia">GUARDIA</option>
                    @if(!in_array(old('role', $user->role), ['capitan', 'guardia', null, ''], true))
                        <option value="{{ old('role', $user->role) }}">
                            {{ strtoupper(str_replace('_', ' ', old('role', $user->role))) }}
                        </option>
                    @endif
                </select>
                <p class="form-hint">
                    Perfiles predefinidos con permisos generales del sistema.
                </p>
                @error('role')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="role_id" class="form-label">
                    Rol Personalizado
                </label>
                <select id="role_id"
                        name="role_id"
                        x-model="roleId"
                        @change="handleRoleIdChange"
                        :disabled="role !== '' && role !== null"
                        :class="role !== '' && role !== null ? 'bg-white text-slate-400 cursor-not-allowed' : 'bg-white text-slate-900'"
                        class="form-select">
                    <option value="">Sin rol personalizado</option>
                    @foreach($roles as $roleItem)
                        <option value="{{ $roleItem->id }}">
                            {{ $roleItem->name }}
                        </option>
                    @endforeach
                </select>
                <p class="form-hint">
                    Si seleccionas un rol personalizado, el perfil de sistema se deshabilita automáticamente.
                </p>
                @error('role_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                <p class="text-sm font-semibold text-amber-800">Regla de acceso</p>
                <p class="mt-1 text-sm text-amber-700">
                    Solo puedes seleccionar uno: <strong>Perfil de Sistema</strong> o <strong>Rol Personalizado</strong>.
                </p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white">
            Cancelar
        </a>

        <button type="submit"
                class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
            Guardar cambios
        </button>
    </div>
</form>

<script>
    function userEditForm({ initialName = '', initialUsername = '', initialRole = '', initialRoleId = '' }) {
        return {
            name: initialName ?? '',
            username: initialUsername ?? '',
            role: initialRole ?? '',
            roleId: initialRoleId ?? '',
            usernameManuallyEdited: false,

            init() {
                this.usernameManuallyEdited = (this.username || '') !== this.slugify(this.name || '');
            },

            slugify(value) {
                return (value || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
                    .replace(/\s+/g, '.')
                    .replace(/[^a-z0-9._-]/g, '')
                    .replace(/\.{2,}/g, '.')
                    .replace(/^\.|\.$/g, '');
            },

            handleNameInput() {
                if (!this.usernameManuallyEdited || !this.username) {
                    this.username = this.slugify(this.name);
                }
            },

            handleRoleChange() {
                if (this.role) {
                    this.roleId = '';
                }
            },

            handleRoleIdChange() {
                if (this.roleId) {
                    this.role = '';
                }
            }
        }
    }
</script>
    </div>
@endsection
