<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

/**
 * @property \Totem\SamSkeleton\Bundles\Resource\ApiResource $resource
 * @property \Totem\SamSkeleton\Bundles\Resource\ApiCollection $collection
 */
trait AdditionalResourceData
{
    public function with($request): array
    {
        return [
            'apiVersion' => config('app.api'),
        ];
    }
}
