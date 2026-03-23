<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Concerns;

trait UseSorts
{
    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedSorts(array|string ...$fields): static
    {
        $this->sorts
            ->allowedSorts(...$fields)
            ->parseFromRequest($this->request);

        return $this;
    }

    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function defaultSort(array|string ...$fields): static
    {
        if (count($fields) === 1 && is_array($fields[0])) {
            $fields = $fields[0];
        }

        if ($this->request->sort() !== []) {
            return $this;
        }

        $defaultSorts = $this->request->sort(implode(',', $fields));

        $this->sorts
            ->allowedSorts(array_keys($defaultSorts))
            ->parseSortField($defaultSorts);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSorts(): array
    {
        return $this->sorts->getSorts();
    }
}
