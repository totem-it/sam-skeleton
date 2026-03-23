<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\RequestConfig;
use Totem\SamSkeleton\Filterable\Enum\SortDirection;

readonly class SortRequest
{
    /**
     * @param array<string, string[]> $input
     */
    public function __construct(
        private array $input = [],
    ) {
    }

    /**
     * @param array<string, 'asc' | 'desc'>|string $fieldSets
     *
     * @return array<string, 'asc' | 'desc'>
     */
    public static function parse(array|string $fieldSets): array
    {
        return (new static($fieldSets ? self::normalizeInput($fieldSets) : []))();
    }

    /**
     * @return array<string, 'asc' | 'desc'>
     */
    public function __invoke(): array
    {
        $result = [];

        foreach (self::normalizeInput($this->input) as $field) {
            $key = ltrim($field, '-');

            $result[$key] = $field[0] === '-' ? SortDirection::DESC->value : SortDirection::ASC->value;
        }

        return $result;

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
}
