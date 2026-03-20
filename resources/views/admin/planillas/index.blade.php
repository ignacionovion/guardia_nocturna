@extends('layouts.modern')

@section('content')
<div class="w-full">
    <x-ui.page-header title="Planillas" subtitle="Registro semanal de revisión de unidades" icon="fas fa-clipboard-list" iconVariant="emerald">
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.planillas.create') }}">
            Nueva planilla
        </x-ui.button>
        @if(auth()->check() && auth()->user()->role === 'super_admin')
            <x-ui.button variant="secondary" size="md" icon="fas fa-list-check" href="{{ route('admin.planillas.listados.index') }}">
                Editar listados
            </x-ui.button>
            <x-ui.button variant="secondary" size="md" icon="fas fa-qrcode" href="{{ route('admin.planillas.qr_fijo') }}">
                QR fijo
            </x-ui.button>
        @endif
    </x-ui.page-header>

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6">
        <div class="p-5 border-b border-[#9fb0c3] bg-[#c3cfdb]">
            <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1">
                    <label class="form-label mb-2">Filtrar por unidad</label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <select name="unidad" class="bg-[#e7eef5] border border-[#9fb0c3] text-[#1e293b] rounded-xl min-h-[44px] px-4 py-3 text-sm focus:border-[#1e293b] focus:ring-2 focus:ring-[#1e293b]/10 focus:outline-none w-full sm:w-56 appearance-none">
                            <option value="">Todas</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u }}" {{ ($unidadSeleccionada ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                        <x-ui.button type="submit" variant="primary" size="md">Filtrar</x-ui.button>
                        <x-ui.button variant="secondary" size="md" href="{{ route('admin.planillas.index') }}">Limpiar</x-ui.button>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#c3cfdb] border-b border-[#9fb0c3]">
                    <tr class="text-xs font-semibold uppercase tracking-wider text-[#475569]">
                        <th class="text-left px-6 py-3">Fecha</th>
                        <th class="text-left px-6 py-3">Unidad</th>
                        <th class="text-left px-6 py-3">Estado</th>
                        <th class="text-left px-6 py-3">Registrada por</th>
                        <th class="text-right px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-[#e7eef5] divide-y divide-[#9fb0c3]">
                    @forelse($planillas as $p)
                        <tr class="hover:bg-[#c3cfdb] transition-colors">
                            <td class="px-6 py-4 font-semibold text-[#1e293b]">{{ $p->fecha_revision?->format('d-m-Y H:i') }}</td>
                            <td class="px-6 py-4 text-[#1e293b] font-semibold">{{ $p->unidad }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.planillas.estado.update', $p) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="estado" class="px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-wider border {{ ($p->estado ?? '') === 'finalizado' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200' }}" onchange="this.form.submit()">
                                        <option value="en_edicion" {{ ($p->estado ?? '') !== 'finalizado' ? 'selected' : '' }}>En edición</option>
                                        <option value="finalizado" {{ ($p->estado ?? '') === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-[#1e293b]">{{ $p->creador?->name ?? trim((string)($p->bombero?->nombres ?? '') . ' ' . (string)($p->bombero?->apellido_paterno ?? '')) ?: '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if(($p->estado ?? '') !== 'finalizado')
                                        <x-ui.button variant="secondary" size="sm" icon="fas fa-pen" href="{{ route('admin.planillas.edit', $p) }}">
                                            Continuar
                                        </x-ui.button>
                                    @endif
                                    <x-ui.button variant="secondary" size="sm" icon="fas fa-eye" href="{{ route('admin.planillas.show', $p) }}">
                                        Ver
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-envelope" href="{{ route('admin.planillas.email', $p) }}" title="Enviar por email" />
                                    <x-ui.button variant="ghost" size="sm" icon="fas fa-file-pdf" href="{{ route('admin.planillas.pdf', $p) }}" title="Descargar PDF" target="_blank" />
                                    <form method="POST" action="{{ route('admin.planillas.destroy', $p) }}" class="inline" onsubmit="return confirm('¿Eliminar esta planilla? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="ghost" size="sm" icon="fas fa-trash" class="text-red-500 hover:text-red-700">
                                            Eliminar
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-[#475569]">No hay planillas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-[#9fb0c3] bg-[#c3cfdb]">
            {{ $planillas->links() }}
        </div>
    </x-ui.card>

    {{-- Sección Historial --}}
    <div class="mt-8">
        <h2 class="text-label mb-4">Historial de Actividad</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Grid 1: Planillas Semanales por Guardia --}}
            <div class="bg-[#dde6ef] rounded-2xl border border-[#9fb0c3] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#9fb0c3] bg-[#c3cfdb]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-emerald-600"></i>
                            <div class="text-sm font-bold text-[#1e293b]">Planillas Semanales</div>
                        </div>
                        <span class="text-xs text-[#475569]">{{ $inicioSemana->format('d/m') }} - {{ $finSemana->format('d/m') }}</span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach($planillasPorGuardia as $pg)
                            <div class="flex items-center justify-between p-3 rounded-xl {{ $pg['completo'] ? 'bg-emerald-50 border border-emerald-100' : 'bg-[#e7eef5] border border-[#9fb0c3]' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full {{ $pg['completo'] ? 'bg-emerald-500 text-white' : 'bg-[#c3cfdb] text-[#475569]' }} flex items-center justify-center text-xs font-bold">
                                        @if($pg['completo'])
                                            <i class="fas fa-check"></i>
                                        @else
                                            <i class="fas fa-clock"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-[#1e293b]">{{ $pg['guardia']->name }}</div>
                                        <div class="text-xs {{ $pg['completo'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $pg['completadas'] }}/{{ $pg['total'] }} planillas
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($pg['completo'])
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                            <i class="fas fa-check-circle"></i>
                                            Completo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                            <i class="fas fa-exclamation-circle"></i>
                                            Pendiente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-3 border-t border-[#9fb0c3]">
                        <div class="flex items-center gap-4 text-xs text-[#475569]">
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span>Completado</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span>Pendiente</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grid 2: Bitácora / Nuevos Items --}}
            <div class="bg-[#dde6ef] rounded-2xl border border-[#9fb0c3] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-[#9fb0c3] bg-[#c3cfdb]">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-[#475569]"></i>
                        <div class="text-sm font-bold text-[#1e293b]">Bitácora - Novedades (Últimos 7 días)</div>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#c3cfdb] border-b border-[#9fb0c3] sticky top-0">
                            <tr class="text-xs font-semibold uppercase tracking-wider text-[#475569]">
                                <th class="text-left px-4 py-2">Fecha</th>
                                <th class="text-left px-4 py-2">Tipo</th>
                                <th class="text-left px-4 py-2">Descripción</th>
                                <th class="text-left px-4 py-2">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#e7eef5] divide-y divide-[#9fb0c3]">
                            @forelse($bitacora as $item)
                                <tr class="hover:bg-[#c3cfdb] transition-colors">
                                    <td class="px-4 py-3 text-xs text-[#475569]">{{ $item['fecha']?->format('d/m H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                            {{ $item['tipo'] === 'Planilla' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $item['tipo'] === 'Inventario' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $item['tipo'] === 'Novedad' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                        ">
                                            {{ $item['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($item['link'])
                                            <a href="{{ $item['link'] }}" class="text-blue-600 hover:underline">{{ $item['descripcion'] }}</a>
                                        @else
                                            {{ $item['descripcion'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-[#475569]">{{ $item['usuario'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-[#475569] text-sm">No hay registros nuevos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
