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

    public function register(): void
    {
        $this->app['config']->set([
            'app.api' => env('APP_API', '1.0.0'),
        ]);
    }
}
