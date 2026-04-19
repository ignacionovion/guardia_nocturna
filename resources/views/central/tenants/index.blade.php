@extends('central.layouts.app')

@section('title', 'Compañías')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Compañías</h1>
            <p class="text-slate-500 text-sm mt-1">Gestión de compañías (tenants) de la plataforma</p>
        </div>
        <a href="{{ route('central.tenants.create') }}"
           class="bg-slate-900 text-white text-sm font-medium py-2.5 px-5 rounded-xl hover:bg-slate-800 transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nueva Compañía</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-white border-b border-slate-200">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Compañía</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cuerpo</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Facturación</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Vence / Trial</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado acceso</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subdominio</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tenants as $tenant)
                    @php
                        $planSlug = $tenant->planRelation?->slug;
                        $planName = $tenant->planRelation?->nombre ?? 'Sin plan';
                        $b = $tenant->billing;
                        $cycleLabel = $b?->billing_cycle === 'yearly' ? 'Anual' : 'Mensual';
                    @endphp
                    <tr class="hover:bg-white transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                                    <span class="text-xs font-bold text-slate-600">{{ $tenant->numero ?? '#' }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 text-sm">{{ $tenant->nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ $tenant->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $tenant->body?->nombre ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $planSlug ? 'bg-blue-50 text-blue-700' : 'bg-white text-slate-600' }}">
                                {{ $planName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            @if($b)
                                <div class="font-medium text-slate-800 capitalize">{{ str_replace('_', ' ', $b->estado_pago) }}</div>
                                <div class="text-slate-500">{{ $cycleLabel }} · ${{ number_format((float) $b->monto, 0, ',', '.') }}</div>
                                @if(in_array($b->estado_pago, ['vencido', 'suspendido'], true))
                                    <span class="inline-block mt-1 text-[10px] font-semibold text-rose-600">Requiere acción</span>
                                @elseif($b->estado_pago === 'pendiente' && $tenant->daysUntilExpiry() !== null && $tenant->daysUntilExpiry() <= 7 && $tenant->daysUntilExpiry() >= 0)
                                    <span class="inline-block mt-1 text-[10px] font-semibold text-amber-600">Por vencer</span>
                                @endif
                            @else
                                <span class="text-amber-600">Sin billing</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600 whitespace-nowrap">
                            @if($b && $b->estado_pago === 'trial' && $b->trial_ends_at)
                                Trial {{ $b->trial_ends_at->format('d/m/Y') }}
                            @elseif($tenant->fecha_vencimiento)
                                {{ $tenant->fecha_vencimiento->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->estadoBadgeClass() }}">
                                {{ $tenant->estadoLabel() }}
                            </span>
                            @if(!$tenant->activo)
                                <span class="block text-[10px] text-slate-500 mt-0.5">activo=0</span>
                            @endif
                            @if($tenant->daysUntilExpiry() !== null && $tenant->daysUntilExpiry() <= 7 && $tenant->daysUntilExpiry() > 0)
                                <span class="text-[10px] text-amber-600 ml-1">{{ $tenant->daysUntilExpiry() }}d</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-xs bg-white px-2 py-1 rounded text-slate-600">{{ $tenant->domains->first()?->domain ?? $tenant->id }}</code>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <a href="{{ route('central.tenants.show', $tenant->id) }}" class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition" title="Ver detalles">
                                    Ver
                                </a>
                                <a href="{{ route('central.tenants.edit', $tenant->id) }}" class="px-2 py-1 text-xs font-medium text-amber-600 bg-amber-50 rounded hover:bg-amber-100 transition" title="Editar">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('central.tenants.destroy', $tenant->id) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar {{ $tenant->nombre }} y TODA su base de datos? Esta acción es irreversible.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition" title="Eliminar">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No hay compañías registradas. <a href="{{ route('central.tenants.create') }}" class="text-blue-600 hover:underline">Crear la primera</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
@endsection
