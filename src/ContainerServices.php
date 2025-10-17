<?php

declare(strict_types=1);

namespace Totem\SamSkeleton;

use Illuminate\Contracts\Auth\Factory as AuthContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Router;

trait ContainerServices
{
    protected function auth(): AuthContract
    {
        return $this->app['auth'];
    }

    protected function config(): ConfigContract
    {
        return $this->app['config'];
    }

    protected function events(): DispatcherContract
    {
        return $this->app['events'];
    }

    protected function filesystem(string $disk = 'public'): Filesystem
    {
        return $this->app['filesystem']->disk($disk);
    }

    protected function log(): LogManager
    {
        return $this->app['log'];
    }

    protected function router(): Router|Registrar|BindingRegistrar
    {
        return $this->app['router'];
    }
}
