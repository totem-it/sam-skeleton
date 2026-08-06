<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Sleep;
use InvalidArgumentException;
use stdClass;
use Totem\SamSkeleton\Cache\CacheProfile;
use Totem\SamSkeleton\Cache\QueryCache;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

mutates(QueryCache::class);

beforeEach(function (): void {
    $this->cache = new QueryCache();
    $this->calls = 0;
    $this->callback = function (): string {
        $this->calls++;

        return 'result';
    };
});

it('executes the callback once and serves subsequent calls from cache', function (CacheProfile $profile): void {
    $first = $this->cache->remember('alpha', 'all', $this->callback, $profile);

    expect($first)
        ->toBe('result');

    $second = $this->cache->remember('alpha', 'all', $this->callback, $profile);

    expect($second)
        ->toBe('result')
        ->and($this->calls)->toBe(1);
})->with([
    'stale-while-revalidate' => [CacheProfile::DICTIONARY],
    'stale-while-revalidate long' => [CacheProfile::LONG],
    'default profile' => [CacheProfile::SHORT],
]);

it('caches separately per argument set', function (): void {
    $this->cache->remember('alpha', 'find', $this->callback, arguments: [1]);
    $this->cache->remember('alpha', 'find', $this->callback, arguments: [2]);

    expect($this->calls)
        ->toBe(2);
});

describe('invalidation', function (): void {
    it('recomputes after the namespace is invalidated', function (): void {
        $this->cache->remember('alpha', 'all', $this->callback);
        $this->cache->invalidate('alpha');
        $this->cache->remember('alpha', 'all', $this->callback);

        expect([$this->calls, $this->cache->version('alpha')])
            ->toMatchArray([2, 2]);
    });

    it('leaves other namespaces untouched when invalidating', function (): void {
        $this->cache->remember('alpha', 'all', $this->callback);
        $this->cache->remember('beta', 'all', $this->callback);

        $this->cache->invalidate('alpha');

        $this->cache->remember('alpha', 'all', $this->callback);
        $this->cache->remember('beta', 'all', $this->callback);

        expect($this->calls)
            ->toBe(3);
    });

    it('invalidates a multi-namespace entry when the first namespace changes', function (): void {
        $namespaces = ['alpha', 'beta'];

        $this->cache->remember($namespaces, 'all', $this->callback);
        $this->cache->invalidate('alpha');
        $this->cache->remember($namespaces, 'all', $this->callback);

        expect($this->calls)
            ->toBe(2);
    });

    it('invalidates a multi-namespace entry when the second namespace changes', function (): void {
        $namespaces = ['alpha', 'beta'];

        $this->cache->remember($namespaces, 'all', $this->callback);
        $this->cache->invalidate('beta');
        $this->cache->remember($namespaces, 'all', $this->callback);

        expect($this->calls)
            ->toBe(2);
    });

    it('sees an invalidation made earlier in the same request', function (): void {
        expect([$this->cache->version('alpha'), $this->cache->key('alpha', 'all')])
            ->toMatchArray([1, 'alpha:v1:all']);

        $this->cache->invalidate('alpha');

        expect([$this->cache->version('alpha'), $this->cache->key('alpha', 'all')])
            ->toBe([2, 'alpha:v2:all']);
    });

    it('refuses to invalidate a namespace that no key could carry', function (): void {
        expect(fn () => $this->cache->invalidate(''))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'non-empty name of printable ASCII'
            );
    });

    it('bumps a namespace once when it is given twice', function (): void {
        expect($this->cache->version('alpha'))
            ->toBe(1);

        $this->cache->invalidate('alpha', 'alpha');

        expect($this->cache->version('alpha'))
            ->toBe(2);
    });

    it('does nothing when given no namespace', function (): void {
        expect($this->cache->version('alpha'))
            ->toBe(1);

        $this->cache->invalidate();

        expect($this->cache->version('alpha'))
            ->toBe(1);
    });
});

it('remember the version for the rest of the request', function (): void {
    expect($this->cache->version('alpha'))
        ->toBe(1);

    Cache::forever('cache-version:alpha', 9);

    expect($this->cache->version('alpha'))
        ->toBe(1);

    $this->cache->flushMemory();

    expect($this->cache->version('alpha'))
        ->toBe(9);
});

it('swallows a lock timeout and drops the remembered version', function (): void {
    expect($this->cache->version('alpha'))
        ->toBe(1);

    Cache::lock('cache-version:alpha:lock', 30)->get();

    Sleep::fake(syncWithCarbon: true);

    $this->cache->invalidate('alpha');

    expect(Cache::get('cache-version:alpha'))
        ->toBe(1);

    Cache::forever('cache-version:alpha', 2);

    expect($this->cache->version('alpha'))
        ->toBe(2);
});

describe('singleton binding', function (): void {
    it('shares one instance for the whole request', function (): void {
        expect(app(QueryCache::class))
            ->toBe(app(QueryCache::class));
    });

    it('resolves a fresh instance once the scope ends', function (): void {
        $first = app(QueryCache::class);

        app()->forgetScopedInstances();

        expect(app(QueryCache::class))
            ->not->toBe($first);
    });

    it('picks up a version bumped by another process once the scope ends', function (): void {
        expect(app(QueryCache::class)->version('alpha'))
            ->toBe(1);

        Cache::forever('cache-version:alpha', 9);

        expect(app(QueryCache::class)->version('alpha'))
            ->toBe(1);

        app()->forgetScopedInstances();

        expect(app(QueryCache::class)->version('alpha'))
            ->toBe(9);
    });
});

