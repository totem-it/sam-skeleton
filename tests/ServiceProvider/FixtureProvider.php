<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ServiceProvider;

use Illuminate\Contracts\Auth\Factory as AuthContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Totem\SamSkeleton\ContainerServices;

class FixtureProvider extends ServiceProvider
{
    use ContainerServices;

    public function testableAuth(): AuthContract
    {
        return $this->auth();
    }

    public function testableConfig(): ConfigContract
    {
        return $this->config();
    }

    public function testableEvents(): DispatcherContract
    {
        return $this->events();
    }

    public function testableFilesystem(string $disk = 'public'): Filesystem
    {
        return $this->filesystem($disk);
    }

    public function testableLog(): LogManager
    {
        return $this->log();
    }

    public function testableRouter(): Router|Registrar|BindingRegistrar
    {
        return $this->router();
    }
}
