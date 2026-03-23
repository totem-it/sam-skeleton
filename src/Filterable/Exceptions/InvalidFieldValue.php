<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Exceptions;

use InvalidArgumentException;

class InvalidFieldValue extends InvalidArgumentException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dots(): static
    {
        return new static('Field cannot start or end with a dot.');
    }

    public static function multipleDots(): static
    {
        return new static('Field cannot contain consecutive dots.');
    }

    public static function nonString(): static
    {
        return new static('Non-string field provided.');
    }
}
