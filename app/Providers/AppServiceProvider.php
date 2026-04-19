<?php

namespace App\Providers;

use App\Support\TenantSubscriptionUx;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );

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
        $forceHttps = $this->app->environment('production')
            || filter_var(env('APP_FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)
            || request()->headers->get('x-forwarded-proto') === 'https';

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        // Registrar observer para liberación automática de camas
        \App\Models\Bombero::observe(\App\Observers\BomberoObserver::class);
        
        // Registrar observer para sincronización automática de estado de camas
        \App\Models\BedAssignment::observe(\App\Observers\BedAssignmentObserver::class);

        View::composer('layouts.modern', function ($view): void {
            $view->with('subscriptionUx', TenantSubscriptionUx::forLayout(tenant()));
        });
    }
}
