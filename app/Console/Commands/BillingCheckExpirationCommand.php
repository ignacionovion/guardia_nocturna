<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Billing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BillingCheckExpirationCommand extends Command
{
    protected $signature = 'billing:check-expiration';

    protected $description = 'Check for expired billing records, trials, and suspend overdue tenants';

    public function handle(): int
    {
        $this->info('Checking billing expirations and trials...');
        $totalUpdated = 0;
        $totalSuspended = 0;
        $totalTrialsEnded = 0;

        // 1. Check for ended trials: trial → pendiente
        $endedTrials = Billing::where('estado_pago', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($endedTrials as $billing) {
            $billing->finalizarTrial();
            
            Log::info("Trial ended for tenant: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'trial_ended_at' => $billing->trial_ends_at,
            ]);
            
            $totalTrialsEnded++;
            $totalUpdated++;
        }

        $this->info("Ended {$totalTrialsEnded} trials (converted to pending).");

        // 2. Check for expired pending payments: pendiente → vencido
        $expiredBillings = Billing::where('fecha_vencimiento', '<', now())
            ->where('estado_pago', 'pendiente')
            ->get();

        foreach ($expiredBillings as $billing) {
            $billing->update(['estado_pago' => 'vencido']);
            $billing->syncToTenant();

            Log::info("Billing expired: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
            ]);

            $totalUpdated++;
        }

        $this->info("Updated {$expiredBillings->count()} expired billing records to 'vencido'.");

        // 3. Check for overdue (7+ days): vencido → suspendido + suspend tenant
        $overdueBillings = Billing::where('estado_pago', 'vencido')
            ->where('fecha_vencimiento', '<', now()->subDays(7))
            ->get();

        foreach ($overdueBillings as $billing) {
            $billing->update(['estado_pago' => 'suspendido']);
            $billing->syncToTenant();

            Log::warning("Tenant suspended due to non-payment: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
                'days_overdue' => now()->diffInDays($billing->fecha_vencimiento),
            ]);
            
            $totalSuspended++;
            $totalUpdated++;
        }

        $this->info("Suspended {$totalSuspended} tenants due to non-payment (>7 days overdue).");

        // 4. Summary: approaching expiration (within 7 days)
        $approaching = Billing::where('fecha_vencimiento', '<=', now()->addDays(7))
            ->where('fecha_vencimiento', '>=', now())
            ->whereIn('estado_pago', ['pendiente', 'trial'])
            ->count();

        $this->info("{$approaching} tenants approaching expiration within 7 days.");

        // 5. Active trials summary
        $activeTrials = Billing::where('estado_pago', 'trial')
            ->where('trial_ends_at', '>=', now())
            ->count();

        $this->info("{$activeTrials} tenants in active trial period.");

        $this->info("\nTotal: {$totalUpdated} billing records updated.");

        return self::SUCCESS;
    }
}
