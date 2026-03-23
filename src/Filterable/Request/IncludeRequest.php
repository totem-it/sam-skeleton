<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\RequestConfig;

readonly class IncludeRequest
{
    /**
     * @param array<string, string[]> $input
     */
    public function __construct(
        private array $input = [],
    ) {
    }

    /**
     * @param array<string, string[]>|string $fieldSets
     *
     * @return string[]
     */
    public static function parse(array|string $fieldSets): array
    {
        return (new static($fieldSets ? self::normalizeInput($fieldSets) : []))();
    }

    /**
     * @return string[]
     */
    public function __invoke(): array
    {
        return $this->input;
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
