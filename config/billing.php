<?php

declare(strict_types=1);

/**
 * Reglas operativas SaaS (staging/producción).
 *
 * Fuente de verdad comercial: tabla `tenant_billing` (modelo Billing).
 * Réplica operativa / acceso: tabla `tenants` (sincronizada vía Billing::syncToTenant()).
 *
 * Estados en tenant_billing.estado_pago:
 * - trial: período de prueba; fecha efectiva de fin = trial_ends_at
 * - pagado: período corriente abonado; fecha de fin de período = fecha_vencimiento
 * - pendiente: sin pago confirmado para el ciclo actual o período vencido sin registrar cobro
 * - vencido: pasó fecha_vencimiento sin estar al día según reglas del comando de expiración
 * - suspendido: bloqueo por mora prolongada (post gracia) o acción manual equivalente
 *
 * "activo" en tenants (acceso app): activo=true salvo suspendido (y excepciones legacy activo=false).
 * "vencido" en tenants.estado: mora dentro de gracia — la app puede permitir acceso si isInGracePeriod().
 * "suspendido": sin acceso; activo=false.
 */
return [

    /*
    | Días de gracia después de la fecha de vencimiento (tenant_billing.fecha_vencimiento /
    | tenants.fecha_vencimiento alineada) antes de pasar a suspendido.
    | Por defecto coincide con tenants.grace_days (migración default 5); el comando usa el mayor
    | entre el valor del tenant y este mínimo operativo si definís política central.
    */
    'grace_days_after_due' => (int) env('BILLING_GRACE_DAYS', 5),

    /*
    | Días tras finalizar trial antes de exigir primera facturación (fecha_vencimiento inicial).
    */
    'trial_to_pending_due_days' => (int) env('BILLING_TRIAL_TO_PENDING_DUE_DAYS', 30),

    /*
    | Onboarding al crear una compañía (panel central): si es true, todas las altas inician en trial
    | con la duración definida en default_trial_days (sin entrada manual de vencimiento).
    | Si es false, el operador puede marcar trial opcional en el formulario; la duración sigue
    | tomándose solo desde default_trial_days (backend), no desde un campo libre.
    */
    'enabled_trial_on_create' => (bool) env('BILLING_ENABLED_TRIAL_ON_CREATE', false),

    /*
    | Días de trial aplicados al crear compañía cuando corresponde trial (política o checkbox).
    */
    'default_trial_days' => (int) env('BILLING_DEFAULT_TRIAL_DAYS', 14),

];
