@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                    <i class="fas fa-user-edit mr-3 text-red-700"></i> Editar Bombero
                </h1>
                <p class="text-slate-500 mt-1 font-medium">Modificación rápida de datos operativos</p>
            </div>
            <a href="{{ auth()->user()->role === 'guardia' ? route('admin.dotaciones') : route('admin.guardias') }}" class="inline-flex items-center text-slate-600 hover:text-blue-600 font-medium transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 hover:border-blue-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Guardias
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-slate-200">
            <!-- Barra superior decorativa -->
            <div class="h-2 bg-slate-800"></div>

            <form action="{{ route('admin.bomberos.update', $bombero->id) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Datos Básicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="name">
                                Nombres
                            </label>
                            <input type="text" name="nombres" id="name" value="{{ old('nombres', $bombero->nombres) }}" required
                                class="w-full border-2 border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-slate-700 bg-white">
                            @error('nombres') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="last_name_paternal">
                                Apellido Paterno
                            </label>
                            <input type="text" name="apellido_paterno" id="last_name_paternal" value="{{ old('apellido_paterno', $bombero->apellido_paterno) }}"
                                class="w-full border-2 border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-slate-700 bg-white">
                            @error('apellido_paterno') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Datos Operativos -->
                    <div class="bg-slate-50 p-6 rounded-xl border-2 border-slate-200">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-4 border-b border-slate-200 pb-2">Información Operativa</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2 uppercase tracking-wide" for="guardia_id">
                                    Guardia Asignada
                                </label>
                                <select name="guardia_id" id="guardia_id" class="w-full border-2 border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2.5 text-slate-700 bg-white">
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
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="es_conductor" id="is_driver" value="1" {{ $bombero->es_conductor ? 'checked' : '' }}
                                class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-5 w-5">
                            <span class="ml-3 font-bold text-slate-700 flex items-center">
                                <i class="fas fa-truck mr-2 text-blue-500"></i> Habilitado como Conductor
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 mt-6">
                        <a href="{{ auth()->user()->role === 'guardia' ? route('admin.dotaciones') : route('admin.guardias') }}" class="mr-4 px-6 py-2.5 rounded-lg border border-slate-300 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 uppercase tracking-wide text-sm">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
