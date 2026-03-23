<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Query;

use Totem\SamSkeleton\Filterable\Concerns\Normalizer;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidIncludeQuery;
use Totem\SamSkeleton\Filterable\FilterableRequest;

class Includes
{
    use Normalizer;

    /** @var AllowedRelation[] */
    protected array $includes = [];

    /** @var string[] */
    protected array $parsedIncludes = [];

    public function __construct(
        protected string $modelTable,
    ) {
    }

    /**
     * @return AllowedRelation[]
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @param string|array|AllowedRelation ...$fields
     * @phpstan-param string|string[] ...$fields
     */
    public function allowedIncludes(array|string|AllowedRelation ...$fields): static
    {
        if (count($fields) === 1 && is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->includes = $this->normalizeFields($fields);

        return $this;
    }

    /**
     * @return string[]
     */
    public function parseFromRequest(FilterableRequest $request): array
    {
        if ($this->includes === []) {
            return [];
        }

        if ($this->parsedIncludes !== []) {
            return $this->parsedIncludes;
        }

        $unknownIncludes = [];

        foreach ($request->includes() as $relation) {
            if (! array_key_exists($relation, $this->includes)) {
                $unknownIncludes[] = $relation;

                continue;
            }

            $this->parsedIncludes[] = $this->includes[$relation]->name;
        }

        if ($unknownIncludes !== []) {
            throw InvalidIncludeQuery::make($unknownIncludes, array_keys($this->includes));
        }

        return $this->parsedIncludes;
    }

    /**
     * @param int[]|string[]|AllowedRelation[] $fields
     *
     * @return Array<string, AllowedRelation>
     */
    protected function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $relation) {
            if ($relation instanceof AllowedRelation) {
                $normalized[$relation->alias] = $relation;

                continue;
            }

            if (! is_string($relation)) {
                throw InvalidFieldValue::nonString();
            }

            if (str_contains($relation, '.')) {
                $this->validateField($relation);
            }

            $parsedRelation = AllowedRelation::make($relation);

            $normalized[$parsedRelation->alias] = $parsedRelation;
        }

        return $normalized;
    }
}
