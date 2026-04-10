@extends('central.layouts.app')

@section('title', 'Facturación - GuardiAPP SaaS')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Facturación</h1>
            <p class="text-slate-600">Administración de pagos y suscripciones de tenants.</p>
        </div>
        <a href="{{ route('central.billing.plans.index') }}" 
           class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-layer-group mr-2"></i>Administrar Planes
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

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600">Pagos Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pagos_pendientes'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600">En Trial</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['trials'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-gift text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-amber-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600">Por Vencer (7 días)</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $stats['por_vencer'] }}</p>
                </div>
                <div class="bg-amber-100 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-red-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600">Vencidos</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['vencidos'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-ban text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-emerald-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-600">Ingresos Estimados</p>
                    <p class="text-2xl font-bold text-emerald-600">${{ number_format($stats['ingresos_estimados'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-emerald-100 p-3 rounded-lg">
                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Billing Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-900">Registros de Facturación</h2>
            <button onclick="document.getElementById('create-modal').classList.remove('hidden')" 
                    class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>Nuevo Registro
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Compañía</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ciclo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Vencimiento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Observación</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($billings as $billing)
                        <tr class="hover:bg-white">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $billing->tenant?->nombre ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $billing->tenant_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="capitalize text-sm">{{ $billing->planRelation?->nombre ?? $billing->tenant?->planRelation?->nombre ?? 'Sin plan' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $billing->getCicloClase() }}">
                                    {{ $billing->getCicloEtiqueta() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">${{ number_format($billing->monto, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($billing->fecha_vencimiento)
                                    <span class="text-sm {{ $billing->estaVencido() ? 'text-red-600 font-semibold' : '' }}">
                                        {{ $billing->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $billing->getEstadoClase() }}">
                                    {{ ucfirst($billing->estado_pago) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <div class="text-sm text-slate-600 truncate" title="{{ $billing->observacion }}">
                                    {{ $billing->observacion ?: '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3" style="min-width: 320px;">
                                <div class="flex items-center gap-1">
                                    <button onclick="openPaymentModal({{ $billing->id }})" 
                                            class="px-2 py-1 text-xs font-medium rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border border-emerald-200" 
                                            title="Registrar pago">
                                        Pagar
                                    </button>

                                    <button onclick="openExtendModal({{ $billing->id }})" 
                                            class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200 border border-blue-200" 
                                            title="Extender vencimiento">
                                        Extender
                                    </button>

                                    <button onclick="openPlanModal({{ $billing->id }}, '{{ $billing->plan_id ?? $billing->tenant?->plan_id }}')" 
                                            class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-700 hover:bg-purple-200 border border-purple-200" 
                                            title="Cambiar plan">
                                        Plan
                                    </button>

                                    <button onclick="openObservationModal({{ $billing->id }}, '{{ addslashes($billing->observacion) }}')" 
                                            class="px-2 py-1 text-xs font-medium rounded bg-white text-slate-700 hover:bg-slate-200 border border-slate-200" 
                                            title="Editar observación">
                                        Obs
                                    </button>

                                    <form action="{{ route('central.billing.suspend', $billing) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('¿Suspender tenant?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700 hover:bg-red-200 border border-red-200" 
                                                title="Suspender">
                                            Suspender
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No hay registros de facturación.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200">
            {{ $billings->links() }}
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div id="create-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Nuevo Registro de Facturación</h3>
        <form action="{{ route('central.billing.create') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label>
                    <select name="tenant_id" class="w-full rounded-lg border-slate-300" required>
                        <option value="">Seleccionar...</option>
                        @foreach(App\Models\Tenant::whereDoesntHave('billing')->get() as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->nombre }} ({{ $tenant->id }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Plan</label>
                    <select name="plan_id" class="w-full rounded-lg border-slate-300" required>
                        @foreach($planes as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monto</label>
                    <input type="number" name="monto" class="w-full rounded-lg border-slate-300" required min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ciclo</label>
                    <select name="billing_cycle" class="w-full rounded-lg border-slate-300" required>
                        <option value="monthly">Mensual</option>
                        <option value="yearly">Anual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" class="w-full rounded-lg border-slate-300" required>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                    Crear
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Payment Modal --}}
<div id="payment-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Registrar Pago</h3>
        <form id="payment-form" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Pago</label>
                    <input type="date" name="fecha_pago" class="w-full rounded-lg border-slate-300" required value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Método de Pago</label>
                    <select name="metodo_pago" class="w-full rounded-lg border-slate-300">
                        <option value="">Seleccionar...</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="cheque">Cheque</option>
                        <option value="webpay">Webpay</option>
                        <option value="paypal">PayPal</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                    <i class="fas fa-check mr-2"></i>Registrar Pago
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Billing Cycle Modal --}}
<div id="cycle-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-sm w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Cambiar Ciclo de Facturación</h3>
        <form id="cycle-form" method="POST">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ciclo</label>
                <select name="billing_cycle" id="cycle-select" class="w-full rounded-lg border-slate-300" required>
                    <option value="monthly">Mensual (30 días)</option>
                    <option value="yearly">Anual (365 días)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">Al cambiar el ciclo, el próximo vencimiento se recalculará automáticamente.</p>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeCycleModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                    Cambiar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Extend Modal --}}
<div id="extend-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-sm w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Extender Vencimiento</h3>
        <form id="extend-form" method="POST">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Días a agregar</label>
                <input type="number" name="dias" class="w-full rounded-lg border-slate-300" required min="1" max="365" value="30">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeExtendModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Extender
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Plan Modal --}}
<div id="plan-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-sm w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Cambiar Plan</h3>
        <form id="plan-form" method="POST">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Plan</label>
                    <select name="plan_id" id="plan-select" class="w-full rounded-lg border-slate-300" required>
                        @foreach($planes as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">El monto se actualizará automáticamente según el plan seleccionado.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Cambiar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Observation Modal --}}
<div id="observation-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div data-modal-dialog class="bg-white rounded-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Editar Observación</h3>
        <form id="observation-form" method="POST">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Observación</label>
                <textarea name="observacion" id="observation-text" rows="3" class="w-full rounded-lg border-slate-300" maxlength="500"></textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeObservationModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(billingId) {
    document.getElementById('payment-form').action = `/admin/billing/${billingId}/mark-paid`;
    document.getElementById('payment-modal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('payment-modal').classList.add('hidden');
}

function openCycleModal(billingId, currentCycle) {
    document.getElementById('cycle-form').action = `/admin/billing/${billingId}/billing-cycle`;
    document.getElementById('cycle-select').value = currentCycle || 'monthly';
    document.getElementById('cycle-modal').classList.remove('hidden');
}

function closeCycleModal() {
    document.getElementById('cycle-modal').classList.add('hidden');
}

function openExtendModal(billingId) {
    document.getElementById('extend-form').action = `/admin/billing/${billingId}/extend`;
    document.getElementById('extend-modal').classList.remove('hidden');
}

function closeExtendModal() {
    document.getElementById('extend-modal').classList.add('hidden');
}

function openPlanModal(billingId, currentPlan) {
    document.getElementById('plan-form').action = `/admin/billing/${billingId}/change-plan`;
    document.getElementById('plan-select').value = currentPlan;
    document.getElementById('plan-modal').classList.remove('hidden');
}

function closePlanModal() {
    document.getElementById('plan-modal').classList.add('hidden');
}

function openObservationModal(billingId, currentObservation) {
    document.getElementById('observation-form').action = `/admin/billing/${billingId}/observation`;
    document.getElementById('observation-text').value = currentObservation || '';
    document.getElementById('observation-modal').classList.remove('hidden');
}

function closeObservationModal() {
    document.getElementById('observation-modal').classList.add('hidden');
}

// Close modals on click outside
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
    }
}
</script>
@endsection