describe('key building', function (): void {
    it('builds a readable key without arguments', function (): void {
        expect($this->cache->key('alpha', 'all'))
            ->toBe('alpha:v1:all');
    });

    it('renders a single integer argument verbatim', function (): void {
        expect($this->cache->key('alpha', 'find', [15]))
            ->toBe('alpha:v1:find:15');
    });

    it('renders a list of integers verbatim and keeps its order', function (array $ints): void {
        expect($this->cache->key('alpha', 'findMany', [$ints]))
            ->toBe('alpha:v1:findMany:' . implode(',', $ints));
    })->with([
        [[15, 14, 11]],
        [[11, 14, 15]],
    ]);

    it('keeps a uuid readable', function (): void {
        expect($this->cache->key('alpha', 'find', ['9f8e7d6c-1234-4321-abcd-0123456789ab']))
            ->toBe("alpha:v1:find:'9f8e7d6c-1234-4321-abcd-0123456789ab'");
    });

    it('joins the version of every namespace it depends on', function (): void {
        expect($this->cache->key(['alpha', 'beta'], 'all'))
            ->toBe('alpha:v1|beta:v1:all');
    });

    it('deduplicates repeated namespaces', function (): void {
        expect($this->cache->key(['alpha', 'alpha'], 'all'))
            ->toBe('alpha:v1:all');
    });

    it('rejects an entry that depends on no namespace', function (): void {
        expect(fn () => $this->cache->key([], 'all'))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'at least one namespace'
            );
    });

    it('rejects a namespace that cannot be part of a key', function (string $namespace): void {
        expect(fn () => $this->cache->key($namespace, 'all'))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'non-empty name of printable ASCII'
            );
    })->with([
        'empty' => [''],
        'space' => ['two words'],
        'newline' => ["a\nb"],
        'unicode' => ['zażółć'],
    ]);

    it('keeps scalars that share a string cast apart', function (): void {
        $keys = [
            $this->cache->key('alpha', 'find'),
            $this->cache->key('alpha', 'find', [1]),
            $this->cache->key('alpha', 'find', ['1']),
            $this->cache->key('alpha', 'find', [true]),
            $this->cache->key('alpha', 'find', [false]),
            $this->cache->key('alpha', 'find', [null]),
        ];

        expect(array_unique($keys))
            ->toHaveCount(6);
    });

    it('keeps two nestings of the same values apart', function (): void {
        $key = $this->cache->key('alpha', 'findMany', [[[1, 2], [3]]]);

        expect($key)
            ->toBe('alpha:v1:findMany:[1,2],[3]');

        $key2 = $this->cache->key('alpha', 'findMany', [[[1], [2, 3]]]);

        expect($key2)
            ->toBe('alpha:v1:findMany:[1],[2,3]')
            ->not->toBe($key);
    });

    it('keeps a string containing the separators apart from the values it mimics', function (): void {
        expect($this->cache->key('alpha', 'search', ["a','b"]))
            ->not->toBe($this->cache->key('alpha', 'search', ['a', 'b']));
    });

    it('renders an empty list', function (): void {
        expect($this->cache->key('alpha', 'findMany', [[]]))
            ->toBe('alpha:v1:findMany:[]');
    });

    it('hashes an argument list that would outgrow the readable limit', function (): void {
        $key = $this->cache->key('alpha', 'findMany', [range(1, 300)]);

        expect($key)
            ->toMatch('/^alpha:v1:findMany:[0-9a-f]{40}$/')
            ->and(strlen($key))->toBeLessThan(250);
    });

    it('hashes an argument that is not printable ascii', function (mixed $argument): void {
        expect($this->cache->key('alpha', 'search', [$argument]))
            ->toMatch('/^alpha:v1:search:[0-9a-f]{40}$/');
    })->with([
        'space' => ['two words'],
        'newline' => ["a\nb"],
        'unicode' => ['zażółć'],
    ]);

    it('renders every supported argument type', function (mixed $argument): void {
        expect($this->cache->key('alpha', 'search', [$argument]))
            ->toStartWith('alpha:v1:search:');
    })->with([
        'null' => [null],
        'int' => [15],
        'string' => ['abc'],
        'bool' => [true],
        'list of ints' => [[1, 2, 3]],
        'list of strings' => [['a', 'b']],
        'nested list' => [[[1, 2], ['a']]],
    ]);

    it('refuses an associative array', function (): void {
        expect(fn () => $this->cache->key('alpha', 'search', [['a' => 1, 'b' => 2]]))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'must be lists'
            );
    });

    it('refuses an argument that is not an int, string, bool, null or list', function (mixed $argument): void {
        expect(fn () => $this->cache->key('alpha', 'search', [$argument]))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'must be ints, strings, bools, nulls or lists of them'
            );
    })->with([
        'float' => [1.5],
        'object' => [new stdClass()],
        'date' => [CarbonImmutable::parse('2026-08-03 12:00:00')],
    ]);

    it('refuses a closure argument', function (): void {
        expect(fn () => $this->cache->key('alpha', 'search', [static fn (): int => 1]))
            ->toThrow(
                exception: InvalidArgumentException::class,
                exceptionMessage: 'must be ints, strings, bools, nulls or lists of them'
            );
    });
});
