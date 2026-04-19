<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Billing;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Única transición automática de estado comercial: lee/escribe tenant_billing y sincroniza tenants.
 */
class BillingCheckExpirationCommand extends Command
{
    protected $signature = 'billing:check-expiration';

    protected $description = 'Trial, vencimientos, períodos pagados y suspensión por gracia (tenant_billing → tenants)';

    public function handle(): int
    {
        $this->info('Checking billing expirations and trials...');
        $totalUpdated = 0;
        $totalSuspended = 0;
        $totalTrialsEnded = 0;
        $lapsedPaid = 0;

        $today = Carbon::today();

        // 1. Trial terminado → pendiente (+ fecha de vencimiento inicial)
        $endedTrials = Billing::where('estado_pago', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<', $today)
            ->get();

        foreach ($endedTrials as $billing) {
            $billing->finalizarTrial();

            Log::info("Trial ended for tenant: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
            ]);

            $totalTrialsEnded++;
            $totalUpdated++;
        }

        $this->info("Ended {$totalTrialsEnded} trials (converted to pending).");

        // 2. Período pagado vencido → pendiente (debe renovar / registrar cobro)
        $lapsedPaidBillings = Billing::where('estado_pago', 'pagado')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $today)
            ->get();

        foreach ($lapsedPaidBillings as $billing) {
            $billing->update(['estado_pago' => 'pendiente']);
            $billing->refresh();
            $billing->syncToTenant();

            Log::info("Paid period lapsed → pending: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
            ]);

            $lapsedPaid++;
            $totalUpdated++;
        }

        $this->info("Marked {$lapsedPaid} paid periods as pending (renewal required).");

        // 3. Pendiente con fecha pasada → vencido
        $expiredBillings = Billing::where('estado_pago', 'pendiente')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $today)
            ->get();

        foreach ($expiredBillings as $billing) {
            $billing->update(['estado_pago' => 'vencido']);
            $billing->refresh();
            $billing->syncToTenant();

            Log::info("Billing expired (pending → vencido): {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
            ]);

            $totalUpdated++;
        }

        $this->info("Updated {$expiredBillings->count()} pending records to 'vencido'.");

        // 4. Vencido + gracia agotada → suspendido (usa tenants.grace_days y config)
        $vencidos = Billing::where('estado_pago', 'vencido')
            ->whereNotNull('fecha_vencimiento')
            ->with('tenant')
            ->get();

        foreach ($vencidos as $billing) {
            if ($this->graceStillActive($billing, $today)) {
                continue;
            }

            $billing->suspender();

            Log::warning("Tenant suspended due to non-payment (grace ended): {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
            ]);

            $totalSuspended++;
            $totalUpdated++;
        }

        $this->info("Suspended {$totalSuspended} tenants after grace period.");

        // 5. Resumen
        $approaching = Billing::where('fecha_vencimiento', '<=', now()->addDays(7))
            ->where('fecha_vencimiento', '>=', now())
            ->whereIn('estado_pago', ['pendiente', 'trial', 'pagado'])
            ->count();

        $this->info("{$approaching} tenants approaching expiration within 7 days.");

        $activeTrials = Billing::where('estado_pago', 'trial')
            ->where(function ($q) use ($today) {
                $q->whereNull('trial_ends_at')
                    ->orWhereDate('trial_ends_at', '>=', $today);
            })
            ->count();

        $this->info("{$activeTrials} tenants in active trial period.");

        $this->info("\nTotal: {$totalUpdated} billing records updated.");

        return self::SUCCESS;
    }

    private function graceStillActive(Billing $billing, Carbon $today): bool
    {
        $due = $billing->fecha_vencimiento;
        if (!$due) {
            return true;
        }

        /** @var Tenant|null $tenant */
        $tenant = $billing->tenant;
        $graceDays = max(
            (int) config('billing.grace_days_after_due', 5),
            (int) ($tenant?->grace_days ?? 0)
        );

        $graceEnd = $due->copy()->startOfDay()->addDays($graceDays);

        return $today->lte($graceEnd);
    }
}
