<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollection extends ResourceCollection
{
    public function __construct($resource, ?string $collects = null)
    {
        if (! $this->collects && $collects !== null) {
            $this->collects = $collects;
        }

        parent::__construct($resource);
    }

    public function with($request): array
    {
        return [
            'apiVersion' => config('app.api'),
        ];
    }
}
