<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\SamSkeletonServiceProvider;

//arch()->preset()->php();

//arch()->preset()->security();

arch('no debug')
    ->expect('Totem\SamSkeleton')
    ->not->toUse(['die', 'dd', 'dump', 'var_dump']);

arch('no env()')
    ->expect('Totem\SamSkeleton')
    ->not()->toUse('env')
    ->ignoring(SamSkeletonServiceProvider::class);

arch('strict types')
    ->expect('Totem\SamSkeleton')
    ->toUseStrictTypes();

arch('strict equality')
    ->expect('Totem\SamSkeleton')
    ->toUseStrictEquality();

arch('Bundle Middleware')
    ->expect('Totem\SamSkeleton\Bundles\Middleware')
    ->toHaveMethod('handle')
    ->toHaveSuffix('Middleware');

describe('Bundle Resource', function (): void {
    arch('resource')
        ->expect(ApiResource::class)
        ->toExtend(JsonResource::class);

    arch('collection')
        ->expect(ApiCollection::class)
        ->toExtend(ResourceCollection::class);
});
