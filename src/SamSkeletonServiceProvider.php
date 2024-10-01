<?php

declare(strict_types=1);

namespace Totem\SamSkeleton;

use Illuminate\Support\ServiceProvider;
use Totem\SamSkeleton\Bundles\Middleware\ForceJsonMiddleware;

class SamSkeletonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['router']->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);
    }
}
