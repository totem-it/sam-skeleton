<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use ArgumentCountError;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterOperator;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterQuery;
use Totem\SamSkeleton\Filterable\Filterable;

use function Totem\SamSkeleton\Tests\createQueryFilterRequest;

uses(TestCase::class);

describe('allowed filter', function (): void {
    it('can filter by field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFilters('id');

        expect($result->getFilters())
            ->toBeArray()
            ->toHaveCount(1)
            ->toMatchArray([
                'users.id',
            ]);
    });

    it('can filter by multiple fields', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFilters('id', 'email');

        expect($result->getFilters())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('can filter a query by array of fields', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFilters(['id', 'email']);

        expect($result->getFilters())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('throw an exception for non string filter field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFilters(['id', 'email', 3.3123]))
            ->toThrow(
                InvalidFieldValue::class,
                'Non-string field provided.'
            );
    });

    it('throw an exception for incorrect prefixed filter field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFilters('.id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot start or end with a dot.'
            );
    });

    it('throw an exception for incorrect typed resource filter field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFilters('resource..id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot contain consecutive dots.'
            );
    });
});

describe('filter operators', function (): void {
    it('can filter by default operator - EQUAL', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => 'John']))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.firstname', '=', 'John')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by multiple values', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => 'John,Adam']))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereIn('users.firstname', ['John', 'Adam'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by EQUAL operator - EQ', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['eq' => 'John']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.firstname', '=', 'John')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_EQUAL operator - NEQ', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['neq' => 'John']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.firstname', '<>', 'John')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_EQUAL operator - -EQ', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['-eq' => 'John']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.firstname', '<>', 'John')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by LESS_THAN operator - LT', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['lt' => 8]]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.id', '<', '8')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by GREATER_THAN operator - GT', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['gt' => 8]]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.id', '>', '8')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by LESS_THAN_OR_EQUAL operator - LTE', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['lte' => 8]]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.id', '<=', '8')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by GREATER_THAN_OR_EQUAL operator - GTE', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['gte' => 8]]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->where('users.id', '>=', '8')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('filter LIKE', function (): void {
    it('can filter by LIKE operator: LIKE', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['like' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', '%jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by LIKE operator - start wildcard', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['like' => '%jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', '%jo')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by LIKE operator - end wildcard', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['like' => 'jo%']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', 'jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias LIKE operator: CONTAINS', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['contains' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', '%jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias LIKE operator: START', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['start' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', 'jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias LIKE operator: END', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['end' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereLike('users.firstname', '%jo')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_LIKE operator: NLIKE', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['nlike' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', '%jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_LIKE operator - start wildcard', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['nlike' => '%jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', '%jo')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_LIKE operator - end wildcard', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['nlike' => 'jo%']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', 'jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_LIKE operator: -LIKE', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['-like' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', '%jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_LIKE operator: NSTART', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['nstart' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', 'jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_LIKE operator: -START', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['-start' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', 'jo%')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_LIKE operator: NEND', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['nend' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', '%jo')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_LIKE operator: -END', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['-end' => 'jo']]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotLike('users.firstname', '%jo')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('filter NULL', function (): void {
    it('can filter by IS_NULL operator: NULL->true', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['null' => true]]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNull('users.firstname')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by IS_NULL operator: NULL', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => 'null']))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNull('users.firstname')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by IS_NULL operator: `null`', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => null]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNull('users.firstname')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_NULL operator: NULL->false', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['firstname' => ['null' => false]]))
            ->allowedFilters('firstname')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotNull('users.firstname')
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('filter IN', function (): void {
    it('can filter by IN operator: IN', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['in' => '1,2,3,4']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereIn('users.id', ['1', '2', '3', '4'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by IN operator: multiple values', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => '1,2,3,4']))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereIn('users.id', ['1', '2', '3', '4'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_IN operator: NIN', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['nin' => '1,2,3,4']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotIn('users.id', ['1', '2', '3', '4'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by NOT_IN operator: -IN', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['-in' => '1,2,3,4']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotIn('users.id', ['1', '2', '3', '4'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('filter BETWEEN', function (): void {
    it('can filter by BETWEEN operator: BT', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['bt' => '1,10']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereBetween('users.id', ['1', '10'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias BETWEEN operator: BETWEEN', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['between' => '1,10']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereBetween('users.id', ['1', '10'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter dates by BETWEEN operator', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['created_at' => ['between' => '2020-01-20,2020-12-31']]))
            ->allowedFilters('created_at')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereBetween('users.created_at', ['2020-01-20 00:00:00', '2020-12-31 23:59:59'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('throws an exception when to low arguments for BETWEEN operator', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['bt' => '1']]))
            ->allowedFilters('id');

        expect($result->toSql(...))
            ->toThrow(
                ArgumentCountError::class,
                'Missing values to filter for field [users.id].'
            );
    });

    it('can filter by NOT_BETWEEN operator: NBT', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['nbt' => '1,10']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotBetween('users.id', ['1', '10'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_BETWEEN operator: -BT', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['-bt' => '1,10']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotBetween('users.id', ['1', '10'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter by alias NOT_BETWEEN operator: -BETWEEN', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['id' => ['-between' => '1,10']]))
            ->allowedFilters('id')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotBetween('users.id', ['1', '10'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can filter dates by NOT_BETWEEN operator', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['created_at' => ['-bt' => '2020-01-20,2020-12-31']]))
            ->allowedFilters('created_at')
            ->toRawSql();

        $expected = FixtureUser::query()
            ->whereNotBetween('users.created_at', ['2020-01-20 00:00:00', '2020-12-31 23:59:59'])
            ->toRawSql();

        expect($query)
            ->toEqual($expected);
    });
});

describe('filter fields', function (): void {
    it('throws an exception when requested operator is not recognized', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['name' => ['random' => 'John']]));

        expect(fn () => $result->allowedFilters('name'))
            ->toThrow(InvalidFilterOperator::class);
    });

    it('throws an exception when requested filter is not an allowed filter', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['random-column' => 'test']));

        expect(fn () => $result->allowedFilters('name'))
            ->toThrow(
                InvalidFilterQuery::class,
                'Requested filters [users.random-column] are not allowed. Allowed filters are [users.name].'
            );
    });

    it('filter a query for non-exists columns if they are allowed filter', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFilterRequest(['$weird->column[name]' => 'test']))
            ->allowedFilters('$weird->column[name]')
            ->toSql();

        $expected = FixtureUser::query()
            ->where(FixtureUser::query()->from . '.$weird->column[name]', '=', 'test')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });
});
