@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Emergencias" subtitle="Registro de emergencias transcurridas en guardias nocturnas" icon="fas fa-truck-medical" iconVariant="amber">
        @if(auth()->check() && in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania'], true))
            <x-ui.button variant="secondary" size="md" icon="fas fa-key" href="{{ route('admin.emergency-keys.index') }}">
                Claves Radiales
            </x-ui.button>
            <x-ui.button variant="secondary" size="md" icon="fas fa-truck" href="{{ route('admin.emergency-units.index') }}">
                Unidades
            </x-ui.button>
        @endif
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.emergencies.create') }}">
            Nueva Emergencia
        </x-ui.button>
    </x-ui.page-header>

    <x-ui.card class="mb-8">
        <form action="{{ route('admin.emergencies.index') }}" method="GET" class="relative">
            <div class="flex items-center">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#475569]"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por clave o detalle del llamado..."
                    class="bg-[#e7eef5] border border-[#9fb0c3] text-[#1e293b] placeholder-[#475569] rounded-xl min-h-[44px] px-4 py-3 pl-11 text-sm focus:border-[#1e293b] focus:ring-2 focus:ring-[#1e293b]/10 focus:outline-none flex-1">

                @if(request('search'))
                    <a href="{{ route('admin.emergencies.index') }}" class="absolute right-24 top-1/2 -translate-y-1/2 text-[#475569] hover:text-[#1e293b] p-2">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <x-ui.button type="submit" variant="primary" size="md" class="ml-3">
                    Buscar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($emergencies->isEmpty())
        <x-ui.empty-state icon="fas fa-truck-medical" title="No hay emergencias registradas" message="Registra una emergencia para comenzar el historial." />
    @else
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-[#c3cfdb]">
                        <tr class="text-label">
                            <th scope="col" class="px-6 py-4 text-left">Clave</th>
                            <th scope="col" class="px-6 py-4 text-left">H. salida</th>
                            <th scope="col" class="px-6 py-4 text-left">H. llegada</th>
                            <th scope="col" class="px-6 py-4 text-left">Unidades</th>
                            <th scope="col" class="px-6 py-4 text-left">A cargo</th>
                            <th scope="col" class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($emergencies as $emergency)
                            <tr class="hover:bg-[#c3cfdb] transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $emergency->key?->code ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($emergency->key?->description ?? '', 60) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $emergency->dispatched_at?->format('d-m-Y') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $emergency->dispatched_at?->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    @if($emergency->arrived_at)
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $emergency->arrived_at->format('d-m-Y') }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $emergency->arrived_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-wrap gap-1 max-w-sm">
                                        @forelse($emergency->units as $unit)
                                            <x-ui.badge variant="info" size="xs">{{ $unit->name }}</x-ui.badge>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @if($emergency->officerInChargeFirefighter || $emergency->officerInCharge)
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $emergency->officerInChargeFirefighter?->nombres ?? $emergency->officerInCharge?->name }} {{ $emergency->officerInChargeFirefighter?->apellido_paterno ?? '' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $emergency->guardia?->name ?? '-' }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.emergencies.edit', $emergency->id) }}" class="text-slate-400 hover:text-blue-600 transition-colors p-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.emergencies.destroy', $emergency->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta emergencia? Esta acción no se puede deshacer.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Eliminar">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-slot:footer>
                {{ $emergencies->links() }}
            </x-slot:footer>
        </x-ui.card>
    @endif
@endsection
