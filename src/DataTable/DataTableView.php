<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\DataTable;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<array-key, mixed>
 */
class DataTableView implements Arrayable
{
    /**
     * @param \Totem\SamSkeleton\DataTable\Header[] $headers
     * @param \Totem\SamSkeleton\DataTable\FilterColumn[] $filters
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public array $headers = [],
        public array $filters = [],
    ) {
    }

    public function addFilter(FilterColumn $filter, string|int|null $value = null): self
    {
        $key = is_int($value) || $value === null ? $filter->label : $value;

        if (! isset($this->filters[$key])) {
            $this->filters[$key] = $filter;
        }

        return $this;
    }

    /**
     * @param \Totem\SamSkeleton\DataTable\FilterColumn[] $filters
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $key => $filter) {
            $this->addFilter($filter, $key);
        }

        return $this;
    }

    public function addHeader(Header $header): self
    {
        if (! isset($this->headers[$header->value])) {
            $this->headers[$header->value] = $header;
        }

        return $this;
    }

    /**
     * @param \Totem\SamSkeleton\DataTable\Header[] $headers
     */
    public function addHeaders(array $headers): self
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'headers' => array_map(static fn (Header $value) => $value->toArray(), $this->headers),
            'filters' => array_map(static fn (FilterColumn $value) => $value->toArray(), $this->filters),
        ];
    }

    public static function make(string $key, string|null $name = null): self
    {
        return new self($key, $name ?? $key);
    }
}
