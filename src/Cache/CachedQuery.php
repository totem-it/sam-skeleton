<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

use Attribute;

/**
 * Marks a method as safe to serve from cache.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CachedQuery
{
    public function __construct(
        public CacheProfile|null $profile = null,
    ) {
    }
}
