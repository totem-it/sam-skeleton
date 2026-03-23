<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Totem\SamSkeleton\Filterable\Concerns\HandleBuilderCalls;
use Totem\SamSkeleton\Filterable\Concerns\UseFields;
use Totem\SamSkeleton\Filterable\Concerns\UseFilters;
use Totem\SamSkeleton\Filterable\Concerns\UseSorts;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterableArgument;
use Totem\SamSkeleton\Filterable\Query\AllowedRelation;
use Totem\SamSkeleton\Filterable\Query\Fields;
use Totem\SamSkeleton\Filterable\Query\Filter;
use Totem\SamSkeleton\Filterable\Query\Includes;
use Totem\SamSkeleton\Filterable\Query\Sort;

class Filterable
{
    use HandleBuilderCalls;
    use UseFields;
    use UseFilters;
    use UseSorts;

    protected Request $request;
    protected string $modelTable;
    protected Fields $fields;
    protected Filter $filters;
    protected Includes $includes;
    protected Sort $sorts;

    public function __construct(
        protected Builder $scopedBuilder,
        ?Request $request = null,
    ) {
        $this->request = $request ? FilterableRequest::fromRequest($request) : self::makeRequest();
        $this->modelTable = $scopedBuilder->getModel()->getTable();
        $this->fields = $this->newFields();
        $this->sorts = $this->newSorts();
        $this->filters = $this->newFilters();
        $this->includes = $this->newIncludes();
    }

    /**
     * @param class-string<Model>|\Illuminate\Database\Eloquent\Builder $model
     * @param \Illuminate\Http\Request|null $request
     *
     * @return static
     */
    public static function for(string|Builder $model, ?Request $request = null): static
    {
        if (is_subclass_of($model, Model::class)) {
            $model = $model::query();
        }

        if (! $model instanceof Builder) {
            throw new InvalidFilterableArgument();
        }

        return new static($model, $request);
    }

    public static function create(?Request $request = null): FilterableBuilder
    {
        return FilterableBuilder::create($request ?? self::makeRequest());
    }

    protected static function makeRequest(): FilterableRequest
    {
        return FilterableRequest::fromRequest(app(Request::class));
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getBuilder(): Builder
    {
        return $this->scopedBuilder;
    }

    public function withRequest(Request $request, bool $useKebabCase = false): static
    {
        $this->request = FilterableRequest::fromRequest($request, ['kebab-case' => $useKebabCase]);

        return $this;
    }

    protected function newFields(): Fields
    {
        return new Fields($this->modelTable);
    }

    protected function newFilters(): Filter
    {
        return new Filter($this->modelTable);
    }

    protected function newIncludes(): Includes
    {
        return new Includes($this->modelTable);
    }

    protected function newSorts(): Sort
    {
        return new Sort($this->modelTable);
    }

    protected function pipelineQuery(): void
    {
        $selectColumns = $this->fields->parseFromRequest($this->request);
        $orderByClauses = $this->sorts->parseFromRequest($this->request);
        $filterColumns = $this->filters->parseFromRequest($this->request);
        $includeRelations = $this->includes->parseFromRequest($this->request);

        if ($selectColumns !== []) {
            $this->scopedBuilder->beforeQuery(
                static fn (QueryBuilder $builder) => $builder->select($selectColumns)
            );
        }

        foreach ($orderByClauses as $column => $direction) {
            $this->scopedBuilder->beforeQuery(
                static fn (QueryBuilder $builder) => $builder->orderBy($column, $direction)
            );
        }

        foreach ($filterColumns as $filter) {
            $this->scopedBuilder->beforeQuery($filter->query(...));
        }

        if ($includeRelations !== []) {
            $this->scopedBuilder->with($includeRelations);
        }
    }

    public function allowedIncludes(array|string|AllowedRelation ...$relationships): static
    {
        $this->includes
            ->allowedIncludes(...$relationships)
            ->parseFromRequest($this->request);

        return $this;
    }

    public function getIncludes(): array
    {
        return $this->includes->getIncludes();
    }
}
