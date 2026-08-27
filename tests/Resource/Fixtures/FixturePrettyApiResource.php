<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use Totem\SamSkeleton\Bundles\Resource\ApiResource;

class FixturePrettyApiResource extends ApiResource
{
    public function jsonOptions(): int
    {
        return JSON_PRETTY_PRINT;
    }
}
