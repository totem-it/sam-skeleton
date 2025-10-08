<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\DataTable;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use UnexpectedValueException;

/**
 * @implements \Illuminate\Contracts\Support\Arrayable<array-key, mixed>
 */
class DataTable implements Arrayable
{
    /**
     * @param \Totem\SamSkeleton\DataTable\DataTableView[] $views
     */
    public function __construct(
        public string|null $defaultView = null,
        public array $views = [],
    ) {
    }

    public function addView(DataTableView $view): self
    {
        if (! isset($this->views[$view->key])) {
            $this->views[$view->key] = $view;
        }

        return $this;
    }

    public function setDefaultView(string $view): self
    {
        if (isset($this->views[$view])) {
            $this->defaultView = $view;
        }

        return $this;
    }

    /**
     * @param string|\Totem\SamSkeleton\DataTable\DataTableView[] $views
     */
    public function setViews(array|string $views): self
    {
        if (is_string($views) && is_subclass_of($views, BackedEnum::class) && enum_exists($views)) {
            return $this->setViews(
                array_map(static fn (BackedEnum $view) => DataTableView::make((string) $view->value), $views::cases())
            );
        }

        foreach ($views as $view) {
            $this->addView($view);
        }

        if ($this->defaultView === null && $views !== []) {
            $this->setDefaultView($views[0]->key);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'defaultView' => $this->defaultView,
            'views' => array_map(static fn (DataTableView $value) => $value->toArray(), $this->views),
        ];
    }

    public function view(string $key): DataTableView
    {
        return $this->views[$key] ?? throw new UnexpectedValueException('View key [' . $key . '] is not registered.');
    }

    /**
     * @param string|\Totem\SamSkeleton\DataTable\DataTableView[] $views
     */
    public static function make(array|string $views = []): self
    {
        return (new self())->setViews($views);
    }
}
