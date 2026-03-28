<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Checks tenant expiration dates and updates lifecycle states.
 *
 * - Sends warning emails at 7, 3, and 1 day(s) before expiry
 * - Transitions active tenants to 'vencido' when expired
 * - Transitions vencido tenants past grace period to 'suspendido'
 *
 * Scheduled to run daily.
 */
class TenantCheckExpiryCommand extends Command
{
    protected $signature = 'tenant:check-expiry {--dry-run : Show what would happen without making changes}';
    protected $description = 'Check tenant expirations, update states, and send warning emails';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $tenants = Tenant::whereNotNull('fecha_vencimiento')
            ->whereIn('estado', [Tenant::ESTADO_TRIAL, Tenant::ESTADO_ACTIVO, Tenant::ESTADO_VENCIDO])
            ->get();

        $this->info("Checking {$tenants->count()} tenants...");

        $warnings = 0;
        $expired = 0;
        $suspended = 0;

        foreach ($tenants as $tenant) {
            $days = $tenant->daysUntilExpiry();

            if ($days === null) continue;

            // Warning emails: 7, 3, 1 days before expiry
            if ($days > 0 && in_array($days, [7, 3, 1]) && $tenant->isOperational()) {
                $this->warn("  ⚠ {$tenant->nombre} ({$tenant->id}): vence en {$days} día(s)");
                $warnings++;

                if (!$dryRun) {
                    $this->sendExpiryWarning($tenant, $days);
                }
            }

            // Transition: activo/trial → vencido (just expired)
            if ($days <= 0 && $tenant->isOperational()) {
                $graceDays = $tenant->grace_days ?? 5;
                $this->error("  ✗ {$tenant->nombre} ({$tenant->id}): VENCIDO — grace period: {$graceDays} días");
                $expired++;

                if (!$dryRun) {
                    $tenant->estado = Tenant::ESTADO_VENCIDO;
                    $tenant->save();
                    Log::channel('single')->info("Tenant {$tenant->id} transitioned to VENCIDO");
                    $this->sendExpiryNotice($tenant);
                }
            }

            // Transition: vencido → suspendido (past grace period)
            if ($tenant->estado === Tenant::ESTADO_VENCIDO && !$tenant->isInGracePeriod()) {
                $this->error("  ✗ {$tenant->nombre} ({$tenant->id}): grace period terminado → SUSPENDIDO");
                $suspended++;

                if (!$dryRun) {
                    $tenant->estado = Tenant::ESTADO_SUSPENDIDO;
                    $tenant->activo = false;
                    $tenant->save();
                    Log::channel('single')->info("Tenant {$tenant->id} transitioned to SUSPENDIDO (grace expired)");
                }
            }
        }

        $this->newLine();
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Tenants revisados', $tenants->count()],
                ['Avisos enviados', $warnings],
                ['Nuevos vencidos', $expired],
                ['Nuevos suspendidos', $suspended],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN — no se realizaron cambios.');
        }

        return self::SUCCESS;
    }

    protected function sendExpiryWarning(Tenant $tenant, int $days): void
    {
        try {
            $tenant->loadMissing('planRelation');
            $planLabel = $tenant->planRelation?->nombre ?? 'Sin plan asignado';
            $subject = "⚠ Tu plan vence en {$days} día(s) — {$tenant->nombre}";
            $body = "Hola,\n\nTu plan '{$planLabel}' para {$tenant->nombre} vence el {$tenant->fecha_vencimiento->format('d/m/Y')}.\n\nRenueva tu suscripción para evitar la interrupción del servicio.\n\nSaludos,\nGuardiAPP";

            Mail::raw($body, function ($message) use ($tenant, $subject) {
                $message->to(config('mail.admin_email', 'admin@guardianocturna.cl'))
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::channel('single')->error("Failed to send expiry warning for {$tenant->id}: {$e->getMessage()}");
        }
    }

    protected function sendExpiryNotice(Tenant $tenant): void
    {
        try {
            $graceDays = $tenant->grace_days ?? 5;
            $tenant->loadMissing('planRelation');
            $planLabel = $tenant->planRelation?->nombre ?? 'Sin plan asignado';
            $subject = "❌ Plan vencido — {$tenant->nombre}";
            $body = "Hola,\n\nEl plan '{$planLabel}' para {$tenant->nombre} ha vencido el {$tenant->fecha_vencimiento->format('d/m/Y')}.\n\nTienes {$graceDays} días de gracia antes de que la cuenta sea suspendida.\n\nRenueva lo antes posible.\n\nSaludos,\nGuardiAPP";

            Mail::raw($body, function ($message) use ($tenant, $subject) {
                $message->to(config('mail.admin_email', 'admin@guardianocturna.cl'))
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::channel('single')->error("Failed to send expiry notice for {$tenant->id}: {$e->getMessage()}");
        }
    }
}
