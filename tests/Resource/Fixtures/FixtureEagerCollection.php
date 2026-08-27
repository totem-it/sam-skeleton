<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

class FixtureEagerCollection extends FixtureProbeCollection
{
    protected int $chunkThreshold = 1;
}
