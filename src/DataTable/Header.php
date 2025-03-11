<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\DataTable;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, string|bool>
 */
readonly class Header implements Arrayable
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
}
