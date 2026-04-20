<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\CentralPaymentBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly CentralPaymentBillingService $paymentBillingService,
    ) {}

    public function index(): View
    {
        $payments = Payment::query()
            ->with(['tenant:id,nombre', 'billing', 'createdBy:id,name,username'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('central.payments.index', compact('payments'));
    }

    public function create(): View
    {
        $tenants = Tenant::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $billings = Billing::query()
            ->with('tenant:id,nombre')
            ->orderBy('tenant_id')
            ->get(['id', 'tenant_id']);

        $paymentMethods = $this->paymentMethodOptions();
        $payment = new Payment();

        return view('central.payments.create', compact('tenants', 'billings', 'paymentMethods', 'payment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePaymentPayload($request, creating: true);

        try {
            DB::connection('central')->transaction(function () use ($validated): void {
                $payment = Payment::query()->create($validated);
                if ($payment->status === Payment::STATUS_PAID) {
                    $this->paymentBillingService->syncTenantBillingFromPaidPayment($payment);
                }
            });
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('central.payments.index')
            ->with('success', 'Pago registrado correctamente.');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['tenant:id,nombre', 'billing', 'createdBy:id,name,username']);

        return view('central.payments.show', compact('payment'));
    }

    public function edit(Payment $payment): View
    {
        $payment->load(['tenant:id,nombre', 'billing']);
        $tenants = Tenant::query()->orderBy('nombre')->get(['id', 'nombre']);
        $billings = Billing::query()
            ->with('tenant:id,nombre')
            ->orderBy('tenant_id')
            ->get(['id', 'tenant_id']);
        $paymentMethods = $this->paymentMethodOptions();

        return view('central.payments.edit', compact('payment', 'tenants', 'billings', 'paymentMethods'));
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status === Payment::STATUS_PAID && $request->input('status') !== Payment::STATUS_PAID) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['status' => 'No se puede revertir un pago ya marcado como pagado.']);
        }

        $wasPaid = $payment->status === Payment::STATUS_PAID;
        $validated = $this->validatePaymentPayload($request, creating: false, payment: $payment);

        try {
            DB::connection('central')->transaction(function () use ($payment, $validated, $wasPaid): void {
                $payment->fill($validated);
                $payment->save();

                if (! $wasPaid && $payment->status === Payment::STATUS_PAID) {
                    $this->paymentBillingService->syncTenantBillingFromPaidPayment($payment->fresh());
                }
            });
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('central.payments.index')
            ->with('success', 'Pago actualizado.');
    }

    public function markPaid(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status === Payment::STATUS_PAID) {
            return redirect()
                ->route('central.payments.edit', $payment)
                ->with('error', 'Este pago ya está en estado pagado.');
        }

        $validated = $request->validate([
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:64', Rule::in(array_keys($this->paymentMethodOptions()))],
            'reference' => ['nullable', 'string', 'max:190'],
        ]);

        try {
            DB::connection('central')->transaction(function () use ($payment, $validated): void {
                $payment->status = Payment::STATUS_PAID;
                $payment->paid_at = $validated['paid_at'];
                $payment->payment_method = $validated['payment_method'];
                if (array_key_exists('reference', $validated)) {
                    $payment->reference = $validated['reference'];
                }
                $payment->save();
                $this->paymentBillingService->syncTenantBillingFromPaidPayment($payment->fresh());
            });
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('central.payments.index')
            ->with('success', 'Pago marcado como pagado y facturación sincronizada.');
    }

    /**
     * @return array<string, string>
     */
    private function paymentMethodOptions(): array
    {
        return [
            'transferencia' => 'Transferencia',
            'efectivo' => 'Efectivo',
            'webpay' => 'Webpay',
            'tarjeta' => 'Tarjeta',
            'cheque' => 'Cheque',
            'paypal' => 'PayPal',
            'otro' => 'Otro',
        ];
    }

    private function validatePaymentPayload(Request $request, bool $creating, ?Payment $payment = null): array
    {
        if ($payment && $payment->status === Payment::STATUS_PAID) {
            return $request->validate([
                'reference' => ['nullable', 'string', 'max:190'],
                'notes' => ['nullable', 'string', 'max:5000'],
            ]);
        }

        $statusRule = Rule::in([
            Payment::STATUS_PENDING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_CANCELLED,
        ]);

        $rules = [
            'tenant_id' => [$creating ? 'required' : 'sometimes', 'string', 'exists:tenants,id'],
            'billing_id' => ['nullable', 'integer', 'exists:tenant_billing,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:8'],
            'payment_method' => ['required', 'string', 'max:64', Rule::in(array_keys($this->paymentMethodOptions()))],
            'status' => ['required', 'string', 'max:32', $statusRule],
            'reference' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'paid_at' => ['nullable', 'date'],
        ];

        $data = $request->validate($rules);

        if (($data['status'] ?? '') === Payment::STATUS_PAID && empty($data['paid_at'])) {
            throw ValidationException::withMessages([
                'paid_at' => 'La fecha de pago es obligatoria cuando el estado es pagado.',
            ]);
        }

        if (($data['status'] ?? '') !== Payment::STATUS_PAID) {
            $data['paid_at'] = null;
        }

        $data['currency'] = strtoupper((string) ($data['currency'] ?? 'CLP'));
        if ($data['currency'] === '') {
            $data['currency'] = 'CLP';
        }

        if ($creating) {
            $data['created_by_central_admin_id'] = Auth::guard('central')->id();
        } else {
            unset($data['created_by_central_admin_id']);
        }

        if (! empty($data['billing_id'])) {
            $billing = Billing::query()->find((int) $data['billing_id']);
            $tenantId = $data['tenant_id'] ?? $payment?->tenant_id;
            if ($billing && $tenantId !== null && $billing->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'billing_id' => 'La facturación seleccionada no corresponde al tenant indicado.',
                ]);
            }
        }

        return $data;
    }
}

