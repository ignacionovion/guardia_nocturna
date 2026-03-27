@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto" x-data="userForm()">
        <x-ui.page-header title="Nuevo Usuario" subtitle="Crea una cuenta con acceso al sistema" icon="fas fa-user-plus" iconVariant="blue">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.users.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-8">
                @csrf

                @if(isset($limitData) && !$limitData['can_create'])
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p class="font-bold">{{ $limitData['message'] }}</p>
                        </div>
                    </div>
                @endif

                <fieldset @if(isset($limitData) && !$limitData['can_create']) disabled @endif>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="form-input"
                            x-model="formData.name"
                            @input="generateUsername()">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">El usuario se generará automáticamente</p>
                    </div>

                    <div>
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="form-input"
                            x-model="formData.username">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Autogenerado, editable si es necesario</p>
                    </div>

                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Perfil de Sistema
                        </label>
                        <select name="role" class="form-input">
                            <option value="" {{ old('role') ? '' : 'selected' }}>Seleccionar perfil...</option>
                            <option value="capitan" {{ old('role') === 'capitan' ? 'selected' : '' }}>Capitán (Acceso administrativo completo)</option>
                            <option value="guardia" {{ old('role') === 'guardia' ? 'selected' : '' }}>Guardia (Acceso operativo)</option>
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Perfiles predefinidos con permisos generales de acceso</p>
                    </div>

                    <div>
                        <label class="form-label">
                            Rol Personalizado
                        </label>
                        <select name="role_id" class="form-input">
                            <option value="" {{ old('role_id') ? '' : 'selected' }}>Sin rol personalizado</option>
                            @foreach(($roles ?? collect()) as $r)
                                <option value="{{ $r->id }}" {{ (string)old('role_id') === (string)$r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Roles creados en "Nuevo Rol" con permisos específicos por sección</p>
                        <p class="text-xs text-emerald-600 mt-1">Roles cargados: {{ ($roles ?? collect())->count() }}</p>
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
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-600"></i>
                                <p class="text-sm text-blue-800">
                                    <strong>Contraseña:</strong> Se generará automáticamente de forma segura (12 caracteres) y se mostrará al crear el usuario.
                                </p>
                            </div>
                        </div>
                    </div>
                    </div>
                </fieldset>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.users.index') }}">
                        Cancelar
                    </x-ui.button>

                    <x-ui.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="fas fa-save"
                        :disabled="isset($limitData) && !($limitData['can_create'] ?? true)"
                    >
                        Crear Usuario
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function userForm() {
        return {
            formData: {
                name: '{{ old('name') }}',
                username: '{{ old('username') }}',
                role: '{{ old('role') }}',
                role_id: '{{ old('role_id') }}'
            },
            usernameManuallyEdited: false,

            init() {
                // If username was filled by old() and is different from what would be generated,
                // assume it was manually edited.
                if (this.formData.username && this.formData.username !== this.slugify(this.formData.name)) {
                    this.usernameManuallyEdited = true;
                }
            },

            slugify(value) {
                if (!value) return '';
                const a = 'àáâäæãåāăąçćčđďèéêëēėęěğǵḧîïíīįìłḿñńǹňôöòóœøōõőṕŕřßśšşșťțûüùúūǘůűųẃẍÿýžźż·/_,:;'
                const b = 'aaaaaaaaaacccddeeeeeeeegghiiiiiilmnnnnoooooooooprrsssssttuuuuuuuuuwxyyzzz------'
                const p = new RegExp(a.split('').join('|'), 'g')

                return value.toString().toLowerCase()
                    .replace(/\s+/g, '.') // Replace spaces with - -> changed to .
                    .replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
                    .replace(/&/g, '-and-') // Replace & with 'and'
                    .replace(/[^\w\.]+/g, '') // Remove all non-word chars
                    .replace(/\.\.+/g, '.') // Replace multiple - with single -
                    .replace(/^\.+/, '') // Trim - from start of text
                    .replace(/\.+$/, '') // Trim - from end of text
            },

            generateUsername() {
                if (!this.usernameManuallyEdited) {
                    let newUsername = this.slugify(this.formData.name);
                    if (newUsername.length < 4 && this.formData.name.length >= 2) {
                        newUsername = newUsername + '.user';
                    }
                    this.formData.username = newUsername;
                }
            },

            handleRoleChange() {
                if (this.formData.role) {
                    this.formData.role_id = '';
                }
            },

            handleRoleIdChange() {
                if (this.formData.role_id) {
                    this.formData.role = '';
                }
            }
        }
    }
    </script>
@endsection
