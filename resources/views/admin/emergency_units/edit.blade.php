@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.page-header title="Editar Unidad" subtitle="Actualiza nombre y descripción" icon="fas fa-truck" iconVariant="amber">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergency-units.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <form method="POST" action="{{ route('admin.emergency-units.update', $unit->id) }}" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $unit->name) }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Descripción (opcional)</label>
                        <textarea name="description" rows="4"
                            class="form-input">{{ old('description', $unit->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.emergency-units.index') }}">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                        Guardar cambios
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection
