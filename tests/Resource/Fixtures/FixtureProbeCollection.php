<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Bundles\Resource\ChunkedApiCollection;

class FixtureProbeCollection extends ChunkedApiCollection
{
    public int $toArrayCalls = 0;

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->toArrayCalls++;

        return parent::toArray($request);
    }
}
