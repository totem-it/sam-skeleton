<?php

declare(strict_types=1);

namespace Totem\SamSkeleton;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Router;

trait ContainerServices
{
    protected function config(): ConfigContract
    {
        return $this->app['config'];
    }

    protected function events(): DispatcherContract
    {
        return $this->app['events'];
    }

    protected function router(): Router|Registrar|BindingRegistrar
    {
        return $this->app['router'];
    }
}
