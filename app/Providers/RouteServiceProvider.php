<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // FORZAR rutas centrales para dominios centrales - PRIORIDAD MÁXIMA
        foreach (config('tenancy.central_domains', []) as $domain) {
            Route::middleware('web')
                ->domain($domain)
                ->group(base_path('routes/central.php'));
        }
    }

    public function map(): void
    {
        // NO hacer nada aquí - las rutas se manejan en boot()
    }
}
