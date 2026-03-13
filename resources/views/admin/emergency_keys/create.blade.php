@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.page-header title="Nueva Clave" subtitle="Agrega una clave para emergencias" icon="fas fa-key" iconVariant="amber">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergency-keys.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <form method="POST" action="{{ route('admin.emergency-keys.store') }}" class="p-8">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="form-label">Código</label>
                        <input type="text" name="code" value="{{ old('code') }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Descripción</label>
                        <textarea name="description" rows="4" required
                            class="form-input">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.emergency-keys.index') }}">
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
