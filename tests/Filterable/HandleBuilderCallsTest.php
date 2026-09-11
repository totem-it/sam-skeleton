<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Totem\SamSkeleton\Filterable\Concerns\HandleBuilderCalls;
use Totem\SamSkeleton\Filterable\Filterable;
use Totem\SamSkeleton\Tests\TestCase;

use function Totem\SamSkeleton\Tests\createQueryIncludeRequest;
use function Totem\SamSkeleton\Tests\createQuerySortRequest;

uses(TestCase::class);

covers(HandleBuilderCalls::class);

beforeEach(function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('firstname');
        $table->string('lastname');
        $table->string('email');
    });

    DB::table('users')->insert([
        ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john.doe@test.com'],
        ['firstname' => 'Jane', 'lastname' => 'Roe', 'email' => 'jane.roe@test.com'],
    ]);
});

describe('get', function (): void {
    it('returns all models when nothing was requested', function (): void {
        $result = Filterable::for(FixtureUser::class)->get();

        expect($result)
            ->toBeInstanceOf(Collection::class)
            ->toHaveCount(2)
            ->first()->toBeInstanceOf(FixtureUser::class);
    });

    it('applies the pipeline before fetching', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('-firstname'))
            ->allowedSorts('firstname')
            ->get();

        expect($result->pluck('firstname')->all())
            ->toBe(['John', 'Jane']);
    });

    it('forwards the requested columns', function (): void {
        $result = Filterable::for(FixtureUser::class)->get(['email']);

        expect($result->first()->getAttributes())
            ->toBe(['email' => 'john.doe@test.com']);
    });
});

describe('__call', function (): void {
    it('returns itself when the builder returns the builder', function (): void {
        $filterable = Filterable::for(FixtureUser::class);

        expect($filterable->where('id', 1))
            ->toBe($filterable);
    });

    it('returns the forwarded value when the builder returns something else', function (): void {
        expect(Filterable::for(FixtureUser::class))
            ->getQuery()->toBeInstanceOf(QueryBuilder::class)
            ->count()->toBe(2)
            ->exists()->toBeTrue();
    });

    it('applies the pipeline before forwarding', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('-firstname'))
            ->allowedSorts('firstname')
            ->pluck('firstname');

        expect($result->all())
            ->toBe(['John', 'Jane']);
    });

    it('throws for an unknown method', function (): void {
        expect(fn () => Filterable::for(FixtureUser::class)->noSuchMethod())
            ->toThrow(BadMethodCallException::class);
    });
});

describe('other forwarded calls', function (): void {
    it('returns the eager loads', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('relatedModel'))
            ->allowedIncludes('relatedModel')
            ->getEagerLoads();

        expect($result)
            ->toHaveKey('relatedModel');
    });

    it('returns the raw sql', function (): void {
        expect(Filterable::for(FixtureUser::class)->toRawSql())
            ->toBe(FixtureUser::query()->toRawSql());
    });
});
