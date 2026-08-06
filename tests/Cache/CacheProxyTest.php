<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Cache;

use ArgumentCountError;
use BadMethodCallException;
use LogicException;
use Totem\SamSkeleton\Cache\CacheProfile;
use Totem\SamSkeleton\Cache\CacheProxy;
use Totem\SamSkeleton\Tests\Cache\Twin\FixtureProxiedRepository as TwinRepository;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

mutates(CacheProxy::class);

beforeEach(function (): void {
    $this->repository = new FixtureProxiedRepository();
});

it('executes the query once and serves subsequent calls from cache', function (): void {
    $first = $this->repository->cached()->all();

    expect($first)
        ->toBe('all')
        ->and($this->repository->calls)->toBe(1);

    $second = $this->repository->cached()->all();

    expect($second)
        ->toBe('all')
        ->and($this->repository->calls)->toBe(1);
});

it('caches separately per argument set', function (): void {
    $this->repository->cached()->find(1);
    $this->repository->cached()->find(2);

    expect($this->repository->calls)
        ->toBe(2);
});

it('caches separately per method', function (): void {
    $this->repository->cached()->all();
    $this->repository->cached()->find(1);

    expect($this->repository->calls)
        ->toBe(2);
});

it('gives named and positional arguments the same key', function (): void {
    $first = $this->repository->cached()->find(1, true);

    expect($first)
        ->toBe('find:1:1')
        ->and($this->repository->calls)->toBe(1);

    $second = $this->repository->cached()->find(1, withPrices: true);

    expect($second)
        ->toBe('find:1:1')
        ->and($this->repository->calls)->toBe(1);
});

it('gives an omitted default and an explicit default the same key', function (): void {
    $this->repository->cached()->find(1);

    /** @noinspection PhpRedundantOptionalArgumentInspection */
    $this->repository->cached()->find(1, false);

    expect($this->repository->calls)
        ->toBe(1);
});

describe('variadic tail', function (): void {
    it('keeps the tail in the key', function (): void {
        $first = $this->repository->cached()->search('a', false, 1, 2);

        expect($first)
            ->toBe('search:a:0:1-2')
            ->and($this->repository->calls)->toBe(1);

        $second = $this->repository->cached()->search('a', false, 3);

        expect($second)
            ->toBe('search:a:0:3')
            ->and($this->repository->calls)->toBe(2);
    });

    it('serves a repeated tail from cache', function (): void {
        $this->repository->cached()->search('a', false, 1, 2);
        $this->repository->cached()->search('a', false, 1, 2);

        expect($this->repository->calls)
            ->toBe(1);
    });

    it('keeps two tails of the same values in a different order apart', function (): void {
        $this->repository->cached()->search('a', false, 1, 2);
        $this->repository->cached()->search('a', false, 2, 1);

        expect($this->repository->calls)
            ->toBe(2);
    });

    it('separates an empty tail from a tail that has values', function (): void {
        $this->repository->cached()->search('a');
        $this->repository->cached()->search('a', false, 1);

        expect($this->repository->calls)
            ->toBe(2);
    });

    it('gives an omitted default in front of the tail and an explicit one the same key', function (): void {
        $this->repository->cached()->search('a');

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $this->repository->cached()->search('a', false);

        expect($this->repository->calls)
            ->toBe(1);
    });
});

it('separates two classes sharing a basename, a domain and a method name', function (): void {
    $twin = new TwinRepository();

    expect($this->repository->cached()->all())
        ->toBe('all');

    $twinCall = $twin->cached()->all();

    expect($twinCall)
        ->toBe('twin');

    $calls = [$this->repository->calls, $twin->calls];

    expect($calls)
        ->toMatchArray([1, 1]);
});

it('refuses a method that is not marked as a cached query', function (): void {
    expect(fn () => $this->repository->cached()->write(['a' => 1]))
        ->toThrow(
            exception: LogicException::class,
            exceptionMessage: 'is not marked with #[CachedQuery]',
        );
});

it('refuses a method that returns void', function (): void {
    expect(fn () => $this->repository->cached()->touch())
        ->toThrow(
            exception: LogicException::class,
            exceptionMessage: 'cannot be cached',
        );
});

it('keys a call missing a required argument off what it was given', function (): void {
    expect(function (): void {
        /** @noinspection PhpParamsInspection */
        $this->repository->cached()->find(withPrices: true);
    })->toThrow(ArgumentCountError::class);
});

it('refuses a method the repository does not have', function (): void {
    expect(function (): void {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->repository->cached()->missing();
    })->toThrow(BadMethodCallException::class);
});

it('recomputes after the repository invalidates its domain', function (): void {
    $this->repository->cached()->all();
    $this->repository->invalidateCache();
    $this->repository->cached()->all();

    expect($this->repository->calls)
        ->toBe(2);
});

describe('missing namespace', function (): void {
    it('refuses to read a repository that declares no cache namespace', function (): void {
        expect(fn () => (new FixtureNamelessRepository())->cached()->all())
            ->toThrow(
                exception: LogicException::class,
                exceptionMessage: 'must declare a non-empty public [cacheNamespace()] method',
            );
    });

    it('refuses to invalidate a repository that declares no cache namespace', function (): void {
        expect(fn () => (new FixtureNamelessRepository())->invalidateCache())
            ->toThrow(
                exception: LogicException::class,
                exceptionMessage: 'must declare a non-empty public [cacheNamespace()] method',
            );
    });
});

describe('cache-profiles', function (): void {
    it('takes the profile declared on the method', function (): void {
        $this->repository->cached()->find(1);

        $this->travel(6)->minutes();

        $this->repository->cached()->find(1);

        expect($this->repository->calls)
            ->toBe(1);
    });

    it('falls back to SHORT when the method declares no profile', function (): void {
        $this->repository->cached()->all();

        $this->travel(6)->minutes();

        $this->repository->cached()->all();

        expect($this->repository->calls)
            ->toBe(2);
    });

    it('lets the call site override the declared profile', function (): void {
        $this->repository->cached(CacheProfile::SHORT)->find(1);

        $this->travel(6)->minutes();

        $this->repository->cached(CacheProfile::SHORT)->find(1);

        expect($this->repository->calls)
            ->toBe(2);
    });
});
