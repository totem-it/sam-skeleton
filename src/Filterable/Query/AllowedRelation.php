<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

readonly class AllowedRelation
{
    private function __construct(
        public string $name,
        public string $alias,
    ) {
    }

    public static function make(string $relation, ?string $alias = null): self
    {
        return new self($relation, $alias ?? $relation);
    }
}
