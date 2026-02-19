<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $scheduleTz = env('GUARDIA_SCHEDULE_TZ', config('app.timezone'));
        $schedule->command('guardia:expire-replacements')->everyMinute();
        $schedule->command('guardia:run-calendar')->everyMinute();
        $schedule->command('guardia:reset-beds')->everyMinute();
        $schedule->command('guardia:generate-notifications')->everyMinute();
        $schedule->command('guardia:daily-cleanup')->dailyAt('07:00')->timezone($scheduleTz);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\ExpireReplacements::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureInventoryOnly::class);
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'emergency_access' => \App\Http\Middleware\EnsureEmergencyAccess::class,
            'inventory_access' => \App\Http\Middleware\EnsureInventoryAccess::class,
            'inventario_only' => \App\Http\Middleware\EnsureInventoryOnly::class,
            'preventivas_admin' => \App\Http\Middleware\EnsurePreventivasAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
