<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

use Totem\SamSkeleton\Filterable\Concerns\Normalizer;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterQuery;
use Totem\SamSkeleton\Filterable\FilterableRequest;

class Filter
{
    use Normalizer;

    /** @var string[] */
    protected array $filters = [];

    /** @var AllowedFilter[] */
    protected array $parsedFilters = [];

    public function __construct(
        protected string $modelTable,
    ) {
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedFilters(array|string ...$fields): static
    {
        if (count($fields) === 1 && is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->filters = $this->normalizeFields($fields);

        return $this;
    }

    /**
     * @return AllowedFilter[]
     */
    public function parseFromRequest(FilterableRequest $request): array
    {
        if ($this->filters === []) {
            return [];
        }

        if ($this->parsedFilters !== []) {
            return $this->parsedFilters;
        }

        $unknownFields = [];

        foreach ($request->filters() as $filter) {
            $qualified = $this->modelTable ? $this->modelTable . '.' . $filter->field : $filter->field;

            $this->parsedFilters[] = $filter->rename($qualified);

            if (! in_array($qualified, $this->filters, true)) {
                $unknownFields[] = $qualified;
            }
        }

        if ($unknownFields !== []) {
            throw InvalidFilterQuery::make($unknownFields, $this->filters);
        }

        return $this->parsedFilters;
    }
}
