<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use Totem\SamSkeleton\Bundles\Resource\ChunkedApiCollection;

class FixtureRespondingCollection extends ChunkedApiCollection
{
    public function withResponse($request, $response): void
    {
        $response->headers->set('X-Fixture', 'called');
    }
}
