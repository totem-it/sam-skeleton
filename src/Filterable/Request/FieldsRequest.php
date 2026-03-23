<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Request;

use Totem\SamSkeleton\Filterable\Enum\RequestConfig;

readonly class FieldsRequest
{
    /**
     * @param array<array-key, string> $input
     */
    public function __construct(
        private array $input = [],
    ) {
    }

    /**
     * @param array<array-key, string>|string $fieldSets
     *
     * @return array<string, array<string, string>>
     */
    public static function parse(array|string $fieldSets): array
    {
        return (new self(self::normalizeInput($fieldSets)))();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function __invoke(): array
    {
        if ($this->input === []) {
            return [];
        }

        $result = [];

        foreach ($this->input as $resource => $fields) {
            if (is_numeric($resource)) {
                $resource = RequestConfig::RESOURCE_PREFIX->value;
            }

            $values = self::normalizeInput($fields);

            $result[$resource] = ! array_key_exists($resource, $result)
                ? $values
                : array_merge($result[$resource], $values);
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
