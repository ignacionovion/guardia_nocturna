<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\OperationalAlertMail;
use App\Models\CentralAdmin;
use App\Models\OperationalAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sincroniza alertas persistentes con OperationalHealthService::dashboardSummary()
 * y envía emails solo en: alerta nueva, escalación warning→critical, resolución (opcional).
 */
final class OperationalAlertService
{
    public function __construct(
        protected OperationalHealthService $health,
    ) {}

    /**
     * @return array{emails_sent: int, open_count: int}
     */
    public function syncFromHealthSummary(bool $sendNotifications = true): array
    {
        $summary = $this->health->dashboardSummary();
        $items = $this->flattenSummaryToItems($summary);
        $emailsSent = 0;

        foreach ($items as $item) {
            $level = $item['level'];
            if ($level === 'ok') {
                $emailsSent += $this->resolveIfOpen($item['alert_key'], $sendNotifications);

                continue;
            }

            $emailsSent += $this->upsertOpenAlert($item, $sendNotifications);
        }

        return [
            'emails_sent' => $emailsSent,
            'open_count' => OperationalAlert::query()->where('status', OperationalAlert::STATUS_OPEN)->count(),
        ];
    }

    /**
     * @return list<array{alert_key: string, source: string, level: string, title: string, message: string, payload: array}>
     */
    private function flattenSummaryToItems(array $summary): array
    {
        $out = [];

        $titles = [
            'scheduler' => 'Scheduler / cron',
            'backup' => 'Backups (tenant:backup)',
            'billing' => 'Billing (check-expiration)',
        ];

        foreach (['scheduler', 'backup', 'billing'] as $k) {
            $block = $summary[$k] ?? [];
            $out[] = [
                'alert_key' => $k,
                'source' => $k,
                'level' => (string) ($block['level'] ?? 'ok'),
                'title' => $titles[$k] ?? $k,
                'message' => (string) ($block['message'] ?? ''),
                'payload' => is_array($block) ? $block : [],
            ];
        }

        $tenantRuns = $summary['tenant_runs'] ?? [];
        foreach ($tenantRuns as $tr) {
            $label = (string) ($tr['label'] ?? 'unknown');
            $key = 'tenant_run:' . str_replace(':', '_', $label);
            $out[] = [
                'alert_key' => $key,
                'source' => 'tenant_run',
                'level' => (string) ($tr['level'] ?? 'ok'),
                'title' => 'tenant:run ' . $label,
                'message' => (string) ($tr['message'] ?? ''),
                'payload' => is_array($tr) ? $tr : [],
            ];
        }

        return $out;
    }

    /**
     * @param  array{alert_key: string, source: string, level: string, title: string, message: string, payload: array}  $item
     */
    private function upsertOpenAlert(array $item, bool $sendNotifications): int
    {
        $emailsSent = 0;
        $severity = $item['level'];
        if (! in_array($severity, [OperationalAlert::SEVERITY_WARNING, OperationalAlert::SEVERITY_CRITICAL], true)) {
            return 0;
        }

        $alert = OperationalAlert::query()->where('alert_key', $item['alert_key'])->first();

        if ($alert === null) {
            $alert = OperationalAlert::query()->create([
                'alert_key' => $item['alert_key'],
                'source' => $item['source'],
                'severity' => $severity,
                'status' => OperationalAlert::STATUS_OPEN,
                'title' => $item['title'],
                'message' => $item['message'],
                'first_triggered_at' => now(),
                'last_triggered_at' => now(),
                'payload' => $item['payload'],
            ]);
            if ($sendNotifications) {
                $emailsSent += $this->sendMail($alert, 'new');
            }
            $this->logEvent('alert_opened', $alert);

            return $emailsSent;
        }

        if ($alert->status === OperationalAlert::STATUS_RESOLVED) {
            $alert->update([
                'status' => OperationalAlert::STATUS_OPEN,
                'severity' => $severity,
                'title' => $item['title'],
                'message' => $item['message'],
                'first_triggered_at' => now(),
                'last_triggered_at' => now(),
                'resolved_at' => null,
                'payload' => $item['payload'],
            ]);
            $alert->refresh();
            if ($sendNotifications) {
                $emailsSent += $this->sendMail($alert, 'new');
            }
            $this->logEvent('alert_reopened', $alert);

            return $emailsSent;
        }

        $alert->message = $item['message'];
        $alert->last_triggered_at = now();
        $alert->payload = $item['payload'];

        $old = $alert->severity;
        if ($old === OperationalAlert::SEVERITY_WARNING && $severity === OperationalAlert::SEVERITY_CRITICAL) {
            $alert->severity = OperationalAlert::SEVERITY_CRITICAL;
            $alert->title = $item['title'];
            $alert->save();
            if ($sendNotifications) {
                $emailsSent += $this->sendMail($alert, 'escalated');
            }
            $this->logEvent('alert_escalated', $alert);

            return $emailsSent;
        }

        if ($old === OperationalAlert::SEVERITY_CRITICAL && $severity === OperationalAlert::SEVERITY_WARNING) {
            $alert->severity = OperationalAlert::SEVERITY_WARNING;
            $alert->title = $item['title'];
        }

        $alert->title = $item['title'];
        $alert->save();

        return 0;
    }

    private function resolveIfOpen(string $alertKey, bool $sendNotifications): int
    {
        $alert = OperationalAlert::query()
            ->where('alert_key', $alertKey)
            ->where('status', OperationalAlert::STATUS_OPEN)
            ->first();

        if ($alert === null) {
            return 0;
        }

        $alert->update([
            'status' => OperationalAlert::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
        $alert->refresh();

        $emailsSent = 0;
        if ($sendNotifications && config('operational_alerts.notify_on_resolve', true)) {
            $emailsSent += $this->sendMail($alert, 'resolved');
        }
        $this->logEvent('alert_resolved', $alert);

        return $emailsSent;
    }

    private function sendMail(OperationalAlert $alert, string $eventType): int
    {
        $subject = 'Alerta operativa SaaS (' . $eventType . ') #' . $alert->id;
        if (! SystemEmailService::ensurePolicyAllows('operational_alert', $subject, null)) {
            return 0;
        }

        $emails = $this->recipientEmails();
        if ($emails === []) {
            Log::warning('[operational_alerts] Sin destinatarios configurados (OPS_ALERT_EMAILS vacío y sin emails en central_admins).');

            return 0;
        }

        Mail::to($emails)->send(new OperationalAlertMail($alert, $eventType));

        $alert->update(['last_notified_at' => now()]);

        return count($emails);
    }

    /**
     * @return list<string>
     */
    private function recipientEmails(): array
    {
        $configured = config('operational_alerts.recipient_emails', []);
        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter($configured, static fn ($e) => is_string($e) && $e !== ''));
        }

        return CentralAdmin::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    private function logEvent(string $action, OperationalAlert $alert): void
    {
        $channel = config('operational_alerts.log_channel');
        if (! is_string($channel) || $channel === '') {
            return;
        }

        try {
            Log::channel($channel)->info("[{$action}] {$alert->alert_key}", [
                'severity' => $alert->severity,
                'status' => $alert->status,
                'title' => $alert->title,
            ]);
        } catch (\Throwable) {
            // Canal inexistente: no romper el flujo
        }
    }
}
