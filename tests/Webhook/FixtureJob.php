<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Webhook;

use Illuminate\Contracts\Queue\ShouldQueue;

readonly class FixtureJob implements ShouldQueue
{
    public function __construct(
        private array $data = []
    ) {
    }

    public function __invoke()
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
