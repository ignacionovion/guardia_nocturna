@php
    $methods = $paymentMethods ?? [];
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Compañía (tenant)</label>
        <select name="tenant_id" id="tenant_id" class="w-full rounded-xl border-slate-300 text-sm" {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'disabled' : 'required' }}>
            <option value="">— Seleccionar —</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected(old('tenant_id', $payment->tenant_id ?? '') === $t->id)>{{ $t->nombre }} ({{ $t->id }})</option>
            @endforeach
        </select>
        @if(($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID)
            <input type="hidden" name="tenant_id" value="{{ $payment->tenant_id }}">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Facturación (opcional)</label>
        <select name="billing_id" class="w-full rounded-xl border-slate-300 text-sm" {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'disabled' : '' }}>
            <option value="">— Sin vincular / detectar automático al pagar —</option>
            @foreach($billings as $b)
                <option value="{{ $b->id }}" data-tenant="{{ $b->tenant_id }}" @selected(old('billing_id', $payment->billing_id ?? '') == $b->id)>
                    {{ $b->tenant?->nombre ?? $b->tenant_id }} — billing #{{ $b->id }}
                </option>
            @endforeach
        </select>
        @if(($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID)
            <input type="hidden" name="billing_id" value="{{ $payment->billing_id }}">
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Monto</label>
            <input type="number" name="amount" step="0.01" min="0.01" class="w-full rounded-xl border-slate-300 text-sm" required
                   value="{{ old('amount', $payment->amount ?? '') }}" {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'readonly' : '' }}>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Moneda</label>
            <input type="text" name="currency" maxlength="8" class="w-full rounded-xl border-slate-300 text-sm uppercase"
                   value="{{ old('currency', ($payment->currency ?? '') !== '' ? $payment->currency : 'CLP') }}" {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'readonly' : '' }}>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Método de pago</label>
        <select name="payment_method" class="w-full rounded-xl border-slate-300 text-sm" required {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'disabled' : '' }}>
            @foreach($methods as $value => $label)
                <option value="{{ $value }}" @selected(old('payment_method', $payment->payment_method ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @if(($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID)
            <input type="hidden" name="payment_method" value="{{ $payment->payment_method }}">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
        <select name="status" id="payment_status" class="w-full rounded-xl border-slate-300 text-sm" required {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'disabled' : '' }}>
            @foreach(['pending' => 'Pendiente', 'paid' => 'Pagado', 'failed' => 'Fallido', 'cancelled' => 'Cancelado'] as $val => $lab)
                <option value="{{ $val }}" @selected(old('status', $payment->status ?? 'pending') === $val)>{{ $lab }}</option>
            @endforeach
        </select>
        @if(($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID)
            <input type="hidden" name="status" value="{{ $payment->status }}">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de pago (obligatoria si estado = pagado)</label>
        <input type="date" name="paid_at" id="paid_at" class="w-full rounded-xl border-slate-300 text-sm"
               value="{{ old('paid_at', isset($payment) && $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '') }}" {{ ($payment ?? null)?->status === \App\Models\Payment::STATUS_PAID ? 'readonly' : '' }}>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Referencia</label>
        <input type="text" name="reference" maxlength="190" class="w-full rounded-xl border-slate-300 text-sm"
               value="{{ old('reference', $payment->reference ?? '') }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
        <textarea name="notes" rows="3" class="w-full rounded-xl border-slate-300 text-sm">{{ old('notes', $payment->notes ?? '') }}</textarea>
    </div>
</div>

<script>
    (function () {
        var st = document.getElementById('payment_status');
        var dt = document.getElementById('paid_at');
        if (!st || !dt) return;
        function sync() {
            dt.required = (st.value === 'paid');
        }
        st.addEventListener('change', sync);
        sync();
    })();
</script>
