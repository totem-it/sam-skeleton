<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ServiceProvider;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Log\LogManager;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

it('can calls the trait method correctly', function (): void {
    $app = new FixtureProvider($this->app);

    expect($app)
        ->testableAuth()->toBeInstanceOf(Factory::class)
        ->testableConfig()->toBeInstanceOf(Repository::class)
        ->testableEvents()->toBeInstanceOf(Dispatcher::class)
        ->testableFilesystem()->toBeInstanceOf(Filesystem::class)
        ->testableLog()->toBeInstanceOf(LogManager::class)
        ->testableRouter()->toBeInstanceOf(Registrar::class);
});
