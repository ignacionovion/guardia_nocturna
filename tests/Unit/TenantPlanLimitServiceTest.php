<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TenantPlanLimitService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
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

    public function test_missing_plan_throws_exception(): void
    {
        $container = new Container();
        $container->instance('log', new class {
            public function error(...$args): void {}
            public function warning(...$args): void {}
            public function info(...$args): void {}
        });
        Facade::setFacadeApplication($container);

        $service = new TenantPlanLimitService();

        $planFetched = new \ReflectionProperty($service, 'planFetched');
        $planFetched->setAccessible(true);
        $planFetched->setValue($service, true);

        $plan = new \ReflectionProperty($service, 'plan');
        $plan->setAccessible(true);
        $plan->setValue($service, null);

        $planSource = new \ReflectionProperty($service, 'planSource');
        $planSource->setAccessible(true);
        $planSource->setValue($service, 'test');

        $getLimit = new \ReflectionMethod($service, 'getLimit');
        $getLimit->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant sin plan asignado. Sistema inconsistente.');

        $getLimit->invoke($service, 'max_users');
    }
}
