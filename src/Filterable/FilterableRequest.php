<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Filterable\Enum\RequestConfig;
use Totem\SamSkeleton\Filterable\Query\AllowedFilter;
use Totem\SamSkeleton\Filterable\Request\FieldsRequest;
use Totem\SamSkeleton\Filterable\Request\FiltersRequest;
use Totem\SamSkeleton\Filterable\Request\IncludeRequest;
use Totem\SamSkeleton\Filterable\Request\SortRequest;

class FilterableRequest extends Request
{
    public bool $kebabCase = false;

    /** @var string[] */
    private array $cachedFields = [];

    /** @var array<string, AllowedFilter> */
    private array $cachedFilters = [];

    /** @var string[] */
    private array $cachedIncludes = [];

    /** @var array<string, 'asc' | 'desc'> */
    private array $cachedSorts = [];

    /**
     * @param array{ kebab-case: boolean } $options
     */
    public static function fromRequest(Request $request, array $options = ['kebab-case' => false]): static
    {
        $instance = static::createFrom($request, new static());

        if ($options['kebab-case']) {
            $instance->kebabCase = true;
        }

        return $instance;
    }

    /**
     * @return array<string, string[]>
     */
    public function fields(): array
    {
        if ($this->cachedFields !== []) {
            return $this->cachedFields;
        }

        $fieldSets = $this->input('fields', []);

        $this->cachedFields = FieldsRequest::parse($fieldSets);

        return $this->cachedFields;
    }

    /**
     * @return AllowedFilter[]
     */
    public function filters(): array
    {
        if ($this->cachedFilters !== []) {
            return $this->cachedFilters;
        }

        $filters = $this->input('filter', []);

        $this->cachedFilters = FilterSRequest::parse($filters);

        return $this->cachedFilters;
    }

    /**
     * @return string[]
     */
    public function includes(): array
    {
        if ($this->cachedIncludes !== []) {
            return $this->cachedIncludes;
        }

        $relations = $this->input('include', '');

        $this->cachedIncludes = IncludeRequest::parse($relations);

        return $this->cachedIncludes;
    }

    /**
     * @return array<string, 'asc' | 'desc'>
     */
    public function sort(string $defaultSort = ''): array
    {
        if ($this->cachedSorts !== []) {
            return $this->cachedSorts;
        }

        $sorts = $this->input('sort', $defaultSort);

        $this->cachedSorts = SortRequest::parse($sorts);

        return $this->cachedSorts;
    }

    public function defaultResourcePrefix(): string
    {
        return RequestConfig::RESOURCE_PREFIX->value;
    }

    public function usingKebabCase(): bool
    {
        return $this->kebabCase;
    }
}
