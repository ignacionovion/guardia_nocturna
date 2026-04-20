@extends('central.layouts.app')

@section('title', 'Panel Financiero SaaS')

@section('content')
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Finanzas</h1>
            <p class="text-sm text-slate-500">Estado de caja y riesgo comercial del SaaS central.</p>
        </div>
        <p class="text-xs text-slate-400">Actualizado: {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 mb-8">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs uppercase font-semibold text-emerald-700">Ingresos del mes</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900">CLP {{ number_format($metrics['monthly_income'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-white p-5">
            <p class="text-xs uppercase font-semibold text-emerald-700">Ingresos totales</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">CLP {{ number_format($metrics['total_income'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs uppercase font-semibold text-amber-700">Pagos pendientes</p>
            <p class="mt-1 text-2xl font-bold text-amber-900">{{ $metrics['pending_payments_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-xs uppercase font-semibold text-blue-700">Tenants activos</p>
            <p class="mt-1 text-2xl font-bold text-blue-900">{{ $metrics['active_tenants_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-xs uppercase font-semibold text-red-700">Tenants vencidos</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ $metrics['expired_tenants_count'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-300 bg-slate-50 p-5">
            <p class="text-xs uppercase font-semibold text-slate-600">Tenants suspendidos</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $metrics['suspended_tenants_count'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 mb-8">
        <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Pagos recientes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Tenant</th>
                            <th class="px-4 py-3">Monto</th>
                            <th class="px-4 py-3">Método</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Fecha pago</th>
                            <th class="px-4 py-3">Referencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $payment->id }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $payment->tenant?->nombre ?? $payment->tenant_id }}</td>
                                <td class="px-4 py-3 font-medium">{{ $payment->currency }} {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 capitalize">{{ $payment->payment_method }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                        @if($payment->status === 'paid') bg-emerald-100 text-emerald-800
                                        @elseif($payment->status === 'pending') bg-amber-100 text-amber-900
                                        @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                        @else bg-slate-200 text-slate-700 @endif">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500 max-w-[180px] truncate" title="{{ $payment->reference }}">{{ $payment->reference ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay pagos recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Métodos de pago</h2>
            </div>
            <div class="p-4 space-y-3">
                @forelse($paymentMethodSummary as $method)
                    <div class="rounded-xl border border-slate-100 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $method->method_group }}</p>
                        <p class="text-sm text-slate-700">{{ $method->payments_count }} pagos</p>
                        <p class="text-base font-semibold text-slate-900">CLP {{ number_format((float) $method->total_amount, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin pagos pagados para agrupar.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 mb-8">
        <div class="rounded-2xl border border-blue-200 bg-white overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100">
                <h2 class="font-semibold text-slate-900">Próximos vencimientos (7 días)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-blue-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-blue-700">
                            <th class="px-4 py-3">Tenant</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3">Vencimiento</th>
                            <th class="px-4 py-3">Días restantes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100">
                        @forelse($upcomingExpirations as $tenant)
                            <tr>
                                <td class="px-4 py-3 text-slate-800">{{ $tenant->nombre }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $tenant->planRelation?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $tenant->fecha_vencimiento?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-semibold {{ $tenant->days_remaining <= 2 ? 'text-amber-700' : 'text-blue-700' }}">{{ $tenant->days_remaining }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay vencimientos próximos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-white overflow-hidden">
            <div class="px-5 py-4 border-b border-red-100">
                <h2 class="font-semibold text-slate-900">Tenants en riesgo</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-red-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-red-700">
                            <th class="px-4 py-3">Tenant</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Vencimiento</th>
                            <th class="px-4 py-3">Días vencido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-100">
                        @forelse($riskTenants as $tenant)
                            <tr>
                                <td class="px-4 py-3 text-slate-800">{{ $tenant->nombre }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $tenant->in_grace ? 'bg-amber-100 text-amber-900' : 'bg-red-100 text-red-800' }}">
                                        {{ $tenant->in_grace ? 'en_gracia' : $tenant->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $tenant->fecha_vencimiento?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-red-700">{{ $tenant->days_overdue }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay tenants vencidos ni en gracia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Gobernanza de correos (mail_strategy)</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 p-5">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase font-semibold text-emerald-700 mb-2">Tipos activos ({{ $activeMailTypes->count() }})</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($activeMailTypes as $type)
                        <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 font-medium">{{ $type }}</span>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase font-semibold text-slate-600 mb-2">Tipos desactivados ({{ $disabledMailTypes->count() }})</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($disabledMailTypes as $type)
                        <span class="px-2 py-1 rounded text-xs bg-slate-200 text-slate-700 font-medium">{{ $type }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
