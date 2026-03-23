<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

use ArgumentCountError;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Totem\SamSkeleton\Filterable\Enum\FilterOperator;

readonly class AllowedFilter
{
    /**
     * @param string|string[] $value
     */
    private function __construct(
        public string $field,
        public FilterOperator $operator,
        public string|array $value,
    ) {
    }

    public static function make(string $field, string $operator, mixed $value): self
    {
        $value = is_array($value) ? $value : (string) $value;

        return new self($field, FilterOperator::fromCode($operator), $value);
    }

    public function rename(string $name): self
    {
        return new self($name, $this->operator, $this->value);
    }

    public function query(Builder $query): Builder
    {
        return match ($this->operator) {
            FilterOperator::IS_NULL => $this->applyIsNull($query),
            FilterOperator::NOT_IN => $query->whereNotIn($this->field, $this->value),
            FilterOperator::BETWEEN => $query->whereBetween($this->field, $this->prepareBetweenValues()),
            FilterOperator::NOT_BETWEEN => $query->whereNotBetween($this->field, $this->prepareBetweenValues()),
            FilterOperator::LIKE => $query->whereLike($this->field, $this->prepareLikeValue()),
            FilterOperator::LIKE_START => $query->whereLike($this->field, $this->value . '%'),
            FilterOperator::LIKE_END => $query->whereLike($this->field, '%' . $this->value),
            FilterOperator::NOT_LIKE => $query->whereNotLike($this->field, $this->prepareLikeValue()),
            FilterOperator::NOT_LIKE_START => $query->whereNotLike($this->field, $this->value . '%'),
            FilterOperator::NOT_LIKE_END => $query->whereNotLike($this->field, '%' . $this->value),
            default => $this->where($query),
        };
    }

    private function applyIsNull(Builder $query): Builder
    {
        return $query->whereNull(
            columns: $this->field,
            not: ! $this->value
        );
    }

    private function where(Builder $query): Builder
    {
        if (is_array($this->value)) {
            return $query->whereIn($this->field, $this->value);
        }

        return $query->where($this->field, $this->operator->comparison(), $this->value);
    }

    /**
     * @return string[]
     */
    private function prepareBetweenValues(): array
    {
        if (! is_array($this->value) || count($this->value) < 2) {
            throw new ArgumentCountError('Missing values to filter for field [' . $this->field . '].');
        }

        return strtotime($this->value[0]) ? [
            Carbon::createFromFormat('Y-m-d', $this->value[0])->startOfDay(),
            Carbon::createFromFormat('Y-m-d', $this->value[1])->endOfDay(),
        ] : $this->value;
    }

    private function prepareLikeValue(): string
    {
        return ! str_contains($this->value, '%') ? '%' . $this->value . '%' : $this->value;
    }
}
