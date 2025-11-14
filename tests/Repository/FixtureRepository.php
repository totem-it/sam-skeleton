<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Repository;

use Totem\SamSkeleton\Repository\UseRepository;

class FixtureRepository
{
    use UseRepository;

    public function testableNotFound(string $msg): void
    {
        $this->throwNotFound($msg);
    }

    public function testableLocked(string $msg): void
    {
        $this->throwLocked($msg);
    }

    public function testableMissing(string $msg): void
    {
        $this->throwMissing($msg);
    }
}
