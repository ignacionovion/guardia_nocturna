<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Zona de peligro (limpieza masiva SaaS)
    |--------------------------------------------------------------------------
    |
    | Por defecto deshabilitado en entornos distintos de "local" para evitar
    | ejecución accidental en producción. En staging/VPS debe activarse
    | explícitamente con SAAS_DANGER_ZONE_ENABLED=true en .env
    |
    */
    'enabled' => filter_var(env('SAAS_DANGER_ZONE_ENABLED', false), FILTER_VALIDATE_BOOL)
        || in_array(env('APP_ENV', 'production'), ['local', 'testing'], true),
];
