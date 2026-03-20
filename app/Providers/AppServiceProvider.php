<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override the default URL generator with our tenant-aware version
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            $url = new TenantAwareUrlGenerator(
                $routes,
                $app->make('request'),
                $app['config']['app.asset_url']
            );

            $url->setSessionResolver(function () use ($app) {
                return $app['session'] ?? null;
            });

            $url->setKeyResolver(function () use ($app) {
                return $app->make('config')->get('app.key');
            });

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observer para liberación automática de camas
        \App\Models\Bombero::observe(\App\Observers\BomberoObserver::class);
    }
}
