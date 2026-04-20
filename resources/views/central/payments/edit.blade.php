@extends('central.layouts.app')

@section('title', 'Editar pago #' . $payment->id)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.payments.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Volver al listado</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Editar pago #{{ $payment->id }}</h1>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl border border-slate-200 p-6 mb-8">
        <form method="POST" action="{{ route('central.payments.update', $payment) }}">
            @csrf
            @method('PUT')
            @include('central.payments._form_fields')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('central.payments.show', $payment) }}" class="px-4 py-2 text-slate-600 hover:text-slate-900 text-sm">Ver</a>
                <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Guardar cambios</button>
            </div>
        </form>
    </div>

    @if($payment->status !== \App\Models\Payment::STATUS_PAID)
        <div class="max-w-2xl bg-emerald-50 border border-emerald-200 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-emerald-900 mb-3">Marcar como pagado</h2>
            <p class="text-xs text-emerald-800 mb-4">Confirma fecha y método; se ejecutará la misma sincronización que en facturación (<code>marcarPagado</code> + <code>syncToTenant</code>).</p>
            <form method="POST" action="{{ route('central.payments.mark-paid', $payment) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-emerald-900 mb-1">Fecha de pago</label>
                    <input type="date" name="paid_at" required value="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border-emerald-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-emerald-900 mb-1">Método</label>
                    <select name="payment_method" required class="w-full rounded-xl border-emerald-300 text-sm">
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected($value === $payment->payment_method)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-emerald-900 mb-1">Referencia (opcional)</label>
                    <input type="text" name="reference" maxlength="190" class="w-full rounded-xl border-emerald-300 text-sm" value="{{ $payment->reference }}">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="px-4 py-2.5 bg-emerald-700 text-white text-sm font-medium rounded-xl hover:bg-emerald-800">Marcar pagado y sincronizar</button>
                </div>
            </form>
        </div>
    @endif
@endsection
