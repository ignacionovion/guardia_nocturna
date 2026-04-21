<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TenantPlanLimitService;
use PHPUnit\Framework\TestCase;

class TenantPlanLimitServiceTest extends TestCase
{
    public function test_unlimited_limit_allows_creation(): void
    {
        $service = new TenantPlanLimitService();

        $method = new \ReflectionMethod($service, 'isWithinLimit');
        $method->setAccessible(true);

        $allowed = $method->invoke($service, 999, null);

        $this->assertTrue($allowed);
    }

    public function test_numeric_limit_blocks_when_reached(): void
    {
        $service = new TenantPlanLimitService();

        $method = new \ReflectionMethod($service, 'isWithinLimit');
        $method->setAccessible(true);

        $blockedAtLimit = $method->invoke($service, 10, 10);
        $blockedWithZero = $method->invoke($service, 0, 0);

        $this->assertFalse($blockedAtLimit);
        $this->assertFalse($blockedWithZero);
    }
}
