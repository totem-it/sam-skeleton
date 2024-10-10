<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (str_contains($request->headers->get('Accept', '*'), '*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
