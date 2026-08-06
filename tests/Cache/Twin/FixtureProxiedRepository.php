<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache\Twin;

use Totem\SamSkeleton\Cache\Cacheable;
use Totem\SamSkeleton\Cache\CachedQuery;
use Totem\SamSkeleton\Cache\CachesQueries;

/**
 * Same basename as `Tests\Cache\FixtureProxiedRepository`, same domain, same method name - the collision
 */
final class FixtureProxiedRepository implements Cacheable
{
    use CachesQueries;

    public int $calls = 0;

    public function cacheNamespace(): string
    {
        return 'alpha';
    }

    #[CachedQuery]
    public function all(): string
    {
        $this->calls++;

        return 'twin';
    }
}
