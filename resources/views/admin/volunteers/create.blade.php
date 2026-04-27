@extends('layouts.modern')

@section('content')
    <div class="max-w-5xl mx-auto">
        <x-ui.page-header title="Nuevo Voluntario" subtitle="Complete el formulario para registrar un nuevo miembro en el sistema" icon="fas fa-user-plus" iconVariant="blue">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.volunteers.index') }}">
                Volver al listado
            </x-ui.button>
        </x-ui.page-header>

        @isset($volunteers_plan_usage)
            <div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 flex flex-wrap items-center justify-between gap-2">
                <span>Voluntarios según tu plan: <strong>{{ $volunteers_plan_usage }}</strong></span>
                <a href="{{ tenant_upgrade_url() }}" class="text-amber-700 font-semibold hover:underline">Ver planes y upgrade</a>
            </div>
        @endisset

        @if(isset($limitData) && !$limitData['can_create'])
            <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="font-bold">{{ $limitData['message'] }}</p>
                    </div>
                    <a href="{{ tenant_upgrade_url() }}" class="text-amber-900 font-semibold underline shrink-0">Actualizar plan</a>
                </div>
            </div>
        @endif

        <div class="card-base overflow-hidden">

            <form action="{{ route('admin.volunteers.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf

                <fieldset @if(isset($limitData) && !$limitData['can_create']) disabled @endif>
                <!-- Sección 1: Identificación Personal -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-3">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-700">
                            <i class="fas fa-id-card text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#1e293b]">Identificación Personal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="form-group md:col-span-3">
                            <label class="form-label">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="nombres" value="{{ old('nombres') }}" required
                                class="form-input">
                            @error('nombres') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" required
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">RUT <span class="text-red-500">*</span></label>
                            <input type="text" name="rut" value="{{ old('rut') }}" placeholder="12.345.678-9" required
                                class="form-input font-medium">
                            @error('rut') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Número de Registro</label>
                            <input type="text" name="numero_registro" value="{{ old('numero_registro') }}" placeholder="Ej: 611"
                                class="form-input">
                        </div>
                        <div class="form-group md:col-span-3">
                            <label class="form-label">Cargo <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="cargo_texto" class="form-select pr-10" required>
                                    @foreach($cargos as $cargo)
                                        <option value="{{ $cargo }}" {{ old('cargo_texto', 'bombero') === $cargo ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Str::title($cargo) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            @error('cargo_texto') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Portátil</label>
                            <input type="text" name="numero_portatil" value="{{ old('numero_portatil') }}" placeholder="364 / 37-D"
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Cumpleaños</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                                class="form-input">
                        </div>
                        <div class="form-group md:col-span-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="correo" value="{{ old('correo') }}"
                                class="form-input">
                            @error('correo') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group md:col-span-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="photo" accept="image/*"
                                class="form-file">
                            @error('photo') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos Institucionales -->
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-3">
                        <div class="bg-red-100 p-2 rounded-lg text-red-700">
                            <i class="fas fa-helmet-safety text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#1e293b]">Datos Institucionales</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="form-group">
                            <label class="form-label">Fecha Ingreso</label>
                            <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}" 
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">¿El voluntario es guardián permanente?</label>
                            <div class="relative">
                                <select name="es_permanente" class="form-select pr-10">
                                    <option value="0" {{ old('es_permanente', '0') === '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('es_permanente') === '1' ? 'selected' : '' }}>Sí</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Guardia Asignada</label>
                            <div class="relative">
                                <select name="guardia_id" class="form-select pr-10">
                                    <option value="">Sin Asignar</option>
                                    @foreach($guardias as $guardia)
                                        <option value="{{ $guardia->id }}" {{ old('guardia_id') == $guardia->id ? 'selected' : '' }}>
                                            {{ $guardia->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            @error('guardia_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-3">
                            <label class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition">
                                <input type="checkbox" name="fuera_de_servicio" value="1" {{ old('fuera_de_servicio') ? 'checked' : '' }} class="rounded text-red-600 focus:ring-red-500 h-5 w-5 border-slate-200">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-[#1e293b] uppercase tracking-wide">Fuera de servicio</div>
                                    <div class="text-xs text-[#475569]">No aparecerá en listas operativas (turno, emergencias, academias, reemplazos, refuerzos).</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-200 pb-3">
                        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-700">
                            <i class="fas fa-shield-halved text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#1e293b]">Especialidades del Tenant</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($specialties as $specialty)
                            <label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 cursor-pointer transition-all shadow-sm group">
                                <input type="checkbox" name="specialty_ids[]" value="{{ $specialty->id }}" {{ in_array($specialty->id, old('specialty_ids', [])) ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 h-5 w-5 border-slate-200">
                                <div class="ml-3 min-w-0">
                                    <span class="block text-sm font-bold text-[#1e293b] group-hover:text-indigo-700">{{ $specialty->name }}</span>
                                    <span class="block text-xs text-[#475569]">{{ $specialty->icon }} · {{ $specialty->color }}</span>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-[#475569]">No hay especialidades activas. Configura en el módulo de Especialidades.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-200">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.volunteers.index') }}">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save" :disabled="isset($limitData) && !$limitData['can_create']">
                        Registrar Voluntario
                    </x-ui.button>
                </div>
                </fieldset>
            </form>
        </div>
    </div>

@endsection
