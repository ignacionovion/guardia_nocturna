@extends('layouts.modern')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.page-header title="Editar Clave" subtitle="Actualiza código y descripción" icon="fas fa-key" iconVariant="amber">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.emergency-keys.index') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <form method="POST" action="{{ route('admin.emergency-keys.update', $key->id) }}" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="form-label">Código</label>
                        <input type="text" name="code" value="{{ old('code', $key->code) }}" required
                            class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Descripción</label>
                        <textarea name="description" rows="4" required
                            class="form-input">{{ old('description', $key->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.emergency-keys.index') }}">
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
