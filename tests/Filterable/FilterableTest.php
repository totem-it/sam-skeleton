<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterableArgument;
use Totem\SamSkeleton\Filterable\Filterable;
use Totem\SamSkeleton\Filterable\FilterableBuilder;
use Totem\SamSkeleton\Filterable\FilterableRequest;

use function Totem\SamSkeleton\Tests\createQueryFieldRequest;

uses(TestCase::class);

describe('create instance', function (): void {
    it('can create instance for Model', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect($result)
            ->toBeInstanceOf(Filterable::class)
            ->getBuilder()->toBeInstanceOf(Builder::class)
            ->getRequest()->toBeInstanceOf(FilterableRequest::class);
    });

    it('can create instance for query Model', function (): void {
        $result = Filterable::for(FixtureUser::query());

        expect($result)
            ->toBeInstanceOf(Filterable::class)
            ->getBuilder()->toBeInstanceOf(Builder::class)
            ->getRequest()->toBeInstanceOf(FilterableRequest::class);
    });

    it('can create instance for Model with custom request', function (): void {
        $result = Filterable::for(FixtureUser::class, resolve(FormRequest::class));

        expect($result->getRequest())
            ->not->toBeInstanceOf(FormRequest::class)
            ->toBeInstanceOf(FilterableRequest::class);
    });

    it('can create a builder instance', function (): void {
        $result = Filterable::create();

        expect($result)
            ->toBeInstanceOf(FilterableBuilder::class)
            ->getRequest()->toBeInstanceOf(FilterableRequest::class);
    });

    it('throws an exception when incorrect model string passed', function (): void {
        expect(fn () => Filterable::for('no-model-class'))
            ->toThrow(
                InvalidFilterableArgument::class,
                'Expected Eloquent model class-string or Builder.'
            );
    });
});

describe('fetch fields', function (): void {
    it('fetch all fields when no field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('ignores requested columns and fetches all columns if no fields was allowed', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest(['resource' => 'random-column']))
            ->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('ignores requested column and fetches all columns if no fields was allowed', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('random-column'))
            ->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('sort fields', function (): void {
    it('sort by default column', function (): void {
        $query = Filterable::for(FixtureUser::class)->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });
});
