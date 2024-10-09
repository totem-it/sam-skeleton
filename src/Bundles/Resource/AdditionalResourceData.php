<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

trait AdditionalResourceData
{
    /**
     * @return array{apiVersion: string}
     */
    public function with($request): array
    {
        return [
            'apiVersion' => config('app.api'),
        ];
    }
}
