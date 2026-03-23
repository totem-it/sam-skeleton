<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Exceptions;

use InvalidArgumentException;
use Totem\SamSkeleton\Filterable\Enum\FilterOperator;

class InvalidFilterOperator extends InvalidArgumentException
{
    public function __construct(string $operator)
    {
        $allowed = implode(', ', array_column(FilterOperator::cases(), 'value'));

        parent::__construct(
            sprintf('Requested operator [%s] is not allowed. Allowed operators are [%s].', $operator, $allowed),
        );
    }
}
