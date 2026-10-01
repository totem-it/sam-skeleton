<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ValueObject;

use Totem\SamSkeleton\ValueObject\ParseValueObject;

class FixtureParseValueObject
{
    use ParseValueObject;

    public function callTrimOrNull(mixed $value): ?string
    {
        return self::trimOrNull($value);
    }

    public function callIntOrNull(mixed $value): ?int
    {
        return self::intOrNull($value);
    }
}
