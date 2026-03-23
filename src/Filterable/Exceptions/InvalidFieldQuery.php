<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidFieldQuery extends BadRequestHttpException
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
            sprintf('Requested fields [%s] are not allowed. Allowed fields are [%s].', $unknown, $allowed),
        );
    }

    /**
     * @param string[] $unknown
     * @param string[] $allowed
     */
    public static function make(array $unknown, array $allowed): self
    {
        return new self($unknown, $allowed);
    }
}
