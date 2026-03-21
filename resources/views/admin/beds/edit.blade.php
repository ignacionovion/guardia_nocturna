@extends('layouts.modern')

@section('content')
<div class="w-full max-w-4xl mx-auto">
    <x-ui.page-header title="Editar Cama" subtitle="Modificar información de {{ $bed->name }}" icon="fas fa-bed" iconVariant="primary">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.beds.index') }}">
            Volver
        </x-ui.button>
    </x-ui.page-header>

    @if($errors->any())
        <x-ui.alert type="error" icon="fas fa-exclamation-triangle" class="mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <x-ui.card>
        <form action="{{ route('admin.beds.update', $bed) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Nombre --}}
                <div>
                    <label class="form-label">Nombre de la cama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $bed->name) }}" required
                        class="form-input" placeholder="Ej: Cama 1, Litera A, etc.">
                </div>

                {{-- Sector/Pieza --}}
                <div>
                    <label class="form-label">Sector / Pieza</label>
                    <input type="text" name="room" value="{{ old('room', $bed->room) }}"
                        class="form-input" placeholder="Ej: Dormitorio 1, Sala principal, Segundo piso, etc.">
                    <p class="text-xs text-[#475569] mt-1">Agrupa las camas por sector o pieza del cuartel</p>
                </div>

                {{-- Ubicación --}}
                <div>
                    <label class="form-label">Ubicación (detalle adicional)</label>
                    <input type="text" name="location" value="{{ old('location', $bed->location) }}"
                        class="form-input" placeholder="Ej: Junto a la ventana, Esquina izquierda, etc.">
                </div>

                {{-- Género --}}
                <div>
                    <label class="form-label">Género <span class="text-red-500">*</span></label>
                    <select name="gender" required class="form-select">
                        <option value="mixed" {{ old('gender', $bed->gender) === 'mixed' ? 'selected' : '' }}>Mixto</option>
                        <option value="male" {{ old('gender', $bed->gender) === 'male' ? 'selected' : '' }}>Masculino</option>
                        <option value="female" {{ old('gender', $bed->gender) === 'female' ? 'selected' : '' }}>Femenino</option>
                    </select>
                    <p class="text-xs text-[#475569] mt-1">Define qué género puede usar esta cama</p>
                </div>

                {{-- Estado --}}
                <div>
                    <label class="form-label">Estado <span class="text-red-500">*</span></label>
                    <select name="status" required class="form-select">
                        <option value="available" {{ old('status', $bed->status) === 'available' ? 'selected' : '' }}>Disponible</option>
                        <option value="occupied" {{ old('status', $bed->status) === 'occupied' ? 'selected' : '' }}>Ocupada</option>
                        <option value="maintenance" {{ old('status', $bed->status) === 'maintenance' ? 'selected' : '' }}>Mantención</option>
                        <option value="disabled" {{ old('status', $bed->status) === 'disabled' ? 'selected' : '' }}>Deshabilitada</option>
                    </select>
                </div>

                {{-- Observaciones --}}
                <div>
                    <label class="form-label">Observaciones</label>
                    <textarea name="notes" rows="4" class="form-input" placeholder="Notas adicionales sobre esta cama...">{{ old('notes', $bed->notes) }}</textarea>
                </div>

                {{-- Info QR Token --}}
                <div class="p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-qrcode text-[#475569] text-xl"></i>
                        <div>
                            <p class="text-sm font-semibold text-[#1e293b]">Código QR generado</p>
                            <p class="text-xs text-[#475569]">Esta cama tiene un código QR único para escaneo</p>
                        </div>
                        <div class="ml-auto">
                            <x-ui.button variant="secondary" size="sm" icon="fas fa-qrcode" href="{{ route('admin.beds.qr', $bed) }}">
                                Ver QR
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-[#9fb0c3]">
                <x-ui.button variant="secondary" size="md" href="{{ route('admin.beds.index') }}">
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                    Guardar cambios
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
