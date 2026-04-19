<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;
use Carbon\Carbon;

/**
 * Copy y flags de UX comercial (solo lectura del estado; no altera facturación).
 */
final class TenantSubscriptionUx
{
    /**
     * Banner bajo el topbar en layouts autenticados (trial, gracia, vencimiento próximo).
     */
    public static function forLayout(?Tenant $tenant): array
    {
        if (! $tenant || ! auth()->check()) {
            return self::emptyLayout();
        }

        $tenant->loadMissing('billing');
        $billing = $tenant->billing;
        $estado = $tenant->estado ?? Tenant::ESTADO_ACTIVO;

        if ($estado === Tenant::ESTADO_TRIAL || ($billing && $billing->estado_pago === 'trial')) {
            $trialEnd = $billing?->trial_ends_at ?? $tenant->fecha_vencimiento;
            $daysRemain = self::wholeDaysUntil($trialEnd);
            $hint = null;
            if ($daysRemain !== null) {
                if ($daysRemain === 0) {
                    $hint = 'El período de prueba vence hoy.';
                } elseif ($daysRemain <= 7) {
                    $hint = 'Quedan aproximadamente '.$daysRemain.' día(s) de prueba.';
                }
            }

            $topbarLine = $trialEnd instanceof Carbon
                ? 'Prueba activa · hasta '.$trialEnd->format('d/m/Y')
                : 'Período de prueba activo';

            return [
                'show_banner' => true,
                'show_topbar_hint' => true,
                'topbar_line' => $topbarLine,
                'variant' => 'trial',
                'title' => 'Período de prueba',
                'body' => $trialEnd instanceof Carbon
                    ? 'Estás usando GuardiAPP sin cargo hasta el '.$trialEnd->format('d/m/Y').'.'
                    : 'Estás usando GuardiAPP en período de prueba.',
                'hint' => $hint,
                'cta_route' => route('tenant.upgrade'),
                'cta_label' => 'Ver planes',
            ];
        }

        if ($estado === Tenant::ESTADO_VENCIDO && $tenant->isInGracePeriod()) {
            $graceEnd = $tenant->gracePeriodEndsAt();
            $daysRemain = self::wholeDaysUntil($graceEnd);
            $hint = null;
            if ($daysRemain !== null) {
                $hint = $daysRemain === 0
                    ? 'Último día del período de regularización.'
                    : 'Tiempo aproximado para regularizar: '.$daysRemain.' día(s).';
            }

            $topbarLine = 'Plan vencido · regularizar pago';
            if ($daysRemain !== null && $daysRemain <= 7) {
                $topbarLine .= $daysRemain === 0 ? ' · hoy' : ' · ~'.$daysRemain.'d';
            }

            return [
                'show_banner' => true,
                'show_topbar_hint' => true,
                'topbar_line' => $topbarLine,
                'variant' => 'grace',
                'title' => 'Plan vencido — podés seguir operando por tiempo limitado',
                'body' => 'Tu suscripción está vencida. Regularizá el pago pronto para no interrumpir el servicio.',
                'hint' => $hint,
                'cta_route' => route('tenant.upgrade'),
                'cta_label' => 'Ver opciones',
            ];
        }

        if (
            $billing
            && $billing->estado_pago === 'pendiente'
            && $tenant->fecha_vencimiento instanceof Carbon
            && $tenant->fecha_vencimiento->isFuture()
            && in_array($estado, [Tenant::ESTADO_ACTIVO, Tenant::ESTADO_TRIAL], true)
        ) {
            $daysRemain = self::wholeDaysUntil($tenant->fecha_vencimiento);
            if ($daysRemain !== null && $daysRemain <= 7) {
                return [
                    'show_banner' => true,
                    'show_topbar_hint' => $daysRemain <= 3,
                    'topbar_line' => 'Vencimiento próximo · '.$tenant->fecha_vencimiento->format('d/m/Y'),
                    'variant' => 'due_soon',
                    'title' => 'Próximo vencimiento',
                    'body' => 'La fecha de tu suscripción es el '.$tenant->fecha_vencimiento->format('d/m/Y').'.',
                    'hint' => $daysRemain === 0
                        ? 'Vence hoy.'
                        : 'Quedan '.$daysRemain.' día(s).',
                    'cta_route' => route('tenant.upgrade'),
                    'cta_label' => 'Revisar plan',
                ];
            }
        }

        return self::emptyLayout();
    }

