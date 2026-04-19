<?php

declare(strict_types=1);

/**
 * Umbrales para clasificación OK / warning / critical en panel y logs.
 * Tiempos sin señal útil ⇒ degradación (cron caído, job no corrido, etc.).
 */
return [

    'scheduler' => [
        'warning_after_minutes' => (int) env('OPS_SCHEDULER_WARN_MINUTES', 5),
        'critical_after_minutes' => (int) env('OPS_SCHEDULER_CRITICAL_MINUTES', 15),
    ],

    'backup' => [
        'warning_after_hours' => (int) env('OPS_BACKUP_WARN_HOURS', 26),
        'critical_after_hours' => (int) env('OPS_BACKUP_CRITICAL_HOURS', 50),
    ],

    'billing' => [
        'warning_after_hours' => (int) env('OPS_BILLING_WARN_HOURS', 26),
        'critical_after_hours' => (int) env('OPS_BILLING_CRITICAL_HOURS', 50),
    ],

    'tenant_run' => [
        'warning_after_minutes' => (int) env('OPS_TENANT_RUN_WARN_MINUTES', 10),
        'critical_after_minutes' => (int) env('OPS_TENANT_RUN_CRITICAL_MINUTES', 30),
    ],

    /*
    | Si true, al pasar a critical se escribe también en storage/logs/laravel.log
    | con contexto (no sustituye el panel).
    */
    'log_critical_to_app_log' => filter_var(env('OPS_LOG_CRITICAL', true), FILTER_VALIDATE_BOOL),

];
