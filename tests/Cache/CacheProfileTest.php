<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache;

use Pest\Expectation;
use Totem\SamSkeleton\Cache\CacheProfile;

mutates(CacheProfile::class);

it('defines ttl for every profile', function (CacheProfile $profile, array $ttl): void {
    expect($profile->ttl())->toBe($ttl);
})->with([
    'dictionary' => [CacheProfile::DICTIONARY, [3600, 604800]],
    'short' => [CacheProfile::SHORT, [60, 300]],
    'long' => [CacheProfile::LONG, [3600, 86400]],
]);

it('keeps fresh lower than ttl for every profile', function (): void {
    foreach (CacheProfile::cases() as $profile) {
        $ttl = $profile->ttl();

        expect($ttl)
            ->toHaveCount(2)
            ->sequence(
                fn (Expectation $item) => $item
                    ->toBeGreaterThan(0)
                    ->toBeLessThan($ttl[1]),
                fn (Expectation $item) => $item
                    ->toBeGreaterThan($ttl[0]),
            );
    }
});
