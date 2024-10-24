<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TrustOnlyAuthenticated
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->getUser($request)->getAttribute('uuid') !== $this->getRoute($request)) {
            $this->throwException();
        }

        return $next($request);
    }

    private function getRoute(Request $request): string
    {
        return tap($request->route('uuid'), function ($uuid) {
            if (! $uuid) {
                $this->throwException();
            }
        });
    }

    private function getUser(Request $request): mixed
    {
        return tap($request->user(), function ($user) {
            if (! $user) {
                $this->throwException();
            }
        });
    }

    private function throwException(): void
    {
        throw new AccessDeniedHttpException(__('The user is not allowed to modify it.'));
    }
}