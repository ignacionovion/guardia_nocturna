<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

/**
 * Puente entre pagos centralizados (`payments`) y la fuente de verdad comercial (`tenant_billing`).
 */
final class CentralPaymentBillingService
{
    /**
     * Cuando un pago queda {@see Payment::STATUS_PAID}, alinea billing + tenant vía {@see Billing::marcarPagado()}.
     *
     * @throws ValidationException Si no existe facturación para el tenant
     */
    public function syncTenantBillingFromPaidPayment(Payment $payment): void
    {
        if ($payment->status !== Payment::STATUS_PAID || $payment->paid_at === null) {
            return;
        }

        $billing = $this->resolveBilling($payment);
        if ($billing === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'No existe registro de facturación (tenant_billing) para esta compañía. Creá facturación en el panel antes de marcar el pago como pagado.',
            ]);
        }

        if ($payment->billing_id === null) {
            $payment->billing_id = $billing->id;
            $payment->saveQuietly();
        }

        $billing->marcarPagado($payment->paid_at->toDateString());
    }

    private function resolveBilling(Payment $payment): ?Billing
    {
        if ($payment->billing_id) {
            return Billing::query()->whereKey($payment->billing_id)->first();
        }

        return Billing::query()->where('tenant_id', $payment->tenant_id)->first();
    }
}
