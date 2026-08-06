<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

use Illuminate\Support\Arr;
use LogicException;

/**
 * @see \Totem\SamSkeleton\Cache\Cacheable
 */
trait CachesQueries
{
    /**
     * Cache domain key.
     *
     * @return non-empty-string
     */
    public function cacheNamespace(): string
    {
        return '';
    }

    /**
     * Cache the next call from the cache.
     *
     * @noinspection PhpDocSignatureInspection
     *
     * @return static
     *
     * @phpstan-return \Totem\SamSkeleton\Cache\CacheProxy<static>
     */
    public function cached(CacheProfile|null $profile = null): CacheProxy
    {
        return new CacheProxy($this, $this->cacheTags(), $profile, $this->resolveQueryCache());
    }

    /**
     * Drop every cached read.
     */
    public function invalidateCache(): void
    {
        $this->resolveQueryCache()->invalidate(...Arr::wrap($this->cacheTags()));
    }

    /**
     * List of cache domain keys.
     *
     * @return non-empty-string|list<non-empty-string>
     */
    protected function cacheTags(): string|array
    {
        if (! $this->cacheNamespace()) {
            throw new LogicException(sprintf(
                'Class [%s] must declare a non-empty public [cacheNamespace()] method.',
                static::class,
            ));
        }

        return $this->cacheNamespace();
    }

    private function resolveQueryCache(): QueryCache
    {
        return app(QueryCache::class);
    }
}
