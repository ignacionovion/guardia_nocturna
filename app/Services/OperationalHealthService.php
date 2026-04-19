<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OperationalMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Persistencia y lectura de métricas operativas (scheduler, backups, billing, tenant:run).
 */
final class OperationalHealthService
{
    public const KEY_SCHEDULER_HEARTBEAT = 'scheduler_heartbeat';

    public const KEY_BACKUP_JOB = 'backup_job';

    public const KEY_BILLING_EXPIRATION = 'billing_expiration';

    public const PREFIX_TENANT_RUN = 'tenant_run:';

    public function touchSchedulerHeartbeat(): void
    {
        $this->upsert(self::KEY_SCHEDULER_HEARTBEAT, [
            'at' => now()->toIso8601String(),
            'host' => php_uname('n'),
        ]);
    }

    /**
     * @param  array{ok: int, failed: int, duration_ms: int, exit_success: bool, backup_dir?: string}  $data
     */
    public function recordBackupRun(array $data): void
    {
        $payload = array_merge($data, [
            'at' => now()->toIso8601String(),
        ]);
        $this->upsert(self::KEY_BACKUP_JOB, $payload);
        if (! ($data['exit_success'] ?? false)) {
            $this->maybeLogCritical('backup', 'Backup job no exitoso', $payload);
        }
    }

    /**
     * @param  array{exit_success: bool, trials_ended?: int, lapsed_paid?: int, expired_pending?: int, suspended?: int, duration_ms?: int}  $data
     */
    public function recordBillingExpiration(array $data): void
    {
        $payload = array_merge($data, [
            'at' => now()->toIso8601String(),
        ]);
        $this->upsert(self::KEY_BILLING_EXPIRATION, $payload);
        if (! ($data['exit_success'] ?? true)) {
            $this->maybeLogCritical('billing', 'billing:check-expiration terminó con error', $payload);
        }
    }

    /**
     * @param  array{tenants: int, failed_inner: int, exit_code: int}  $data
     */
    public function recordTenantRun(string $tenantCommand, array $data): void
    {
        $key = $this->tenantRunKey($tenantCommand);
        $payload = array_merge($data, [
            'at' => now()->toIso8601String(),
            'command' => $tenantCommand,
        ]);
        $this->upsert($key, $payload);
        if (($data['exit_code'] ?? 0) !== 0 || ($data['failed_inner'] ?? 0) > 0) {
            $this->maybeLogCritical('tenant_run', "tenant:run {$tenantCommand} reporta fallos", $payload);
        }
    }

    public function tenantRunKey(string $tenantCommand): string
    {
        return self::PREFIX_TENANT_RUN . str_replace(':', '_', $tenantCommand);
    }

    /**
     * Resumen para panel central: niveles y últimos timestamps.
     *
     * @return array{
     *   overall: string,
     *   scheduler: array{level: string, message: string, last_at: ?string, age_minutes: ?float},
     *   backup: array{level: string, message: string, last_at: ?string, payload: ?array},
     *   billing: array{level: string, message: string, last_at: ?string, payload: ?array},
     *   tenant_runs: array<int, array{key: string, label: string, level: string, message: string, last_at: ?string, payload: ?array}>
     * }
     */
    public function dashboardSummary(): array
    {
        $scheduler = $this->evaluateScheduler();
        $backup = $this->evaluateBackup();
        $billing = $this->evaluateBilling();
        $tenantRuns = $this->evaluateGuardiaTenantRuns();

        $levels = array_merge(
            [$scheduler['level'], $backup['level'], $billing['level']],
            array_column($tenantRuns, 'level')
        );
        $overall = $this->mergeLevels($levels);

        return [
            'overall' => $overall,
            'scheduler' => $scheduler,
            'backup' => $backup,
            'billing' => $billing,
            'tenant_runs' => $tenantRuns,
        ];
    }

