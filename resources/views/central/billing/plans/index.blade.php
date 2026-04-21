@extends('central.layouts.app')

@section('title', 'Planes - GuardiAPP SaaS')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Administración de Planes</h1>
            <p class="text-slate-600">Gestiona los planes disponibles para los tenants.</p>
        </div>
        <a href="{{ route('central.billing.index') }}" class="text-slate-600 hover:text-slate-900">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Facturación
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Plans Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Planes Disponibles</h2>
            <a href="{{ route('central.billing.plans.create') }}" 
               class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>Nuevo Plan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Orden</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Precio Mensual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Precio Anual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Límites</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tenants</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-white">
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-slate-900">{{ $plan->orden }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $plan->nombre }}</div>
                                <div class="text-xs text-slate-500">{{ $plan->slug }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">${{ number_format($plan->precio_mensual, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">${{ number_format($plan->precio_anual, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs space-y-1">
                                    <div><span class="text-slate-500">Usuarios:</span> <span class="font-medium">{{ $plan->max_users ?? '∞' }}</span></div>
                                    <div><span class="text-slate-500">Voluntarios:</span> <span class="font-medium">{{ $plan->max_volunteers ?? '∞' }}</span></div>
                                    <div><span class="text-slate-500">Guardias:</span> <span class="font-medium">{{ $plan->max_guardias ?? '∞' }}</span></div>
                                    <div><span class="text-slate-500">Camas:</span> <span class="font-medium">{{ $plan->max_beds ?? '∞' }}</span></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white text-slate-800">
                                    {{ $plan->tenants()->count() }} tenants
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($plan->activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-white text-slate-600 border-slate-200">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('central.billing.plans.edit', $plan) }}" 
                                       class="px-3 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200 border border-blue-200">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </a>
                                    
                                    <form action="{{ route('central.billing.plans.toggle', $plan) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="px-3 py-1 text-xs font-medium rounded {{ $plan->activo ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 border-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border-emerald-200' }} border">
                                            <i class="fas fa-{{ $plan->activo ? 'pause' : 'play' }} mr-1"></i>{{ $plan->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    @if($plan->tenants()->count() === 0)
                                        <form action="{{ route('central.billing.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este plan permanentemente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-3 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200 border border-red-200">
                                                <i class="fas fa-trash mr-1"></i>Eliminar
                                            </button>
                                        </form>
                                    @else
                                        <button disabled 
                                                class="px-3 py-1 text-xs font-medium rounded bg-white text-slate-400 border border-slate-200 cursor-not-allowed"
                                                title="No se puede eliminar porque está en uso">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No hay planes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
