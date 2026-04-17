<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Registrar rutas tenant DESPUÉS de las centrales para que no tengan prioridad
            Route::group([], base_path('routes/tenant.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule) {
        /*
         * Comandos operativos: SIEMPRE vía tenant:run para ejecutar dentro del contexto
         * de cada tenant (DB correcta). No duplicar en routes/console.php Schedule::command(...)
         * sin tenant:run — corre en conexión por defecto y es incorrecto para datos tenant.
         *
         * Además, routes/console.php registra Schedule::call(runGuardiaScheduler) cada minuto
         * (transición semanal de guardia). No es duplicado de tenant:run: es otro closure programado
         * en el mismo schedule:run; ambos dependen de que el cron ejecute `php artisan schedule:run`.
         */
        $schedule->command('tenant:run', ['guardia:expire-replacements'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:run-calendar'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:reset-beds'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:generate-notifications'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:daily-cleanup'])->everyMinute();

        // SaaS: backups de BD tenant (comando único; itera tenants activos)
        $schedule->command('tenant:backup')->dailyAt('03:00');

        /*
         * Facturación: billing:check-expiration (tenant_billing) sincroniza tenants vía Billing::syncToTenant().
         * tenant:check-expiry usa tenants.fecha_vencimiento / grace para avisos y transiciones;
         * ejecutar ambos diariamente; la fecha de acceso debe quedar alineada tras billing (sync).
         */
        $schedule->command('tenant:check-expiry')->dailyAt('06:00');
        $schedule->command('billing:check-expiration')->dailyAt('06:15');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );
        
        // CRITICAL: Tenancy middlewares MUST run BEFORE auth/guest
        $middleware->priority([
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \App\Http\Middleware\EnsureTenantActive::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\Authenticate::class,
            \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
        
        // Eliminados middlewares tenant del grupo web global para no afectar dominios centrales
        // Estos middlewares se aplican ahora solo en rutas tenant específicas
        $middleware->alias([
            'auth'                => \App\Http\Middleware\Authenticate::class,
            'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'tenant'              => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            'plan'                => \App\Http\Middleware\CheckPlanAccess::class,
            'activo'              => \App\Http\Middleware\CheckTenantActive::class,
            'super_admin'         => \App\Http\Middleware\EnsureSuperAdmin::class,
            'ensure_captain'      => \App\Http\Middleware\EnsureCaptain::class,
            'ensure_guardia'      => \App\Http\Middleware\EnsureGuardia::class,
            'ensure_active_guardia' => \App\Http\Middleware\EnsureActiveGuardia::class,
            'emergency_access'    => \App\Http\Middleware\EnsureEmergencyAccess::class,
            'inventory_access'    => \App\Http\Middleware\EnsureInventoryAccess::class,
            'inventario_only'     => \App\Http\Middleware\EnsureInventoryOnly::class,
            'preventivas_admin'   => \App\Http\Middleware\EnsurePreventivasAdmin::class,
            'guardia_on_duty'     => \App\Http\Middleware\EnsureGuardiaOnDuty::class,
            'role_permission'     => \App\Http\Middleware\CheckRolePermission::class,
            'feature'             => \App\Http\Middleware\EnforceFeatureFlag::class,
            'tenant.feature'      => \App\Http\Middleware\EnsureTenantFeatureEnabled::class,
            'tenant.has_plan'         => \App\Http\Middleware\EnsureTenantHasPlan::class,
            'tenant.has_plan_app'     => \App\Http\Middleware\EnsureTenantHasPlanForApp::class,
            'max_users'           => \App\Http\Middleware\EnforceMaxUsers::class,
            'plan.limit'          => \App\Http\Middleware\EnforcePlanLimits::class,
            'role'                => \App\Http\Middleware\EnsureRole::class,
            'password.not_temporary' => \App\Http\Middleware\EnsurePasswordIsNotTemporary::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
