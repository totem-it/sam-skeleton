<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidSortQuery;
use Totem\SamSkeleton\Filterable\Filterable;

use function Totem\SamSkeleton\Tests\createQuerySortRequest;

uses(TestCase::class);

describe('allowed sorts', function (): void {
    it('can sort by field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedSorts('id');

        expect($result->getSorts())
            ->toBeArray()
            ->toHaveCount(1)
            ->toMatchArray([
                'users.id',
            ]);
    });

    it('can sort by multiple fields', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedSorts('id', 'email');

        expect($result->getSorts())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('can sort a query by array of fields', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedSorts(['id', 'email']);

        expect($result->getSorts())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('throw an exception for non string sort field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedSorts(['id', 'email', 3.3131]))
            ->toThrow(
                InvalidFieldValue::class,
                'Non-string field provided.'
            );
    });

    it('throw an exception for incorrect prefixed sort field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedSorts('.id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot start or end with a dot.'
            );
    });

    it('throw an exception for incorrect typed resource sort field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedSorts('resource..id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot contain consecutive dots.'
            );
    });
});

describe('sort fields', function (): void {
    it('can sort a query ascending', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('id'))
            ->allowedSorts(['id', 'email'])
            ->toSql();

        $expected = FixtureUser::query()
            ->orderBy('users.id')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can sort a query descending', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('-id'))
            ->allowedSorts(['id', 'email'])
            ->toSql();

        $expected = FixtureUser::query()
            ->orderByDesc('users.id')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('skip sort a query if no sort field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->allowedSorts('id', 'name')
            ->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('throws an exception when requested sort name is not an allowed sort field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('-random-column'));

        expect(fn () => $result->allowedSorts('name'))
            ->toThrow(
                InvalidSortQuery::class,
                'Requested sorts [users.random-column] are not allowed. Allowed sorts are [users.name].'
            );
    });

    it('sort a query for non-exists columns if they are allowed sort fields', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('-$weird->column[name],id'))
            ->allowedSorts('$weird->column[name]', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->orderByDesc(FixtureUser::query()->from . '.$weird->column[name]')
            ->orderBy(FixtureUser::query()->from . '.id')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can use default sort if no sort field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->defaultSort('-id', 'name')
            ->toSql();

        $expected = FixtureUser::query()
            ->orderByDesc('users.id')
            ->orderBy('users.name')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can use default array of sort fields if no sort field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->defaultSort(['-id', 'name'])
            ->toSql();

        $expected = FixtureUser::query()
            ->orderByDesc('users.id')
            ->orderBy('users.name')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('skip default sort if sort field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('email'))
            ->defaultSort('id')
            ->allowedSorts(['name', 'email'])
            ->toSql();

        $expected = FixtureUser::query()
            ->orderBy('users.email')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('throws an exception when requested sort name is not an allowed sort field and ignoring default sort', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQuerySortRequest('email'))
            ->defaultSort('id');

        expect(fn () => $query->allowedSorts('name'))
            ->toThrow(
                InvalidSortQuery::class,
                'Requested sorts [users.email] are not allowed. Allowed sorts are [users.name].'
            );
    });
});
