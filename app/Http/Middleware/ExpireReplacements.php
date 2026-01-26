<?php

namespace App\Http\Middleware;

use App\Services\ReplacementService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ExpireReplacements
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Cache::add('guardia:expire-replacements:ran', 1, 60)) {
            ReplacementService::expire();
        }

        return $next($request);
    }
}
