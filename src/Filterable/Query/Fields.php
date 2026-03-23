<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

use Totem\SamSkeleton\Filterable\Concerns\Normalizer;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldQuery;
use Totem\SamSkeleton\Filterable\FilterableRequest;

class Fields
{
    use Normalizer;

    /** @var string[] */
    protected array $fields = [];

    /** @var string[] */
    protected array $parsedFields = [];

    public function __construct(
        protected string $modelTable,
    ) {
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string|array ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedFields(array|string ...$fields): static
    {
        if (count($fields) === 1 && is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->fields = $this->normalizeFields($fields);

        return $this;
    }

    /**
     * @return string[]
     */
    public function parseFromRequest(FilterableRequest $request): array
    {
        if ($this->fields === []) {
            return [];
        }

        if ($this->parsedFields !== []) {
            return $this->parsedFields;
        }

        $unknownFields = [];

        foreach ($request->fields() as $resource => $fields) {
            foreach ($fields as $field) {
                if ($request->usingKebabCase() && str_contains($field, '-')) {
                    $field = str_replace('-', '', $field);
                }

                $qualified = $resource !== $request->defaultResourcePrefix()
                    ? $resource . '.' . $field
                    : $this->getQualifiedFieldName($field);

                $this->parsedFields[] = $qualified;

                if (! in_array($qualified, $this->fields, true)) {
                    $unknownFields[] = $qualified;
                }
            }
        }

        if ($unknownFields !== []) {
            throw InvalidFieldQuery::make($unknownFields, $this->fields);
        }

        $this->parsedFields = array_unique($this->parsedFields);

        return $this->parsedFields;
    }

    private function getQualifiedFieldName(string $field): string
    {
        if (str_contains($field, '.')) {
            return $field;
        }

        return $this->modelTable ? $this->modelTable . '.' . $field : $field;
    }
}
