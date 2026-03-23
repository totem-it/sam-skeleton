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

    public static function dots(): self
    {
        return new self('Field cannot start or end with a dot.');
    }

    public static function multipleDots(): self
    {
        return new self('Field cannot contain consecutive dots.');
    }

    public static function nonString(): self
    {
        return new self('Non-string field provided.');
    }
}
