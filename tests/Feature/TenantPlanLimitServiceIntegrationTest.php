<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\TenantPlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPlanLimitServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear plan de prueba
        $this->plan = Plan::create([
            'slug' => 'test-plan',
            'nombre' => 'Plan de Prueba',
            'max_users' => 5,
            'max_beds' => 10,
            'max_guardias' => 3,
            'max_storage_mb' => 100,
            'features' => json_encode(['test' => true]),
            'precio_mensual' => 99.99,
            'activo' => true,
            'orden' => 1,
        ]);

        // Crear tenant con plan
        $this->tenant = Tenant::create([
            'id' => 'test-tenant',
            'nombre' => 'Tenant de Prueba',
            'plan_id' => $this->plan->id,
            'activo' => true,
        ]);

        // Inicializar contexto tenant
        tenancy()->initialize($this->tenant);
    }

    public function test_resolucion_real_de_plan_y_limites(): void
    {
        $service = new TenantPlanLimitService();

        // Verificar que resuelve el plan correctamente
        $usage = $service->getCurrentUsage();

        $this->assertEquals('Plan de Prueba', $usage['plan_name']);
        $this->assertEquals(5, $usage['users']['limit']);
        $this->assertEquals(10, $usage['beds']['limit']);
        $this->assertEquals(3, $usage['guardias']['limit']);
        $this->assertEquals(100, $usage['storage_mb']['limit']);
    }

    public function test_can_create_user_con_limite_real(): void
    {
        $service = new TenantPlanLimitService();

        // Debería permitir crear usuarios (0 < 5)
        $this->assertTrue($service->canCreateUser());

        // Crear 5 usuarios para alcanzar el límite
        for ($i = 0; $i < 5; $i++) {
            \App\Models\User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'bombero',
            ]);
        }

        // Ahora debería bloquear (5 >= 5)
        $this->assertFalse($service->canCreateUser());
    }

    public function test_can_create_bed_con_limite_real(): void
    {
        $service = new TenantPlanLimitService();

        // Debería permitir crear camas (0 < 10)
        $this->assertTrue($service->canCreateBed());

        // Crear 10 camas para alcanzar el límite
        for ($i = 0; $i < 10; $i++) {
            \App\Models\Bed::create([
                'nombre' => "Bed {$i}",
                'status' => 'available',
            ]);
        }

        // Ahora debería bloquear (10 >= 10)
        $this->assertFalse($service->canCreateBed());
    }

    public function test_excepcion_si_tenant_sin_plan(): void
    {
        // Crear tenant sin plan
        $tenantSinPlan = Tenant::create([
            'id' => 'tenant-sin-plan',
            'nombre' => 'Tenant Sin Plan',
            'plan_id' => null,
            'activo' => true,
        ]);

        tenancy()->initialize($tenantSinPlan);

        $service = new TenantPlanLimitService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant sin plan asignado. Sistema inconsistente.');

        $service->getCurrentUsage();
    }

    public function test_limite_nulo_es_ilimitado(): void
    {
        // Crear plan con límites nulos (ilimitados)
        $planIlimitado = Plan::create([
            'slug' => 'plan-ilimitado',
            'nombre' => 'Plan Ilimitado',
            'max_users' => null,
            'max_beds' => null,
            'max_guardias' => null,
            'max_storage_mb' => null,
            'features' => json_encode(['unlimited' => true]),
            'precio_mensual' => 199.99,
            'activo' => true,
            'orden' => 2,
        ]);

        $tenantIlimitado = Tenant::create([
            'id' => 'tenant-ilimitado',
            'nombre' => 'Tenant Ilimitado',
            'plan_id' => $planIlimitado->id,
            'activo' => true,
        ]);

        tenancy()->initialize($tenantIlimitado);

        $service = new TenantPlanLimitService();

        $usage = $service->getCurrentUsage();

        $this->assertTrue($usage['users']['unlimited']);
        $this->assertTrue($usage['beds']['unlimited']);
        $this->assertTrue($usage['guardias']['unlimited']);
        $this->assertTrue($usage['storage_mb']['unlimited']);

        // Debería permitir crear siempre
        $this->assertTrue($service->canCreateUser());
        $this->assertTrue($service->canCreateBed());
        $this->assertTrue($service->canCreateGuardia());
    }

    protected function tearDown(): void
    {
        // Limpiar contexto tenant
        tenancy()->end();

        parent::tearDown();
    }
}
