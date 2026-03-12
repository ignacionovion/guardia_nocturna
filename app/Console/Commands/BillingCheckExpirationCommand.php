<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Billing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BillingCheckExpirationCommand extends Command
{
    protected $signature = 'billing:check-expiration';

    protected $description = 'Check for expired billing records and update their status';

    public function handle(): int
    {
        $this->info('Checking billing expirations...');

        // Find all billing records with due date in the past and status not 'pagado' or 'suspendido'
        $expiredBillings = Billing::where('fecha_vencimiento', '<', now())
            ->whereNotIn('estado_pago', ['pagado', 'suspendido'])
            ->get();

        $count = 0;
        foreach ($expiredBillings as $billing) {
            $billing->update(['estado_pago' => 'vencido']);
            
            Log::info("Billing record expired: {$billing->tenant_id}", [
                'billing_id' => $billing->id,
                'tenant_id' => $billing->tenant_id,
                'fecha_vencimiento' => $billing->fecha_vencimiento,
            ]);
            
            $count++;
        }

        $this->info("Updated {$count} expired billing records.");

        // Also check for tenants approaching expiration (within 7 days)
        $approaching = Billing::where('fecha_vencimiento', '<=', now()->addDays(7))
            ->where('fecha_vencimiento', '>=', now())
            ->where('estado_pago', 'pendiente')
            ->count();

        $this->info("{$approaching} tenants approaching expiration within 7 days.");

        return self::SUCCESS;
    }
}
