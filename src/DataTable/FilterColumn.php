<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\DataTable;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<string, string|bool>
 */
class FilterColumn implements Arrayable
{
    /**
     * @var array<int, array{title: string, value: string}>
     */
    public array $items = [];

    /**
     * @param string|array<int, array{title?: string, value?: string|int}>|array<string, string>|int[] $items
     */
    public function __construct(
        public string $label,
        public string|null $icon = null,
        string|array $items = []
    ) {
        $this->items = $this->getItems($items);
    }

    public function toArray(): array
    {
        return (array) $this;
    }

    /**
     * @param string|array<int, array{title: string, value: string}>|int[] $items
     *
     * @return array<int, array{title: string, value: string}>
     */
    private function getItems(string|array $items): array
    {
        if (is_string($items) && is_subclass_of($items, BackedEnum::class) && enum_exists($items)) {
            return array_map(
                static fn ($enum) => ['title' => $enum->name, 'value' => $enum->value],
                $items::cases()
            );
        }

        if ($this->isNestedArray($items)) {
            return $items;
        }

        if ($this->isArray($items)) {
            return array_map(
                static fn ($key, $value) => ['title' => $key, 'value' => is_int($value) ? $key : $value],
                $items,
                array_keys($items)
            );
        }

        return (array) $items;
    }

    /**
     * @param string|array<int, array{title: string, value: string}>|int[] $items
     */
    private function isArray(array|string $items): bool
    {
        return is_array($items) && count($items) > 0;
    }

    /**
     * @param string|array<int, array{title?: string, value?: string}>|int[] $items
     */
    private function isNestedArray(array|string $items): bool
    {
        return $this->isArray($items)
            && array_key_exists(0, $items)
            && is_array($items[0])
            && array_key_exists('title', $items[0])
            && array_key_exists('value', $items[0]);
    }
}
