<?php

declare(strict_types=1);

/**
 * Backups de bases de datos por tenant (comando `tenant:backup`).
 *
 * Política de inclusión (ver Tenant::scopeForDatabaseBackup y TenantBackupCommand):
 * - Se respaldan todos los tenants con BD salvo los marcados como cancelados (continuidad + mora).
 * - Los cancelados se excluyen del backup programado: el contrato/plan dejó de aplicar; la retención
 *   legal/archivo histórico debe resolverse con exportación puntual o política aparte.
 * - Con `--tenant=X --include-cancelled` se puede forzar un dump puntual antes de borrar datos.
 */
return [

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 7),

    /*
    | Ruta absoluta del directorio de salida. null = storage_path('app/backups').
    */
    'path' => env('BACKUP_PATH') ?: null,

];
