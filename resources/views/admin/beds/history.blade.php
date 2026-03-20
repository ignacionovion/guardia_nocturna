@extends('layouts.modern')

@section('content')
<div class="w-full max-w-6xl mx-auto">
    <x-ui.page-header title="Historial de Ocupación" subtitle="{{ $bed->name }}" icon="fas fa-history" iconVariant="primary">
        <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.beds.index') }}">
            Volver al listado
        </x-ui.button>
    </x-ui.page-header>

    {{-- Info de la cama --}}
    <x-ui.card class="mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Nombre</p>
                    <p class="text-base font-bold text-[#1e293b]">{{ $bed->name }}</p>
                </div>
                @if($bed->location)
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Ubicación</p>
                    <p class="text-base font-bold text-[#1e293b]">{{ $bed->location }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Estado Actual</p>
                    <x-ui.badge variant="{{ $bed->status_color }}" size="sm">
                        {{ $bed->status_label }}
                    </x-ui.badge>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Género</p>
                    <x-ui.badge variant="{{ $bed->gender_color }}" size="sm">
                        {{ $bed->gender_label }}
                    </x-ui.badge>
                </div>
            </div>

            @if($bed->is_occupied && $bed->current_occupant_name)
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-amber-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Ocupada actualmente por</p>
                            <p class="text-lg font-bold text-amber-900">{{ $bed->current_occupant_name }}</p>
                            @if($bed->currentAssignment && $bed->currentAssignment->started_at)
                                <p class="text-sm text-amber-700">
                                    Desde {{ $bed->currentAssignment->started_at->format('d/m/Y H:i') }}
                                    ({{ $bed->currentAssignment->started_at->diffForHumans() }})
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Historial --}}
    <x-ui.card>
        <div class="p-6 border-b border-[#9fb0c3] bg-[#c3cfdb]">
            <h3 class="text-lg font-bold text-[#1e293b]">Historial de Asignaciones</h3>
            <p class="text-sm text-[#475569]">Registro completo de ocupaciones de esta cama</p>
        </div>

        @if($assignments->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-[#c3cfdb] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-[#475569] text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1e293b] mb-2">Sin historial</h3>
                <p class="text-[#475569]">Esta cama aún no ha sido asignada a ningún voluntario.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-[#c3cfdb] border-b border-[#9fb0c3]">
                        <tr class="text-xs font-semibold uppercase tracking-wider text-[#475569]">
                            <th class="text-left px-6 py-3">Voluntario</th>
                            <th class="text-left px-6 py-3">Inicio</th>
                            <th class="text-left px-6 py-3">Término</th>
                            <th class="text-left px-6 py-3">Duración</th>
                            <th class="text-left px-6 py-3">Asignado por</th>
                            <th class="text-left px-6 py-3">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#e7eef5] divide-y divide-[#9fb0c3]">
                        @foreach($assignments as $assignment)
                            <tr class="hover:bg-[#c3cfdb] transition-colors {{ $assignment->isActive() ? 'bg-amber-50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($assignment->isActive())
                                            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                        @endif
                                        <div>
                                            <p class="font-bold text-[#1e293b]">
                                                {{ $assignment->volunteer ? trim($assignment->volunteer->nombres . ' ' . $assignment->volunteer->apellido_paterno) : 'N/A' }}
                                            </p>
                                            @if($assignment->volunteer && $assignment->volunteer->rut)
                                                <p class="text-xs text-[#475569]">{{ $assignment->volunteer->rut }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-[#1e293b]">{{ $assignment->started_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-[#475569]">{{ $assignment->started_at->format('H:i') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($assignment->ended_at)
                                        <p class="text-sm font-semibold text-[#1e293b]">{{ $assignment->ended_at->format('d/m/Y') }}</p>
                                        <p class="text-xs text-[#475569]">{{ $assignment->ended_at->format('H:i') }}</p>
                                    @else
                                        <x-ui.badge variant="warning" size="sm">
                                            En curso
                                        </x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-[#1e293b]">{{ $assignment->duration }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-[#475569]">
                                        {{ $assignment->assignedBy ? $assignment->assignedBy->name : 'Sistema' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($assignment->notes)
                                        <p class="text-sm text-[#475569] max-w-xs truncate" title="{{ $assignment->notes }}">
                                            {{ $assignment->notes }}
                                        </p>
                                    @else
                                        <span class="text-xs text-[#9fb0c3]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($assignments->hasPages())
                <div class="p-6 border-t border-[#9fb0c3]">
                    {{ $assignments->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
