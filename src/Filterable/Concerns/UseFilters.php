<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Concerns;

trait UseFilters
{
    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedFilters(array|string ...$fields): static
    {
        $this->filters
            ->allowedFilters(...$fields)
            ->parseFromRequest($this->request);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters->getFilters();
    }
}
