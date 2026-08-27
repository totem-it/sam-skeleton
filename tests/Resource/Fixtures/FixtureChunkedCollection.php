<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use Totem\SamSkeleton\Bundles\Resource\ApiCollection;

class FixtureChunkedCollection extends ApiCollection
{
    public $collects = FixtureApiResource::class;

    /**
     * @return array<array-key, mixed>
     */
    public function with($request): array
    {
        return [];
    }
}