    private function upsert(string $key, array $payload): void
    {
        OperationalMetric::query()->updateOrCreate(
            ['metric_key' => $key],
            ['payload' => $payload]
        );
    }

    private function getPayload(string $key): ?array
    {
        $row = OperationalMetric::query()->where('metric_key', $key)->first();

        return $row?->payload;
    }

    private function evaluateScheduler(): array
    {
        $p = $this->getPayload(self::KEY_SCHEDULER_HEARTBEAT);
        $at = isset($p['at']) ? Carbon::parse($p['at']) : null;
        $warnM = (int) config('operational.scheduler.warning_after_minutes', 5);
        $critM = (int) config('operational.scheduler.critical_after_minutes', 15);

        if ($at === null) {
            return [
                'level' => 'critical',
                'message' => 'Sin heartbeat del scheduler (¿migración no corrida o cron caído desde el deploy).',
                'last_at' => null,
                'age_minutes' => null,
            ];
        }

        $ageMin = now()->diffInMinutes($at, true);

        if ($ageMin >= $critM) {
            return [
                'level' => 'critical',
                'message' => "Último schedule:run hace ~{$ageMin} min (umbral critical ≥{$critM} min).",
                'last_at' => $at->toIso8601String(),
                'age_minutes' => $ageMin,
            ];
        }

        if ($ageMin >= $warnM) {
            return [
                'level' => 'warning',
                'message' => "Último schedule:run hace ~{$ageMin} min (umbral warning ≥{$warnM} min).",
                'last_at' => $at->toIso8601String(),
                'age_minutes' => $ageMin,
            ];
        }

        return [
            'level' => 'ok',
            'message' => 'Scheduler con señal reciente.',
            'last_at' => $at->toIso8601String(),
            'age_minutes' => $ageMin,
        ];
    }

