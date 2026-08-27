<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;

/**
 * Overrides the json options on the collection itself, not on the collected resource.
 */
class FixtureOptionsCollection extends ApiCollection
{
    public static int $options = 0;

    public $collects = ApiResource::class;

    public function jsonOptions(): int
    {
        return static::$options;
    }
}
