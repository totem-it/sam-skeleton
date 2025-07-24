<?php

declare(strict_types=1);

namespace Totem\SamSkeleton;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Events\JobDeleted;
use Totem\SamSkeleton\Bundles\Middleware\ForceJsonMiddleware;
use Totem\SamSkeleton\Webhook\Webhook;

class SamSkeletonServiceProvider extends ServiceProvider
{
    use ContainerServices;

    public function boot(): void
    {
        $this->router()->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);

        $this->events()->listen(JobDeleted::class, Webhook::class);
    }

    public function register(): void
    {
        $this->config()->set([
            'app.api' => env('APP_API', '1.0.0'),
        ]);
    }
}