    private function evaluateBackup(): array
    {
        $p = $this->getPayload(self::KEY_BACKUP_JOB);
        $warnH = (int) config('operational.backup.warning_after_hours', 26);
        $critH = (int) config('operational.backup.critical_after_hours', 50);

        if ($p === null) {
            return [
                'level' => 'warning',
                'message' => 'Aún no hay registro de tenant:backup (ejecutá uno o esperá el cron).',
                'last_at' => null,
                'payload' => null,
            ];
        }

        $at = isset($p['at']) ? Carbon::parse($p['at']) : null;
        if ($at === null) {
            return ['level' => 'warning', 'message' => 'Payload de backup incompleto.', 'last_at' => null, 'payload' => $p];
        }

        $ageH = $at->diffInHours(now(), true);
        $failed = (int) ($p['failed'] ?? 0);
        $ok = (bool) ($p['exit_success'] ?? true);

        if (! $ok) {
            return [
                'level' => 'critical',
                'message' => 'Último job de backup falló (preflight, mysqldump o fallos de tenant).',
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        if ($failed > 0) {
            return [
                'level' => 'warning',
                'message' => "Último run con {$failed} tenant(s) fallidos (job terminó con código error).",
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        if ($ageH >= $critH) {
            return [
                'level' => 'critical',
                'message' => "Sin backup exitoso reciente (~{$ageH} h, critical ≥{$critH} h).",
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        if ($ageH >= $warnH) {
            return [
                'level' => 'warning',
                'message' => "Último backup hace ~{$ageH} h (warning ≥{$warnH} h).",
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        return [
            'level' => 'ok',
            'message' => 'Backup reciente y OK.',
            'last_at' => $at->toIso8601String(),
            'payload' => $p,
        ];
    }

    private function evaluateBilling(): array
    {
        $p = $this->getPayload(self::KEY_BILLING_EXPIRATION);
        $warnH = (int) config('operational.billing.warning_after_hours', 26);
        $critH = (int) config('operational.billing.critical_after_hours', 50);

        if ($p === null) {
            return [
                'level' => 'warning',
                'message' => 'Sin registro de billing:check-expiration.',
                'last_at' => null,
                'payload' => null,
            ];
        }

        $at = isset($p['at']) ? Carbon::parse($p['at']) : null;
        $ok = (bool) ($p['exit_success'] ?? true);

        if ($at === null) {
            return ['level' => 'warning', 'message' => 'Payload billing incompleto.', 'last_at' => null, 'payload' => $p];
        }

        $ageH = $at->diffInHours(now(), true);

        if ($ageH >= $critH || ! $ok) {
            return [
                'level' => 'critical',
                'message' => ! $ok
                    ? 'Última corrida de billing falló.'
                    : "Sin billing:check-expiration reciente (~{$ageH} h).",
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        if ($ageH >= $warnH) {
            return [
                'level' => 'warning',
                'message' => "Última corrida hace ~{$ageH} h (warning ≥{$warnH} h).",
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        return [
            'level' => 'ok',
            'message' => 'Billing reciente.',
            'last_at' => $at->toIso8601String(),
            'payload' => $p,
        ];
    }

    /**
     * @return list<array{key: string, label: string, level: string, message: string, last_at: ?string, payload: ?array}>
     */
    private function evaluateGuardiaTenantRuns(): array
    {
        $commands = [
            'guardia:expire-replacements',
            'guardia:run-calendar',
            'guardia:reset-beds',
            'guardia:generate-notifications',
            'guardia:daily-cleanup',
        ];

        $warnM = (int) config('operational.tenant_run.warning_after_minutes', 10);
        $critM = (int) config('operational.tenant_run.critical_after_minutes', 30);

        $out = [];
        foreach ($commands as $cmd) {
            $key = $this->tenantRunKey($cmd);
            $p = $this->getPayload($key);
            if ($p === null) {
                $out[] = [
                    'key' => $key,
                    'label' => $cmd,
                    'level' => 'warning',
                    'message' => 'Sin ejecución registrada aún.',
                    'last_at' => null,
                    'payload' => null,
                ];

                continue;
            }

            $at = isset($p['at']) ? Carbon::parse($p['at']) : null;
            if ($at === null) {
                $out[] = [
                    'key' => $key,
                    'label' => $cmd,
                    'level' => 'warning',
                    'message' => 'Payload incompleto.',
                    'last_at' => null,
                    'payload' => $p,
                ];

                continue;
            }

            $ageMin = now()->diffInMinutes($at, true);
            $failedInner = (int) ($p['failed_inner'] ?? 0);
            $exitCode = (int) ($p['exit_code'] ?? 0);

            if ($ageMin >= $critM || $exitCode !== 0) {
                $level = 'critical';
                $msg = $exitCode !== 0
                    ? "Última ejecución con código {$exitCode}."
                    : "Sin señal hace ~{$ageMin} min (critical ≥{$critM}).";
            } elseif ($ageMin >= $warnM || $failedInner > 0) {
                $level = 'warning';
                $msg = $failedInner > 0
                    ? "Fallos internos en tenants: {$failedInner}."
                    : "Última ejecución hace ~{$ageMin} min (warning ≥{$warnM}).";
            } else {
                $level = 'ok';
                $msg = 'OK';
            }

            $out[] = [
                'key' => $key,
                'label' => $cmd,
                'level' => $level,
                'message' => $msg,
                'last_at' => $at->toIso8601String(),
                'payload' => $p,
            ];
        }

        return $out;
    }

    /**
     * @param  list<string>  $levels
     */
    private function mergeLevels(array $levels): string
    {
        if (in_array('critical', $levels, true)) {
            return 'critical';
        }
        if (in_array('warning', $levels, true)) {
            return 'warning';
        }

        return 'ok';
    }

    private function maybeLogCritical(string $area, string $message, array $payload): void
    {
        if (! config('operational.log_critical_to_app_log', true)) {
            return;
        }

        Log::warning('[operational] ' . $message, [
            'area' => $area,
            'payload' => $payload,
        ]);
    }
}
