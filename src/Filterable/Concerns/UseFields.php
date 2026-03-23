<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Concerns;

trait UseFields
{
    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedFields(array|string ...$fields): static
    {
        $this->fields
            ->allowedFields(...$fields)
            ->parseFromRequest($this->request);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields->getFields();
    }
}
