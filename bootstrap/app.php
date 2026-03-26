<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
        // Cada comando operativo corre para todos los tenants activos
        $schedule->command('tenant:run', ['guardia:expire-replacements'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:run-calendar'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:reset-beds'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:generate-notifications'])->everyMinute();
        $schedule->command('tenant:run', ['guardia:daily-cleanup'])->everyMinute();

        // SaaS maintenance
        $schedule->command('tenant:backup')->dailyAt('03:00');
        $schedule->command('tenant:check-expiry')->dailyAt('06:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        // Eliminados middlewares tenant del grupo web global para no afectar dominios centrales
        // Estos middlewares se aplican ahora solo en rutas tenant específicas
        $middleware->alias([
            'auth'                => \App\Http\Middleware\Authenticate::class,
            'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class,
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
            'max_users'           => \App\Http\Middleware\EnforceMaxUsers::class,
            'plan.limit'          => \App\Http\Middleware\EnforcePlanLimits::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
