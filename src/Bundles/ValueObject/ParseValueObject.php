<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\ValueObject;

trait ParseValueObject
{
    /**
     * Parse the property to a trimmed string or returns null.
     */
    protected static function trimOrNull(string|null $value): string|null
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * Parse the property to an int or returns null.
     */
    protected static function intOrNull(string|null $value): int|null
    {
        if ($value === null) {
            return null;
        }

        return (int) $value;
    }
}
