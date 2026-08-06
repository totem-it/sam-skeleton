<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache;

use Totem\SamSkeleton\Cache\Cacheable;
use Totem\SamSkeleton\Cache\CachedQuery;
use Totem\SamSkeleton\Cache\CachesQueries;

/**
 * Declares no `cacheNamespace()`
 */
final class FixtureNamelessRepository implements Cacheable
{
    use CachesQueries;

    #[CachedQuery]
    public function all(): string
    {
        return 'all';
    }
}
