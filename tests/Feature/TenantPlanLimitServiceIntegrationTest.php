<?php
declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\TenantDatabaseSeeder;
use App\Models\Bed;
use App\Models\Bombero;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantPlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantPlanLimitServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = Plan::create([
            'slug' => 'test-plan-' . Str::uuid(),
            'nombre' => 'Plan de Prueba',
            'max_users' => 5,
            'max_volunteers' => 4,
            'max_beds' => 10,
            'max_guardias' => 3,
            'max_storage_mb' => 100,
            'features' => json_encode(['test' => true]),
            'precio_mensual' => 99.99,
            'activo' => true,
            'orden' => 1,
        ]);

        $this->tenant = Tenant::create([
            'id' => 'tenant-' . Str::uuid(),
            'nombre' => 'Tenant de Prueba',
            'plan_id' => $this->plan->id,
            'activo' => true,
        ]);

        tenancy()->initialize($this->tenant);
    }

    public function test_resolucion_real_de_plan_y_limites(): void
    {
        $service = new TenantPlanLimitService();

        $usage = $service->getCurrentUsage();

        $this->assertEquals('Plan de Prueba', $usage['plan_name']);
        $this->assertEquals(5, $usage['users']['limit']);
        $this->assertEquals(4, $usage['volunteers']['limit']);
        $this->assertEquals(10, $usage['beds']['limit']);
        $this->assertEquals(3, $usage['guardias']['limit']);
        $this->assertEquals(100, $usage['storage_mb']['limit']);
    }

    public function test_can_create_user_con_limite_real(): void
    {
        $service = new TenantPlanLimitService();

        $this->assertTrue($service->canCreateUser());

        for ($i = 0; $i < 5; $i++) {
User::create([
    'name' => "User {$i}",
    'username' => 'user.' . $i . '.' . Str::lower((string) Str::uuid()),
    'email' => 'user-' . $i . '-' . Str::uuid() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'bombero',
]);
        }

        $this->assertFalse($service->canCreateUser());
    }

    public function test_can_create_bed_con_limite_real(): void
    {
        $service = new TenantPlanLimitService();

        $this->assertTrue($service->canCreateBed());

        for ($i = 0; $i < 10; $i++) {
            Bed::create([
                'name' => 'Bed ' . $i . ' ' . Str::uuid(),
                'status' => 'available',
            ]);
        }

        $this->assertFalse($service->canCreateBed());
    }

    public function test_can_create_volunteer_con_limite_real(): void
    {
        $service = new TenantPlanLimitService();

        $this->assertTrue($service->canCreateVolunteer());

        for ($i = 0; $i < 4; $i++) {
            Bombero::create([
                'nombres' => "Vol {$i}",
                'apellido_paterno' => 'Test',
                'estado_asistencia' => 'constituye',
                'es_titular' => true,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }

        $this->assertFalse($service->canCreateVolunteer());
    }

    public function test_tenant_seed_no_crea_camas_por_defecto(): void
    {
        $seedExitCode = Artisan::call('db:seed', [
            '--class' => TenantDatabaseSeeder::class,
            '--force' => true,
        ]);

        $this->assertSame(0, $seedExitCode);
        $this->assertSame(0, Bed::count());
    }

    public function test_tenant_sin_plan_no_rompe_resumen_de_uso(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant-with-plan-' . Str::uuid(),
            'nombre' => 'Tenant Con Plan Temporal',
            'plan_id' => $this->plan->id,
            'activo' => true,
        ]);

        DB::connection('central')
            ->table('tenants')
            ->where('id', $tenant->id)
            ->update(['plan_id' => null]);

        $tenant = Tenant::query()->findOrFail($tenant->id);

        tenancy()->end();
        tenancy()->initialize($tenant);

        $service = new TenantPlanLimitService();

        $usage = $service->getCurrentUsage();

        $this->assertSame('Sin plan', $usage['plan_name']);
        $this->assertNull($usage['users']['limit']);
        $this->assertTrue($usage['users']['unlimited']);
    }

    public function test_limite_nulo_es_ilimitado(): void
    {
        $planIlimitado = Plan::create([
            'slug' => 'plan-ilimitado-' . Str::uuid(),
            'nombre' => 'Plan Ilimitado',
            'max_users' => null,
            'max_volunteers' => null,
            'max_beds' => null,
            'max_guardias' => null,
            'max_storage_mb' => null,
            'features' => json_encode(['unlimited' => true]),
            'precio_mensual' => 199.99,
            'activo' => true,
            'orden' => 2,
        ]);

        $tenantIlimitado = Tenant::create([
            'id' => 'tenant-ilimitado-' . Str::uuid(),
            'nombre' => 'Tenant Ilimitado',
            'plan_id' => $planIlimitado->id,
            'activo' => true,
        ]);

        tenancy()->end();
        tenancy()->initialize($tenantIlimitado);

        $service = new TenantPlanLimitService();

        $usage = $service->getCurrentUsage();

        $this->assertTrue($usage['users']['unlimited']);
        $this->assertTrue($usage['volunteers']['unlimited']);
        $this->assertTrue($usage['beds']['unlimited']);
        $this->assertTrue($usage['guardias']['unlimited']);
        $this->assertTrue($usage['storage_mb']['unlimited']);

        $this->assertTrue($service->canCreateUser());
        $this->assertTrue($service->canCreateVolunteer());
        $this->assertTrue($service->canCreateBed());
        $this->assertTrue($service->canCreateGuardia());
    }

    protected function tearDown(): void
    {
        tenancy()->end();

        parent::tearDown();
    }
}
