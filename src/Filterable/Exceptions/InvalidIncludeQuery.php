<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidIncludeQuery extends BadRequestHttpException
{
    /**
     * @param string[] $unknownSorts
     * @param string[] $allowedSorts
     */
    public function __construct(array $unknownSorts, array $allowedSorts)
    {
        $unknown = implode(', ', $unknownSorts);
        $allowed = implode(', ', $allowedSorts);

        parent::__construct(
            sprintf('Requested includes [%s] are not allowed. Allowed includes are [%s].', $unknown, $allowed),
        );
    }

    /**
     * @param string[] $unknown
     * @param string[] $allowed
     */
    public static function make(array $unknown, array $allowed): static
    {
        return new static($unknown, $allowed);
    }
}