    /**
     * Avisos en la página /upgrade (incluye suspendido y vencido fuera de gracia).
     */
    public static function forUpgrade(?Tenant $tenant): array
    {
        if (! $tenant) {
            return ['panels' => [], 'footer_note' => null];
        }

        $tenant->loadMissing('billing');
        $billing = $tenant->billing;
        $estado = $tenant->estado ?? Tenant::ESTADO_ACTIVO;
        $panels = [];

        if ($estado === Tenant::ESTADO_SUSPENDIDO || $tenant->activo === false) {
            $panels[] = [
                'variant' => 'danger',
                'icon' => 'pause-circle',
                'title' => 'Cuenta suspendida',
                'body' => 'Podés revisar o mejorar tu plan en esta pantalla, pero tu cuenta seguirá suspendida hasta que se regularice el pago con GuardiAPP. Cambiar de plan no reactiva automáticamente una cuenta suspendida por mora.',
                'footnote' => 'Tus datos siguen guardados de forma segura.',
            ];

            return [
                'panels' => $panels,
                'footer_note' => 'La reactivación depende de la regularización del pago con GuardiAPP, no solo del cambio de plan.',
            ];
        }

        if ($estado === Tenant::ESTADO_VENCIDO) {
            if ($tenant->isInGracePeriod()) {
                $graceEnd = $tenant->gracePeriodEndsAt();
                $daysRemain = self::wholeDaysUntil($graceEnd);
                $hint = $daysRemain !== null
                    ? ($daysRemain === 0
                        ? 'Último día del período de regularización.'
                        : 'Aproximadamente '.$daysRemain.' día(s) para regularizar.')
                    : null;

                $panels[] = [
                    'variant' => 'warning',
                    'icon' => 'clock',
                    'title' => 'Plan vencido — período de regularización',
                    'body' => 'Tu plan está vencido; aún podés operar durante este período. Elegir otro plan actualiza límites y módulos, pero no sustituye un pago adeudado: si hay mora, coordiná la regularización con GuardiAPP.',
                    'footnote' => $hint,
                ];
            } else {
                $panels[] = [
                    'variant' => 'danger',
                    'icon' => 'exclamation-circle',
                    'title' => 'Regularización necesaria',
                    'body' => 'Tu acceso a la aplicación está restringido por mora. Podés ver los planes aquí; reactivar el servicio requiere confirmar el pago con GuardiAPP. Cambiar de plan solo actualiza el plan contratado y no reactiva la cuenta por sí solo.',
                    'footnote' => 'La información de tu compañía no se elimina por esta situación.',
                ];
            }

            return [
                'panels' => $panels,
                'footer_note' => $tenant->isInGracePeriod()
                    ? null
                    : 'Reactivar el acceso requiere confirmar el pago con GuardiAPP; el cambio de plan solo actualiza el contrato en la plataforma.',
            ];
        }

        if ($estado === Tenant::ESTADO_TRIAL || ($billing && $billing->estado_pago === 'trial')) {
            $trialEnd = $billing?->trial_ends_at ?? $tenant->fecha_vencimiento;
            $panels[] = [
                'variant' => 'info',
                'icon' => 'leaf',
                'title' => 'Estás en período de prueba',
                'body' => $trialEnd instanceof Carbon
                    ? 'Explorá las funciones con calma. El período de prueba está previsto hasta el '.$trialEnd->format('d/m/Y').', sin compromiso.'
                    : 'Explorá las funciones con calma durante tu período de prueba, sin compromiso.',
                'footnote' => null,
            ];

            return ['panels' => $panels, 'footer_note' => null];
        }

        return ['panels' => [], 'footer_note' => null];
    }

    /**
     * Pantalla 403 (acceso denegado por estado comercial).
     *
     * @param  'suspendido'|'vencido'|'cancelado'  $reasonKey
     */
    public static function forBlockedScreen(?Tenant $tenant, string $reasonKey): array
    {
        $support = 'soporte@guardianocturna.cl';

        if ($reasonKey === 'suspendido') {
            return [
                'title' => 'Cuenta suspendida',
                'lead' => 'En este momento no podemos ofrecerte acceso a la aplicación hasta regularizar la facturación.',
                'points' => [
                    'Tus datos siguen almacenados de forma segura.',
                    'Cambiar de plan en la pantalla de planes no reactiva automáticamente una cuenta suspendida por mora.',
                    'Para volver a operar, coordiná el pago o la regularización con GuardiAPP.',
                ],
                'support' => $support,
            ];
        }

        if ($reasonKey === 'vencido') {
            return [
                'title' => 'Acceso restringido',
                'lead' => 'El plan de tu compañía está vencido y ya no aplica el período de gracia para acceder a la app.',
                'points' => [
                    'Contactá a GuardiAPP para renovar o regularizar el pago y reactivar el acceso.',
                    'Tus datos no se eliminan por el vencimiento del plan.',
                ],
                'support' => $support,
            ];
        }

        return [
            'title' => 'Cuenta no disponible',
            'lead' => 'Esta cuenta no está disponible.',
            'points' => [
                'Si creés que es un error, escribinos.',
            ],
            'support' => $support,
        ];
    }

    private static function emptyLayout(): array
    {
        return [
            'show_banner' => false,
            'show_topbar_hint' => false,
            'topbar_line' => null,
        ];
    }

    private static function wholeDaysUntil(?Carbon $moment): ?int
    {
        if (! $moment instanceof Carbon || ! $moment->isFuture()) {
            return null;
        }

        return max(0, (int) now()->copy()->startOfDay()->diffInDays($moment->copy()->startOfDay()));
    }
}
