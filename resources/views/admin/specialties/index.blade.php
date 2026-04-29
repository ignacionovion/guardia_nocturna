@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Especialidades" subtitle="Configura las especialidades activas del tenant" icon="fas fa-shield-halved" iconVariant="indigo">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.volunteers.index') }}">
            Voluntarios
        </x-ui.button>
    </x-ui.page-header>

    @if(session('warning'))
        <x-ui.alert type="warning" icon="fas fa-exclamation-triangle" class="mb-6">{{ session('warning') }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.specialties.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="md:col-span-2">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="w-full min-h-[44px] px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white" required>
            </div>
            <div>
                <label class="form-label">Color</label>
                <input type="text" name="color" class="w-full min-h-[44px] px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white" placeholder="#334155">
            </div>
            <div class="md:col-span-4">
                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-plus">Agregar especialidad</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Datos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Uso</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($specialties as $specialty)
                    <tr>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.specialties.update', $specialty->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $specialty->name }}" class="w-full min-h-[44px] px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white" required>
                                <input type="text" name="color" value="{{ $specialty->color }}" class="w-full min-h-[44px] px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                                <div class="md:col-span-2 mt-2">
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="fas fa-save">Guardar cambios</x-ui.button>
                                </div>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $specialty->bomberos_count }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold {{ $specialty->active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $specialty->active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.specialties.toggle', $specialty->id) }}" method="POST">
                                    @csrf
                                    <x-ui.button type="submit" variant="{{ $specialty->active ? 'warning' : 'success' }}" size="sm" icon="fas fa-power-off">
                                        {{ $specialty->active ? 'Desactivar' : 'Activar' }}
                                    </x-ui.button>
                                </form>
                                <form action="{{ route('admin.specialties.destroy', $specialty->id) }}" method="POST" onsubmit="return confirm('¿Eliminar especialidad?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash-can">Eliminar</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay especialidades configuradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
