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
        public readonly string $name,
        public array $headers = [],
        public array $filters = [],
    ) {
    }

    public function addFilter(FilterColumn $filter): self
    {
        if (! isset($this->filters[$filter->label])) {
            $this->filters[$filter->label] = $filter;
        }

        return $this;
    }

    /**
     * @param \Totem\SamSkeleton\DataTable\FilterColumn[] $filters
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
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

    public static function make(string $name): self
    {
        return new self($name);
    }
}
