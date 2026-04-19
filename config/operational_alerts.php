<?php

declare(strict_types=1);

return [

    /*
    | Lista explícita de emails (separados por coma). Si está vacío, se usan
    | todos los central_admins con email no vacío.
    */
    'recipient_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OPS_ALERT_EMAILS', ''))
    ))),

    'notify_on_resolve' => filter_var(env('OPS_ALERT_NOTIFY_RESOLVE', true), FILTER_VALIDATE_BOOL),

    /*
    | URL del panel central para enlaces en emails (ej. https://central.tudominio.com/admin).
    | Si null, se arma con APP_URL + /admin.
    */
    'panel_url' => env('OPS_ALERT_PANEL_URL'),

    /*
    | Canal de log Monolog para auditoría de envíos (opcional).
    */
    'log_channel' => env('OPS_ALERT_LOG_CHANNEL', 'operational_alerts'),

];
