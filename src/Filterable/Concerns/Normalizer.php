<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Concerns;

use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;

trait Normalizer
{
    /**
     * @param array<array-key, string|int> $fields
     *
     * @return string[]
     */
    protected function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $resource => $field) {
            if (! is_string($field)) {
                throw InvalidFieldValue::nonString();
            }

            $normalized[] = $this->prepareField($resource, $field);
        }

        return array_unique($normalized);
    }

    protected function validateField(string $field): void
    {
        if (str_starts_with($field, '.') || str_ends_with($field, '.')) {
            throw InvalidFieldValue::dots();
        }

        if (str_contains($field, '..')) {
            throw InvalidFieldValue::multipleDots();
        }
    }

    protected function prepareField(string|int $resource, string $field): string
    {
        if (str_contains($field, '.')) {
            $this->validateField($field);

            return $field;
        }

        return (is_int($resource) ? $this->modelTable : $resource) . '.' . $field;
    }
}
