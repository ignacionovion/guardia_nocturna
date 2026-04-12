<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Body;
use App\Models\CentralAuditLog;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\PlanService;
use App\Services\TenantCaptainProvisioningService;
use App\Services\TenantMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function __construct(
        protected TenantMetricsService $metrics,
        protected TenantCaptainProvisioningService $captainProvisioning,
    ) {}
    public function index()
    {
        $tenants = Tenant::with(['body', 'domains', 'planRelation'])->latest()->paginate(20);
        return view('central.tenants.index', compact('tenants'));
    }

    private function resolvePlanForTenantCreation(?int $planId): Plan
    {
        if ($planId !== null) {
            $plan = Plan::find($planId);
            if ($plan) {
                return $plan;
            }
        }

        $plan = Plan::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$plan) {
            throw new \RuntimeException('No existe ningún plan activo en la base de datos central. No es posible crear tenants.');
        }

        return $plan;
    }

    public function create()
    {
        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        $plans = Plan::active()->ordered()->get();
        return view('central.tenants.form', ['tenant' => null, 'bodies' => $bodies, 'plans' => $plans]);
    }

    public function store(Request $request)
    {
        // Debug: Log incoming request data
        Log::debug('Tenant creation attempt', [
            'input_id' => $request->input('id'),
            'input_nombre' => $request->input('nombre'),
            'all_input' => $request->all(),
        ]);

        // Check if tenant exists before validation
        $inputId = $request->input('id');
        if ($inputId) {
            $existingTenant = Tenant::where('id', $inputId)->first();
            Log::debug('Pre-validation tenant check', [
                'input_id' => $inputId,
                'exists' => $existingTenant ? true : false,
                'existing_tenant' => $existingTenant?->toArray(),
            ]);
        }

        $validated = $request->validate([
            'id' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                'unique:tenants,id'
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'tiene_trial' => ['nullable', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'seed' => ['boolean'],
        ], [
            'id.required' => 'El identificador es obligatorio.',
            'id.regex' => 'El identificador solo puede contener letras minúsculas, números y guiones.',
            'id.unique' => 'Este identificador ya está en uso (validación Laravel).',
            'nombre.required' => 'El nombre es obligatorio.',
            'plan_id.exists' => 'El plan seleccionado no es válido.',
            'billing_cycle.required' => 'El ciclo de facturación es obligatorio.',
            'billing_cycle.in' => 'El ciclo de facturación debe ser mensual o anual.',
        ]);

        Log::debug('Validation passed', ['validated_id' => $validated['id']]);

        $tenant = null;
        $steps = [];

        try {
            // Step 1: Create tenant record in central DB
            $plan = $this->resolvePlanForTenantCreation(
                isset($validated['plan_id']) ? (int) $validated['plan_id'] : null
            );
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'nombre' => $validated['nombre'],
                'numero' => $validated['numero'] ?? null,
                'body_id' => $validated['body_id'] ?? null,
                'plan_id' => $plan?->id,
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            ]);
            $steps[] = '✓ Registro tenant creado';

            // Step 1b: Create billing record automatically
            try {
                $billingCycle = $validated['billing_cycle'] ?? 'monthly';
                $tieneTrial = $request->boolean('tiene_trial');
                $trialDays = $validated['trial_days'] ?? 30;
                
                // Calcular monto según ciclo
                $planPrice = $billingCycle === 'yearly' 
                    ? ($plan?->precio_anual ?? $plan?->precio_mensual * 12 ?? 0)
                    : ($plan?->precio_mensual ?? 0);
                
                Log::debug('Creating billing record', [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan?->id,
                    'plan_slug' => $plan?->slug,
                    'billing_cycle' => $billingCycle,
                    'monto' => $planPrice,
                    'tiene_trial' => $tieneTrial,
                    'trial_days' => $trialDays,
                ]);
                
                // Determinar fechas según trial
                if ($tieneTrial) {
                    $trialEndsAt = now()->addDays($trialDays);
                    $fechaVencimiento = null; // Se calculará al terminar el trial
                    $estadoPago = 'trial';
                    $observacion = "Período de prueba de {$trialDays} días";
                } else {
                    $diasVencimiento = $billingCycle === 'yearly' ? 365 : 30;
                    $trialEndsAt = null;
                    $fechaVencimiento = now()->addDays($diasVencimiento);
                    $estadoPago = 'pendiente';
                    $observacion = null;
                }
                
                $billing = Billing::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan?->id,
                    'plan' => $plan?->slug,
                    'billing_cycle' => $billingCycle,
                    'monto' => $planPrice,
                    'estado_pago' => $estadoPago,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'trial_ends_at' => $trialEndsAt,
                    'fecha_ultimo_pago' => null,
                    'observacion' => $observacion,
                ]);
                
                Log::debug('Billing record created successfully', [
                    'billing_id' => $billing->id,
                    'tenant_id' => $tenant->id,
                ]);
                
                $cicloLabel = $billingCycle === 'yearly' ? 'Anual' : 'Mensual';
                $trialLabel = $tieneTrial ? " (Trial {$trialDays} días)" : '';
                $steps[] = "✓ Facturación creada: {$cicloLabel}{$trialLabel} - \${$planPrice}";
            } catch (\Throwable $e) {
                Log::error('Billing record creation failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $steps[] = '✗ ERROR Facturación: ' . $e->getMessage();
            }

            // Step 2: Create domain (subdomain = tenant id)
            $tenant->domains()->create(['domain' => $validated['id'] . '.dev-app.cl']);
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

            // Step 5: Optional seeding (sin credenciales productivas; ver TenantDatabaseSeeder)
            if ($request->boolean('seed')) {
                $tenant->run(function () {
                    Artisan::call('db:seed', [
                        '--class' => \Database\Seeders\TenantDatabaseSeeder::class,
                        '--force' => true,
                    ]);
                });
                $steps[] = '✓ Datos iniciales poblados';
            }

            // Step 6: Usuario inicial capitan (no sobrescribe si ya existe)
            $provision = $this->captainProvisioning->provisionInitialAccess($tenant);
            if ($provision['skipped']) {
                $steps[] = '○ Usuario capitan ya existía; no se generó contraseña nueva';
                CentralAuditLog::log(
                    'tenant_captain_initial_access',
                    "Provisionamiento acceso capitán omitido (usuario ya existía) — «{$tenant->nombre}»",
                    $tenant->id,
                    ['skipped' => true]
                );
            } else {
                $this->mergeCaptainAccessLastResetAt($tenant);
                $steps[] = '✓ Usuario inicial capitan creado';
                CentralAuditLog::log(
                    'tenant_captain_initial_access',
                    "Usuario inicial capitan provisionado — «{$tenant->nombre}»",
                    $tenant->id,
                    ['skipped' => false]
                );
            }

            Log::info("Tenant created successfully", [
                'tenant_id' => $tenant->id,
                'database' => $dbName,
                'steps' => $steps,
            ]);

            CentralAuditLog::log('tenant_created', "Compañía «{$tenant->nombre}» creada", $tenant->id, [
                'plan_id' => $plan?->id,
                'plan_slug' => $plan?->slug,
                'seed' => $request->boolean('seed'),
                'steps' => $steps,
            ]);

            $redirect = redirect()
                ->route('central.tenants.show', $tenant->id)
                ->with('success', "Compañía «{$tenant->nombre}» creada correctamente.\n" . implode("\n", $steps));

            if (!$provision['skipped'] && $provision['plain_password'] !== null) {
                $redirect->with('captain_access_credentials', [
                    'username' => $provision['username'],
                    'password' => $provision['plain_password'],
                ]);
            }

            return $redirect;

        } catch (\Illuminate\Database\QueryException $e) {
            // Log detailed SQL error info
          Log::error('Tenant creation SQL error', [
    'tenant_id' => $validated['id'] ?? $request->input('id'),
    'sql_error_code' => $e->getCode(),
    'sql_error_message' => $e->getMessage(),
    'sql_state' => $e->errorInfo[0] ?? null,
    'sql_driver_code' => $e->errorInfo[1] ?? null,
    'sql_driver_message' => $e->errorInfo[2] ?? null,
]);

            // Check if it's a duplicate key error
            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, 'Duplicate entry') || str_contains($errorMsg, '1062')) {
                // Try to identify which table/column caused the duplicate
                if (str_contains($errorMsg, 'domains')) {
                    return back()
                        ->withInput()
                        ->with('error', "El dominio '{$validated['id']}' ya está registrado. Este error puede ocurrir si se eliminó una compañía anterior pero quedó residuo del dominio. Contacte al administrador del sistema.")
                        ->with('steps', $steps);
                }
                if (str_contains($errorMsg, 'tenants')) {
                    return back()
                        ->withInput()
                        ->with('error', "El identificador '{$validated['id']}' ya existe en la base de datos (error SQL). Intente con otro nombre.")
                        ->with('steps', $steps);
                }
            }

            return back()
                ->withInput()
                ->with('error', "Error de base de datos al crear la compañía: {$e->getMessage()}")
                ->with('steps', $steps);

        } catch (\Throwable $e) {
            Log::error("Tenant creation failed", [
                'tenant_id' => $validated['id'] ?? $request->input('id'),
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

    /**
     * Persist only non-sensitive metadata on the central tenant record.
     */
    private function mergeCaptainAccessLastResetAt(Tenant $tenant): void
    {
        $tenant->refresh();
        $data = $tenant->data ?? [];
        if (!is_array($data)) {
            $data = [];
        }
        $data['captain_access'] = array_merge($data['captain_access'] ?? [], [
            'last_reset_at' => now()->toIso8601String(),
        ]);
        $tenant->data = $data;
        $tenant->save();
    }

    public function show(string $tenant)
    {
        $tenant = Tenant::findOrFail($tenant);
        $tenant->load(['body', 'domains', 'planRelation']);
        
        $metrics = $this->metrics->forTenant($tenant);
        $health = $this->metrics->healthStatus($tenant);
        
        // Get plan usage info using the tenant-specific method
        $usageInfo = PlanService::getUsageInfoForTenant($tenant);
        $tenantUsers = [];
        try {
            $tenant->run(function () use (&$tenantUsers) {
                $tenantUsers = \App\Models\User::select('id', 'name', 'email', 'username', 'role')
                    ->orderByRaw("FIELD(role, 'super_admin', 'capitania', 'administrador', 'guardia')")
                    ->orderBy('name')
                    ->limit(20)
                    ->get()
                    ->toArray();
            });
        } catch (\Throwable $e) {
            // Tenant DB may not exist yet
        }

        $captainAccess = [
            'username' => 'capitan',
            'last_reset_at' => data_get($tenant->data, 'captain_access.last_reset_at'),
            'password_must_change' => null,
            'captain_exists' => false,
        ];
        try {
            $tenant->run(function () use (&$captainAccess) {
                $u = \App\Models\User::query()->where('username', 'capitan')->first();
                $captainAccess['captain_exists'] = $u !== null;
                $captainAccess['password_must_change'] = $u?->password_must_change;
            });
        } catch (\Throwable $e) {
            // Tenant DB may not exist yet
        }
        
        // Get available plans for change plan dropdown
        $availablePlans = Plan::active()->ordered()->get();

        return view('central.tenants.show', compact('tenant', 'metrics', 'health', 'tenantUsers', 'usageInfo', 'availablePlans', 'captainAccess'));
    }

    public function resetCaptainPassword($tenant)
    {
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        try {
            $result = $this->captainProvisioning->resetCaptainPassword($tenant);
            $this->mergeCaptainAccessLastResetAt($tenant);

            CentralAuditLog::log(
                'tenant_captain_password_reset',
                "Contraseña del usuario capitan restablecida — «{$tenant->nombre}»",
                $tenant->id,
                ['tenant_id' => $tenant->id]
            );

            return redirect()
                ->route('central.tenants.show', $tenant->id)
                ->with('success', 'Contraseña del usuario capitan regenerada. Guárdala de forma segura; solo se muestra una vez.')
                ->with('captain_access_credentials', [
                    'username' => $result['username'],
                    'password' => $result['plain_password'],
                ]);
        } catch (\Throwable $e) {
            Log::error('resetCaptainPassword failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('central.tenants.show', $tenant->id)
                ->with('error', 'No se pudo restablecer la contraseña: ' . $e->getMessage());
        }
    }

    public function edit($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        $plans = Plan::active()->ordered()->get();
        return view('central.tenants.form', compact('tenant', 'bodies', 'plans'));
    }

    public function update(Request $request, $tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'estado' => ['required', 'in:trial,activo,suspendido,vencido,cancelado'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        // Sync activo boolean with estado for backward compatibility
        $validated['activo'] = in_array($validated['estado'], ['trial', 'activo']);
        $validated['grace_days'] = $validated['grace_days'] ?? 5;

        $oldEstado = $tenant->estado;
        $oldPlanId = $tenant->plan_id;
        $oldPlanSlug = $tenant->planRelation?->slug;

        $newPlan = Plan::findOrFail((int) $validated['plan_id']);

        $tenant->update($validated);

        if ((int) $oldPlanId !== (int) $validated['plan_id']) {
            CentralAuditLog::log('plan_changed', "Plan cambiado de {$oldPlanSlug} a {$newPlan->slug}", $tenant->id, [
                'old_plan_id' => $oldPlanId,
                'old_plan_slug' => $oldPlanSlug,
                'new_plan_id' => $newPlan->id,
                'new_plan_slug' => $newPlan->slug,
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

    public function destroy($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $nombre = $tenant->nombre;
        $tenantId = $tenant->id;
        $plan = $tenant->planRelation?->slug;
        $tenant->delete();

        CentralAuditLog::log('tenant_deleted', "Compañía «{$nombre}» eliminada", $tenantId, ['plan' => $plan]);

        return redirect('/admin/tenants')
            ->with('success', "Compañía «{$nombre}» eliminada junto con su base de datos.");
    }

    public function updateFeatures(Request $request, $tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

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

    public function runMigrations($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

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

    public function runSeed($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        try {
            $tenant->run(function () {
                Artisan::call('db:seed', [
                    '--class' => \Database\Seeders\TenantDatabaseSeeder::class,
                    '--force' => true,
                ]);
            });

            CentralAuditLog::log('seed_run', "Seeders ejecutados para «{$tenant->nombre}»", $tenant->id);

            return redirect("/admin/tenants/{$tenant->id}")
                ->with('success', 'Seeders ejecutados correctamente.');
        } catch (\Throwable $e) {
            return redirect("/admin/tenants/{$tenant->id}")
                ->with('error', 'Error al ejecutar seeders: ' . $e->getMessage());
        }
    }

    public function timeline($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $events = CentralAuditLog::forTenant($tenant->id)
            ->latest()
            ->paginate(30);

        return view('central.tenants.timeline', compact('tenant', 'events'));
    }

    public function admin($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        return view('central.tenants.admin', compact('tenant'));
    }

    /**
     * Check if a slug is available via AJAX.
     */
    public function checkSlugAvailability(Request $request)
    {
        $slug = $request->input('slug');

        if (!$slug) {
            return response()->json(['available' => false, 'message' => 'Slug vacío']);
        }

        // Validate format
        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return response()->json([
                'available' => false,
                'message' => 'Solo minúsculas, números y guiones'
            ]);
        }

        // Check if exists
        $exists = Tenant::where('id', $slug)->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => '✖ Ya existe'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => '✔ Disponible'
        ]);
    }

    /**
     * Change the plan of a tenant.
     */
    public function changePlan(Request $request, $tenant)
    {
        // Handle route model binding manualmente for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $oldPlan = $tenant->planRelation?->slug;

        $tenant->update([
            'plan_id' => $plan->id,
        ]);

        CentralAuditLog::log('plan_changed', "Plan cambiado de {$oldPlan} a {$plan->slug}", $tenant->id, [
            'old_plan_slug' => $oldPlan,
            'new_plan_slug' => $plan->slug,
            'new_plan_id' => $plan->id,
        ]);

        return redirect("/admin/tenants/{$tenant->id}")
            ->with('success', "Plan actualizado a «{$plan->nombre}»");
    }
}
