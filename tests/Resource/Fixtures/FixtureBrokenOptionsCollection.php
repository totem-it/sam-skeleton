<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource\Fixtures;

use ReflectionException;
use Totem\SamSkeleton\Bundles\Resource\ChunkedApiCollection;

class FixtureBrokenOptionsCollection extends ChunkedApiCollection
{
    public int $optionCalls = 0;

    /**
     * @throws \ReflectionException
     */
    public function jsonOptions(): int
    {
        $this->optionCalls++;

        if ($this->optionCalls === 1) {
            throw new ReflectionException('Cannot instantiate abstract class');
        }

        return 0;
    }
}
