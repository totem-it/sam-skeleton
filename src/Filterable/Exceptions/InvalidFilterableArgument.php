<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Exceptions;

use InvalidArgumentException;

class InvalidFilterableArgument extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Expected Eloquent model class-string or Builder.');
    }
}
