<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

use Totem\SamSkeleton\SamSkeletonServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SamSkeletonServiceProvider::class];
    }
}
