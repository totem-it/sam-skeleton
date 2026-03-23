<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\FilterOperator;
use Totem\SamSkeleton\Filterable\Enum\RequestConfig;
use Totem\SamSkeleton\Filterable\Query\AllowedFilter;

class FiltersRequest
{
    /** @var AllowedFilter[] */
    private array $filters = [];

    /**
     * @param array<string, string|array<string, string|int|null>> $input
     */
    public function __construct(
        private readonly array $input = [],
    ) {
    }

    /**
     * @param array<string, string|array<string, string|int|null>> $fieldSets
     *
     * @return AllowedFilter[]
     */
    public static function parse(array $fieldSets): array
    {
        return (new self($fieldSets))();
    }

    /**
     * @return AllowedFilter[]
     */
    public function __invoke(): array
    {
        if ($this->input === []) {
            return [];
        }

        foreach ($this->input as $field => $values) {
            if (is_array($values)) {
                $this->buildArray($field, $values);

                continue;
            }

            $this->filters[] = $this->buildSingleFilter($field, $values);
        }

        return $this->filters;
    }

    /**
     * @param array<string, string[]>|string $input
     *
     * @return string[]
     */
    protected static function normalizeInput(array|string $input): array
    {
        return is_string($input) ? explode(RequestConfig::DELIMITER->value, $input) : $input;
    }

    /**
     * @param array<string, string|int> $values
     */
    private function buildArray(string $field, array $values): void
    {
        foreach ($values as $operator => $value) {
            $this->filters[] = AllowedFilter::make(
                field: $field,
                operator: $operator,
                value: $this->getFilterValue((string) $value)
            );
        }
    }

    private function buildSingleFilter(string $field, string|int|null $values): AllowedFilter
    {
        if ($this->isNullValue($values)) {
            return AllowedFilter::make(
                field: $field,
                operator: FilterOperator::IS_NULL->value,
                value: 'true',
            );
        }

        return AllowedFilter::make(
            field: $field,
            operator: RequestConfig::DEFAULT_FILTER->value,
            value: $this->getFilterValue($values),
        );
    }

    private function isNullValue(string|null $value): bool
    {
        return in_array($value, [null, 'null', 'NULL', 'Null'], true);
    }

    /**
     * @return string|string[]
     */
    private function getFilterValue(string $value): array|string
    {
        if (str_contains($value, ',')) {
            return self::normalizeInput($value);
        }

        return $value;
    }
}
