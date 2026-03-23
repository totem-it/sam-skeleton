<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\RequestConfig;

readonly class IncludeRequest
{
    /**
     * @param string[] $input
     */
    public function __construct(
        private array $input = [],
    ) {
    }

    /**
     * @param string $fieldSets
     *
     * @return string[]
     */
    public static function parse(string $fieldSets): array
    {
        return (new self($fieldSets ? self::normalizeInput($fieldSets) : []))();
    }

    /**
     * @return string[]
     */
    public function __invoke(): array
    {
        return $this->input;
    }

    /**
     * @return string[]
     */
    protected static function normalizeInput(string $input): array
    {
        return explode(RequestConfig::DELIMITER->value, $input);
    }
}
