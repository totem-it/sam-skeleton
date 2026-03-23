<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\RequestConfig;
use Totem\SamSkeleton\Filterable\Enum\SortDirection;

readonly class SortRequest
{
    /**
     * @param string[] $input
     */
    public function __construct(
        private array $input = [],
    ) {
    }

    /**
     * @return array<string, 'asc' | 'desc'>
     */
    public static function parse(string $fieldSets): array
    {
        return (new self($fieldSets ? self::normalizeInput($fieldSets) : []))();
    }

    /**
     * @return array<string, 'asc' | 'desc'>
     */
    public function __invoke(): array
    {
        $result = [];

        foreach ($this->input as $field) {
            $key = ltrim($field, '-');

            $result[$key] = $field[0] === '-' ? SortDirection::DESC->value : SortDirection::ASC->value;
        }

        return $result;

    }

    /**
     * @param string $input
     *
     * @return string[]
     */
    protected static function normalizeInput(string $input): array
    {
        return explode(RequestConfig::DELIMITER->value, $input);
    }
}
