<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache;

use Totem\SamSkeleton\Cache\Cacheable;
use Totem\SamSkeleton\Cache\CachedQuery;
use Totem\SamSkeleton\Cache\CacheProfile;
use Totem\SamSkeleton\Cache\CachesQueries;

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

        return 'all';
    }

    #[CachedQuery(CacheProfile::DICTIONARY)]
    public function find(int $id, bool $withPrices = false): string
    {
        $this->calls++;

        return 'find:' . $id . ':' . ($withPrices ? '1' : '0');
    }

    #[CachedQuery]
    public function search(string $term, bool $exact = false, int ...$ids): string
    {
        $this->calls++;

        return 'search:' . $term . ':' . ($exact ? '1' : '0') . ':' . implode('-', $ids);
    }

    #[CachedQuery]
    public function touch(): void
    {
        $this->calls++;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function write(array $values): int
    {
        $this->calls++;

        return count($values);
    }
}
