<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

enum CacheProfile
{
    case DICTIONARY;
    case SHORT;
    case LONG;

    /**
     * Stale-while-revalidate window in seconds: entries are served from cache
     * for `fresh` seconds, then recomputed in the background until `ttl`.
     *
     * @return array{0: int, 1: int}
     */
    public function ttl(): array
    {
        return match ($this) {
            self::DICTIONARY => [$this->hours(1), $this->days(7)],
            self::SHORT => [$this->minutes(1), $this->minutes(5)],
            self::LONG => [$this->hours(1), $this->days(1)],
        };
    }

    private function days(int $days): int
    {
        return $this->hours($days * 24);
    }

    private function hours(int $hours): int
    {
        return $this->minutes($hours * 60);
    }

    private function minutes(int $minutes): int
    {
        return $minutes * 60;
    }
}
