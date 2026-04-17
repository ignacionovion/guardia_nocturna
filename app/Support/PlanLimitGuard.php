<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\PlanAccessDeniedException;
use App\Models\Plan;
use App\Models\Tenant;

class PlanLimitGuard
{
    private const LIMIT_LABELS = [
        'users' => 'usuarios',
        'guardias' => 'guardias',
        'beds' => 'camas',
        'storage' => 'almacenamiento',
    ];

    public static function plan(): Plan
    {
        $tenant = tenant();

        if (!$tenant instanceof Tenant) {
            throw PlanAccessDeniedException::organizationNotResolved();
        }

        $tenant->loadMissing('planRelation');
        $plan = $tenant->planRelation;

        if (!$plan instanceof Plan) {
            throw PlanAccessDeniedException::noPlanAssigned();
        }

        return $plan;
    }

    public static function exceeds(string $limitType, int $currentUsage, int $incoming = 1): bool
    {
        $max = self::plan()->getLimit($limitType);

        if ($max === null) {
            return false;
        }

        return ($currentUsage + $incoming) > $max;
    }

    public static function enforceOrAbort(string $limitType, int $currentUsage, int $incoming = 1): void
    {
        $plan = self::plan();
        $max = $plan->getLimit($limitType);

        if ($max === null) {
            return;
        }

        if (($currentUsage + $incoming) <= $max) {
            return;
        }

        throw PlanAccessDeniedException::limitReached($limitType, $max, $plan->nombre);
    }
}
