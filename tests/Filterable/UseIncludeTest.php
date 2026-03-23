<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidIncludeQuery;
use Totem\SamSkeleton\Filterable\Filterable;
use Totem\SamSkeleton\Filterable\Query\AllowedRelation;

use function Totem\SamSkeleton\Tests\createQueryIncludeRequest;

uses(TestCase::class);

describe('allowed includes', function (): void {
    it('can include a relation', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedIncludes('relatedModel');

        expect($result->getIncludes())
            ->toBeArray()
            ->toHaveCount(1)
            ->toHaveKey('relatedModel');
    });

    it('can include multiple relations', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedIncludes('relatedModel', 'relatedModel-2');

        expect($result->getIncludes())
            ->toBeArray()
            ->toHaveCount(2)
            ->toHaveKeys([
                'relatedModel',
                'relatedModel-2',
            ]);
    });

    it('can include by array of relations', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedIncludes(['relatedModel']);

        expect($result->getIncludes())
            ->toBeArray()
            ->toHaveCount(1)
            ->toHaveKey('relatedModel');
    });

    it('throw an exception for non string relation', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedIncludes(['relatedModel', 3.3121]))
            ->toThrow(
                InvalidFieldValue::class,
                'Non-string field provided.'
            );
    });

    it('throw an exception for incorrect prefixed relation', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedIncludes('.relatedModel'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot start or end with a dot.'
            );
    });

    it('throw an exception for incorrect typed sub relation', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedIncludes('relatedModel..nestedrelatedModel'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot contain consecutive dots.'
            );
    });
});

describe('include relations', function (): void {
    it('can include requested relation', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('relatedModel'))
            ->allowedIncludes('relatedModel')
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(1)
            ->toHaveKeys(array_keys($expected));
    });

    it('can include multiple requested relation', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('relatedModel,relatedModel-2'))
            ->allowedIncludes('relatedModel', 'relatedModel-2')
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel', 'relatedModel-2')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(2)
            ->toHaveKeys(array_keys($expected));
    });

    it('skip including relations if no includes was requested', function (): void {
        $result = Filterable::for(FixtureUser::class, createQueryIncludeRequest())
            ->allowedIncludes('relatedModel')
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->getEagerLoads();

        expect($result)
            ->toBeEmpty()
            ->toEqual($expected);
    });

    it('can include requested relation by alias', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('alias'))
            ->allowedIncludes(AllowedRelation::make('relatedModel', 'alias'))
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(1)
            ->toHaveKeys(array_keys($expected));
    });

    it('can include multiple requested relation by alias', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('alias,alias-2'))
            ->allowedIncludes([
                AllowedRelation::make('relatedModel', 'alias'),
                AllowedRelation::make('relatedModel-2', 'alias-2'),
            ])
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel', 'relatedModel-2')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(2)
            ->toHaveKeys(array_keys($expected));
    });

    it('can include requested nested relation', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('relatedModel.nestedrelatedModel'))
            ->allowedIncludes('relatedModel.nestedrelatedModel')
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel.nestedrelatedModel')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(2)
            ->toHaveKeys(array_keys($expected));
    });

    it('can include requested nested relation by alias', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('alias'))
            ->allowedIncludes(AllowedRelation::make('relatedModel.nestedrelatedModel', 'alias'))
            ->getEagerLoads();

        $expected = FixtureUser::query()
            ->with('relatedModel.nestedrelatedModel')
            ->getEagerLoads();

        expect($result)
            ->toHaveCount(2)
            ->toHaveKeys(array_keys($expected));
    });

    it('throws an exception when requested relation is not an allowed relation', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryIncludeRequest('random-relation'));

        expect(fn () => $query->allowedIncludes('relatedModel'))
            ->toThrow(
                InvalidIncludeQuery::class,
                'Requested includes [random-relation] are not allowed. Allowed includes are [relatedModel].'
            );
    });
});
