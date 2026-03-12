@extends('layouts.modern')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="icon-box icon-box-gradient-red icon-box-lg">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h1 class="text-title-lg uppercase">Editar Bombero</h1>
                    <p class="text-body-sm">Modificación rápida de datos operativos</p>
                </div>
            </div>
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ auth()->user()->role === 'guardia' ? route('admin.dotaciones') : route('admin.guardias') }}">
                Volver a Guardias
            </x-ui.button>
        </div>

        <x-ui.card class="!border-t-4 !border-t-slate-800">
            <form action="{{ route('admin.bomberos.update', $bombero->id) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Datos Básicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label" for="name">
                                Nombres
                            </label>
                            <input type="text" name="nombres" id="name" value="{{ old('nombres', $bombero->nombres) }}" required
                                class="form-input @error('nombres') !border-red-500 @enderror">
                            @error('nombres') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label" for="last_name_paternal">
                                Apellido Paterno
                            </label>
                            <input type="text" name="apellido_paterno" id="last_name_paternal" value="{{ old('apellido_paterno', $bombero->apellido_paterno) }}"
                                class="form-input @error('apellido_paterno') !border-red-500 @enderror">
                            @error('apellido_paterno') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Datos Operativos -->
                    <div class="card !bg-slate-50 dark:!bg-slate-800 !border-slate-200 dark:!border-slate-700">
                        <h3 class="text-label mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Información Operativa</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label" for="guardia_id">
                                    Guardia Asignada
                                </label>
                                <select name="guardia_id" id="guardia_id" class="form-select">
                                    @foreach($guardias as $guardia)
                                        <option value="{{ $guardia->id }}" {{ $bombero->guardia_id == $guardia->id ? 'selected' : '' }}>
                                            {{ $guardia->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Especialidades -->
                    <div class="card !bg-blue-50 dark:!bg-blue-900/20 !border-blue-200 dark:!border-blue-800 flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="es_conductor" id="is_driver" value="1" {{ $bombero->es_conductor ? 'checked' : '' }}
                                class="form-checkbox">
                            <span class="ml-3 font-bold text-slate-700 dark:text-slate-300 flex items-center">
                                <i class="fas fa-truck mr-2 text-blue-500"></i> Habilitado como Conductor
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800 mt-6">
                        <x-ui.button variant="ghost" size="md" href="{{ auth()->user()->role === 'guardia' ? route('admin.dotaciones') : route('admin.guardias') }}" class="mr-4">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                            Guardar Cambios
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
