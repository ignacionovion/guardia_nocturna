<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use App\Services\TenantMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function __construct(
        protected TenantMetricsService $metrics,
    ) {}
    public function index()
    {
        $tenants = Tenant::with(['body', 'domains'])->latest()->paginate(20);
        return view('central.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        return view('central.tenants.form', ['tenant' => null, 'bodies' => $bodies]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:tenants,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan' => ['required', 'in:basico,profesional,enterprise'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'seed' => ['boolean'],
        ]);

        $tenant = null;
        $steps = [];

        try {
            // Step 1: Create tenant record in central DB
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'nombre' => $validated['nombre'],
                'numero' => $validated['numero'] ?? null,
                'body_id' => $validated['body_id'] ?? null,
                'plan' => $validated['plan'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            ]);
            $steps[] = '✓ Registro tenant creado';

            // Step 2: Create domain (subdomain = tenant id)
            $tenant->domains()->create(['domain' => $validated['id']]);
            $steps[] = '✓ Dominio creado: ' . $validated['id'];

            // Step 3: Database creation + grants happen via TenantCreated event
            // (CreateDatabase job runs automatically via TenancyServiceProvider)
            // We verify the database was created
            $dbName = $tenant->database()->getName();
            if (!$this->databaseExists($dbName)) {
                throw new \Exception("La base de datos {$dbName} no fue creada correctamente.");
            }
            $steps[] = "✓ Base de datos creada: {$dbName}";

            // Step 4: Verify migrations ran (check if migrations table exists and has records)
            $migrationsRan = $this->verifyMigrations($tenant);
            if (!$migrationsRan) {
                throw new \Exception("Las migraciones no se ejecutaron correctamente.");
            }
            $steps[] = '✓ Migraciones ejecutadas';

            // Step 5: Optional seeding
            if ($request->boolean('seed')) {
                $tenant->run(function () {
                    $seeder = new \Database\Seeders\DatabaseSeeder();
                    $seeder->run();
                });
                $steps[] = '✓ Datos iniciales poblados';
            }

            Log::info("Tenant created successfully", [
                'tenant_id' => $tenant->id,
                'database' => $dbName,
                'steps' => $steps,
            ]);

            CentralAuditLog::log('tenant_created', "Compañía «{$tenant->nombre}» creada", $tenant->id, [
                'plan' => $validated['plan'],
                'seed' => $request->boolean('seed'),
                'steps' => $steps,
            ]);

            return redirect('/admin/tenants')
                ->with('success', "Compañía «{$tenant->nombre}» creada correctamente.\n" . implode("\n", $steps));

        } catch (\Throwable $e) {
            Log::error("Tenant creation failed", [
                'tenant_id' => $validated['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'steps_completed' => $steps,
            ]);

            // Rollback: delete tenant if it was created (this will also delete DB via TenantDeleted event)
            if ($tenant && $tenant->exists) {
                try {
                    $tenant->delete();
                } catch (\Throwable $deleteError) {
                    Log::error("Failed to rollback tenant", ['error' => $deleteError->getMessage()]);
                }
            }

            return back()
                ->withInput()
                ->with('error', "Error al crear la compañía: {$e->getMessage()}")
                ->with('steps', $steps);
        }
    }

    /**
     * Check if a database exists
     */
    protected function databaseExists(string $name): bool
    {
        $result = DB::connection('central')->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
            [$name]
        );
        return count($result) > 0;
    }

    /**
     * Verify that migrations ran successfully for a tenant
     */
    protected function verifyMigrations(Tenant $tenant): bool
    {
        try {
            $count = $tenant->run(function () {
                return DB::table('migrations')->count();
            });
            return $count > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['body', 'domains']);
        $metrics = $this->metrics->forTenant($tenant);
        $health = $this->metrics->healthStatus($tenant);

        return view('central.tenants.show', compact('tenant', 'metrics', 'health'));
    }

    public function edit(Tenant $tenant)
    {
        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        return view('central.tenants.form', compact('tenant', 'bodies'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan' => ['required', 'in:basico,profesional,enterprise'],
            'estado' => ['required', 'in:trial,activo,suspendido,vencido,cancelado'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        // Sync activo boolean with estado for backward compatibility
        $validated['activo'] = in_array($validated['estado'], ['trial', 'activo']);
        $validated['grace_days'] = $validated['grace_days'] ?? 5;

        $oldEstado = $tenant->estado;
        $oldPlan = $tenant->plan;

        $tenant->update($validated);

        if ($oldPlan !== $validated['plan']) {
            CentralAuditLog::log('plan_changed', "Plan cambiado de {$oldPlan} a {$validated['plan']}", $tenant->id, [
                'old_plan' => $oldPlan, 'new_plan' => $validated['plan'],
            ]);
        }
        if ($oldEstado !== $validated['estado']) {
            CentralAuditLog::log('estado_changed', "Estado cambiado de {$oldEstado} a {$validated['estado']}", $tenant->id, [
                'old_estado' => $oldEstado, 'new_estado' => $validated['estado'],
            ]);
        }
        CentralAuditLog::log('tenant_updated', "Compañía «{$tenant->nombre}» actualizada", $tenant->id);

        return redirect('/admin/tenants')
            ->with('success', "Compañía «{$tenant->nombre}» actualizada.");
    }

    public function destroy(Tenant $tenant)
    {
        $nombre = $tenant->nombre;
        $tenantId = $tenant->id;
        $plan = $tenant->plan;
        $tenant->delete();

        CentralAuditLog::log('tenant_deleted', "Compañía «{$nombre}» eliminada", $tenantId, ['plan' => $plan]);

        return redirect('/admin/tenants')
            ->with('success', "Compañía «{$nombre}» eliminada junto con su base de datos.");
    }

    public function updateFeatures(Request $request, Tenant $tenant)
    {
        $features = $request->input('features', []);
        $resolved = [];

        foreach (\App\Services\FeatureFlagService::availableFeatures() as $feature) {
            if ($feature === 'max_users') {
                $value = $request->input("features.{$feature}");
                if ($value !== null && $value !== '') {
                    $resolved[$feature] = (int) $value;
                }
            } else {
                $resolved[$feature] = isset($features[$feature]) && $features[$feature] === '1';
            }
        }

        $tenant->features = $resolved;
        $tenant->save();

        CentralAuditLog::log('features_updated', "Feature flags actualizados para «{$tenant->nombre}»", $tenant->id, [
            'features' => $resolved,
        ]);

        return redirect("/admin/tenants/{$tenant->id}")
            ->with('success', 'Feature flags actualizados.');
    }

    public function runMigrations(Tenant $tenant)
    {
        try {
            $tenant->run(function () {
                Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations/tenant']);
            });

            CentralAuditLog::log('migrations_run', "Migraciones ejecutadas para «{$tenant->nombre}»", $tenant->id);

            return redirect("/admin/tenants/{$tenant->id}")
                ->with('success', 'Migraciones ejecutadas correctamente.');
        } catch (\Throwable $e) {
            return redirect("/admin/tenants/{$tenant->id}")
                ->with('error', 'Error al ejecutar migraciones: ' . $e->getMessage());
        }
    }

    public function runSeed(Tenant $tenant)
    {
        try {
            $tenant->run(function () {
                Artisan::call('db:seed', ['--force' => true]);
            });

            CentralAuditLog::log('seed_run', "Seeders ejecutados para «{$tenant->nombre}»", $tenant->id);

            return redirect("/admin/tenants/{$tenant->id}")
                ->with('success', 'Seeders ejecutados correctamente.');
        } catch (\Throwable $e) {
            return redirect("/admin/tenants/{$tenant->id}")
                ->with('error', 'Error al ejecutar seeders: ' . $e->getMessage());
        }
    }
}
