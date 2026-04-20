@extends('central.layouts.app')

@section('title', 'Pagos — Panel Central')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pagos</h1>
            <p class="text-slate-500 text-sm mt-1">Historial central de ingresos; la facturación operativa sigue en <a href="{{ route('central.billing.index') }}" class="text-amber-700 hover:underline">Facturación</a>.</p>
        </div>
        <a href="{{ route('central.payments.create') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
            Registrar pago
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Compañía</th>
                        <th class="px-5 py-3">Monto</th>
                        <th class="px-5 py-3">Método</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Fecha pago</th>
                        <th class="px-5 py-3">Referencia</th>
                        <th class="px-5 py-3">Creado por</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $p->id }}</td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-900">{{ $p->tenant?->nombre ?? '—' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $p->tenant_id }}</div>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                {{ $p->currency }} {{ number_format((float) $p->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 capitalize text-slate-700">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                            <td class="px-5 py-3">
                                @php $st = $p->status; @endphp
                                <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full
                                    @if($st === 'paid') bg-emerald-100 text-emerald-800
                                    @elseif($st === 'pending') bg-amber-100 text-amber-900
                                    @elseif($st === 'failed') bg-rose-100 text-rose-800
                                    @else bg-slate-200 text-slate-700 @endif">
                                    {{ $st }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-700">{{ $p->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 max-w-[140px] truncate" title="{{ $p->reference }}">{{ $p->reference ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600 text-xs">
                                {{ $p->createdBy?->name ?? '—' }}
                                @if($p->createdBy?->username)
                                    <span class="text-slate-400">({{ $p->createdBy->username }})</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('central.payments.show', $p) }}" class="text-slate-600 hover:text-slate-900 font-medium mr-2">Ver</a>
                                <a href="{{ route('central.payments.edit', $p) }}" class="text-amber-700 hover:text-amber-900 font-medium">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-slate-500">No hay pagos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
