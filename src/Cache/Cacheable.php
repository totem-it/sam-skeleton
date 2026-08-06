<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

/**
 * Contract for fetchers reachable through a cache proxy.
 *
 * Implement with the `CachesQueries` trait.
 */
interface Cacheable
{
    /**
     * Read the next call from the cache.
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return static
     *
     * @phpstan-return \Totem\SamSkeleton\Cache\CacheProxy<static>
     */
    public function cached(CacheProfile|null $profile = null): CacheProxy;

    /**
     * Drop every cached read. Call after save.
     */
    public function invalidateCache(): void;
}
