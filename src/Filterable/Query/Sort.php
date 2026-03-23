<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

use Totem\SamSkeleton\Filterable\Concerns\Normalizer;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidSortQuery;
use Totem\SamSkeleton\Filterable\FilterableRequest;

class Sort
{
    use Normalizer;

    /** @var string[] */
    protected array $sorts = [];

    /** @var array<string, 'asc' | 'desc'> */
    protected array $parsedSorts = [];

    public function __construct(
        protected string $modelTable,
    ) {
    }

    /**
     * @return string[]
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedSorts(array|string ...$fields): static
    {
        if (count($fields) === 1 && is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->sorts = $this->normalizeFields($fields);

        return $this;
    }

    /**
     * @return array<string, 'asc' | 'desc'>
     */
    public function parseFromRequest(FilterableRequest $request): array
    {
        if ($this->sorts === []) {
            return [];
        }

        if ($this->parsedSorts !== []) {
            return $this->parsedSorts;
        }

        $this->parseSortField($request->sort());

        return $this->parsedSorts;
    }

    /**
     * @param array<string, 'asc' | 'desc'> $requestedSorts
     *
     * @return array<string, 'asc' | 'desc'>
     */
    public function parseSortField(array $requestedSorts): array
    {
        if ($this->sorts === []) {
            return [];
        }

        if ($this->parsedSorts !== []) {
            return $this->parsedSorts;
        }

        $unknownSorts = [];

        foreach ($requestedSorts as $field => $direction) {
            $qualified = $this->modelTable ? $this->modelTable . '.' . $field : $field;

            $this->parsedSorts[$qualified] = $direction;

            if (! in_array($qualified, $this->sorts, true)) {
                $unknownSorts[] = $qualified;
            }
        }

        if ($unknownSorts !== []) {
            throw InvalidSortQuery::make($unknownSorts, $this->sorts);
        }

        return $this->parsedSorts;
    }
}
