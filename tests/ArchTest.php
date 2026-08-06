<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

use Attribute;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Cache\Cacheable;
use Totem\SamSkeleton\Cache\CachedQuery;
use Totem\SamSkeleton\Cache\CacheProfile;
use Totem\SamSkeleton\Cache\CacheProxy;
use Totem\SamSkeleton\Cache\CachesQueries;
use Totem\SamSkeleton\Cache\QueryCache;
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

describe('Cache', function (): void {
    arch('enum')
        ->expect(CacheProfile::class)
        ->toBeEnum();

    arch('interface')
        ->expect(Cacheable::class)
        ->toBeInterface();

    arch('contract exposes reading and invalidation')
        ->expect(Cacheable::class)
        ->toHaveMethods(['cached', 'invalidateCache']);

    arch('trait')
        ->expect(CachesQueries::class)
        ->toBeTrait();

    arch('attribute')
        ->expect(CachedQuery::class)
        ->toHaveAttribute(Attribute::class)
        ->toBeReadonly();

    arch('base classes are final')
        ->expect([QueryCache::class, CachedQuery::class, CacheProxy::class])
        ->toBeFinal();
});
