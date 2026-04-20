@extends('central.layouts.app')

@section('title', 'Pago #' . $payment->id)

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
        <div>
            <a href="{{ route('central.payments.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Listado</a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">Pago #{{ $payment->id }}</h1>
        </div>
        <a href="{{ route('central.payments.edit', $payment) }}" class="inline-flex px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Editar</a>
    </div>

    <dl class="max-w-2xl bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 text-sm">
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Compañía</dt><dd class="font-medium text-slate-900 text-right">{{ $payment->tenant?->nombre }} <span class="text-slate-500 font-mono text-xs">({{ $payment->tenant_id }})</span></dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Facturación</dt><dd class="text-slate-900 text-right">{{ $payment->billing_id ? '#'.$payment->billing_id : '—' }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Monto</dt><dd class="text-slate-900 text-right">{{ $payment->currency }} {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Método</dt><dd class="text-slate-900 text-right capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Estado</dt><dd class="text-slate-900 text-right font-mono">{{ $payment->status }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Fecha pago</dt><dd class="text-slate-900 text-right">{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Referencia</dt><dd class="text-slate-900 text-right break-all">{{ $payment->reference ?? '—' }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Notas</dt><dd class="text-slate-900 text-right whitespace-pre-wrap max-w-md">{{ $payment->notes ?? '—' }}</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Creado por</dt><dd class="text-slate-900 text-right">{{ $payment->createdBy?->name ?? '—' }} @if($payment->createdBy?->username)<span class="text-slate-500">({{ $payment->createdBy->username }})</span>@endif</dd></div>
        <div class="px-5 py-3 flex justify-between gap-4"><dt class="text-slate-500">Registrado</dt><dd class="text-slate-900 text-right">{{ $payment->created_at?->format('d/m/Y H:i') }}</dd></div>
    </dl>
@endsection
