<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

/**
 * Extended UrlGenerator that automatically adds 'tenant' and 'domain' parameters
 * when generating URLs in the tenant context.
 * 
 * This solves the "Missing required parameter" errors when using route() helper
 * in tenant views, since all tenant routes are scoped under {tenant}.{domain} pattern.
 * 
 * - Tenant parameter: injected from tenant() context
 * - Domain parameter: extracted from current request host or config fallback
 */
class TenantAwareUrlGenerator extends UrlGenerator
{
    /**
     * Get the URL for a given route instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    public function toRoute($route, $parameters, $absolute = true)
    {
        $routeName = $route->getName();
        $isCentralRoute = $routeName && str_starts_with($routeName, 'central.');

        // CRITICAL FIX: Handle ANY tenant route called from central context
        if (!tenant() && !$isCentralRoute) {
            // Check if this route requires tenant parameter
            if ($this->routeNeedsTenantParameter($route)) {
                // Let the parent handle the route - it will show the actual error
                // instead of returning a broken javascript:void(0); link
            }
        }

        // If the route requires a 'tenant' parameter (and is not a central route)
        if (!$isCentralRoute && $this->routeNeedsTenantParameter($route)) {
            $parameters = $this->injectTenantParameter($parameters);
        }

        // If the route requires a 'domain' parameter
        if ($this->routeNeedsDomainParameter($route)) {
            $parameters = $this->injectDomainParameter($parameters);
        }

        return parent::toRoute($route, $parameters, $absolute);
    }

    /**
     * Check if the route requires a 'tenant' parameter.
     */
    protected function routeNeedsTenantParameter($route): bool
    {
        $parameterNames = $route->parameterNames();
        
        // Check if 'tenant' is a required parameter
        return in_array('tenant', $parameterNames);
    }

    /**
     * Inject the current tenant ID into the parameters.
     */
    protected function injectTenantParameter($parameters): array
    {
        // Ensure parameters is an array
        if (!is_array($parameters)) {
            $parameters = $parameters ? [$parameters] : [];
        }

        // If tenant parameter is already provided, don't override it
        if (isset($parameters['tenant'])) {
            return $parameters;
        }

        // If we're in tenant context, use current tenant
        if (tenant()) {
            $parameters['tenant'] = tenant('id');
            return $parameters;
        }

        // Not in tenant context and no tenant parameter provided
        // This will cause the parent::toRoute to throw an exception
        // with a clear message about missing parameter
        return $parameters;
    }

    /**
     * Check if the route requires a 'domain' parameter.
     */
    protected function routeNeedsDomainParameter($route): bool
    {
        $parameterNames = $route->parameterNames();
        
        // Check if 'domain' is a required parameter
        return in_array('domain', $parameterNames);
    }

    /**
     * Inject the current domain into the parameters.
     */
    protected function injectDomainParameter($parameters): array
    {
        // Ensure parameters is an array
        if (!is_array($parameters)) {
            $parameters = $parameters ? [$parameters] : [];
        }

        // If domain parameter is already provided, don't override it
        if (isset($parameters['domain'])) {
            return $parameters;
        }

        // Extract domain from current request host
        $host = $this->request->getHost();
        
        // For tenant routes: {tenant}.{domain} pattern
        // Extract the domain part after the first dot
        if (tenant() && str_contains($host, '.')) {
            // Remove tenant subdomain to get base domain
            $parts = explode('.', $host, 2);
            if (count($parts) === 2) {
                $parameters['domain'] = $parts[1];
                return $parameters;
            }
        }

        // Fallback: use first central domain from config
        $centralDomains = config('tenancy.central_domains', []);
        if (!empty($centralDomains)) {
            $parameters['domain'] = $centralDomains[0];
        }

        return $parameters;
    }
}
