@extends('layouts.modern')

@section('content')
    <div class="max-w-xl mx-auto">
        <x-ui.page-header title="Editar Guardia" subtitle="Modificar nombre de la unidad operativa" icon="fas fa-edit" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.guardias') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <div class="p-8">
                <form action="{{ route('admin.guardias.update', $guardia->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-8">
                        <label class="form-label" for="name">
                            Nombre de la Guardia
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-shield-halved text-slate-400"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $guardia->name) }}" required
                                class="form-input pl-10">
                        </div>
                        @error('name')
                            <p class="text-red-600 text-xs mt-2 font-medium flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                            Actualizar Nombre
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
