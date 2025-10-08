<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\DataTable;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, string|bool>
 */
class Header implements Arrayable
{
    public function __construct(
        public string $value,
        public string $title,
        public ?string $align = null,
        public ?bool $sortable = null,
        public ?bool $filterable = null,
        public ?string $description = null,
        public ?string $customFilter = null,
        public string|bool|null $hide = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter((array) $this, static fn ($v) => $v !== null);
    }

    public function disableSort(): self
    {
        $this->sortable = false;

        return $this;
    }

    public function disableFilter(): self
    {
        $this->filterable = false;

        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function description(string $text): self
    {
        $this->description = $text;

        return $this;
    }

    public function filterBy(string $filter): self
    {
        $this->customFilter = $filter;

        return $this;
    }

    public function hide(string|bool $value): self
    {
        $this->hide = $value;

        return $this;
    }

    public static function make(string $title, string $value): self
    {
        return new self($value, $title);
    }
}
