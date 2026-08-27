<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

class ChunkedApiCollection extends ApiCollection
{
    public function __construct($resource, ?string $collects = null)
    {
        parent::__construct($resource, $collects);

        $this->chunked = true;
    }

    /**
     * @return class-string<\Illuminate\Http\Resources\Json\JsonResource>|null
     */
    protected function collects(): ?string
    {
        return parent::collects() ?? ApiResource::class;
    }
}
