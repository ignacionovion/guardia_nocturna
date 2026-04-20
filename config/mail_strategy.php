<?php

declare(strict_types=1);

/**
 * Política global de correos (SaaS central / operación).
 *
 * - enabled: interruptor de despliegue (prioridad sobre toggles UI salvo que indiques lo contrario en código).
 * - category: saas | tenant | operational | manual (metadato / futuro routing).
 * - priority: critical | important | operational | low.
 * - legacy_setting_suffix: clave SystemSetting mail_enabled_{suffix} (null = sin toggle en panel).
 * - recipients_strategy: documentación / futuro (ops_alerts, system_recipients, tenant_admin, etc.).
 *
 * Tipos desconocidos: ver allow_unknown_types (por defecto permitidos para no cortar integraciones legacy).
 */
return [
    'allow_unknown_types' => true,

    /*
     * Normalización: nombres históricos / ambiguos → tipo canónico.
     */
    'aliases' => [
        'beds' => 'bed_report',
        'bed_report' => 'bed_report',
        'cleaning' => 'cleaning_assignment',
        'cleaning_assignment' => 'cleaning_assignment',
        'rotation' => 'guard_rotation',
        'guard_rotation' => 'guard_rotation',
        'novelty' => 'novelty',
        'academy' => 'academy',
        'operational_alert' => 'operational_alert',
        'tenant_expiry_warning' => 'tenant_expiry_warning',
        'tenant_expiry_notice' => 'tenant_expiry_notice',
        'smtp_test' => 'smtp_test',
        'guard_snapshot' => 'guard_snapshot',
    ],

    'types' => [
        'operational_alert' => [
            'enabled' => true,
            'category' => 'saas',
            'priority' => 'critical',
            'legacy_setting_suffix' => null,
            'recipients_strategy' => 'ops_alerts',
        ],
        'tenant_expiry_warning' => [
            'enabled' => true,
            'category' => 'saas',
            'priority' => 'important',
            'legacy_setting_suffix' => null,
            'recipients_strategy' => 'mail_admin_email',
        ],
        'tenant_expiry_notice' => [
            'enabled' => true,
            'category' => 'saas',
            'priority' => 'important',
            'legacy_setting_suffix' => null,
            'recipients_strategy' => 'mail_admin_email',
        ],
        'novelty' => [
            'enabled' => false,
            'category' => 'tenant',
            'priority' => 'low',
            'legacy_setting_suffix' => 'novelty',
            'recipients_strategy' => 'system_recipients',
        ],
        'academy' => [
            'enabled' => false,
            'category' => 'tenant',
            'priority' => 'low',
            'legacy_setting_suffix' => 'academy',
            'recipients_strategy' => 'system_recipients',
        ],
        'cleaning_assignment' => [
            'enabled' => false,
            'category' => 'tenant',
            'priority' => 'operational',
            'legacy_setting_suffix' => 'cleaning',
            'recipients_strategy' => 'system_recipients',
        ],
        'bed_report' => [
            'enabled' => true,
            'category' => 'tenant',
            'priority' => 'operational',
            'legacy_setting_suffix' => 'beds',
            'recipients_strategy' => 'system_recipients',
        ],
        'guard_rotation' => [
            'enabled' => true,
            'category' => 'tenant',
            'priority' => 'operational',
            'legacy_setting_suffix' => 'rotation',
            'recipients_strategy' => 'request_recipients',
        ],
        'smtp_test' => [
            'enabled' => true,
            'category' => 'manual',
            'priority' => 'low',
            'legacy_setting_suffix' => null,
            'recipients_strategy' => 'cli_argument',
        ],
        /*
         * PDF snapshot guardia (no estaba en la lista original; habilitado por defecto para no romper flujo).
         */
        'guard_snapshot' => [
            'enabled' => true,
            'category' => 'tenant',
            'priority' => 'operational',
            'legacy_setting_suffix' => null,
            'recipients_strategy' => 'system_recipients',
        ],
    ],
];
