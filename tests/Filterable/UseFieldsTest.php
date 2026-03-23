<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldQuery;
use Totem\SamSkeleton\Filterable\Exceptions\InvalidFieldValue;
use Totem\SamSkeleton\Filterable\Filterable;

use function Totem\SamSkeleton\Tests\createQueryFieldRequest;

uses(TestCase::class);

describe('allowed fields', function (): void {
    it('can selecting field for a query', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFields('id');

        expect($result->getFields())
            ->toBeArray()
            ->toHaveCount(1)
            ->toMatchArray([
                'users.id',
            ]);
    });

    it('can selecting multiple fields for a query', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFields('id', 'email');

        expect($result->getFields())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('can selecting fields as array for a query', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->allowedFields(['id', 'email']);

        expect($result->getFields())
            ->toBeArray()
            ->toHaveCount(2)
            ->toMatchArray([
                'users.id',
                'users.email',
            ]);
    });

    it('throw an exception for non string field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFields(['id', 'email', 3.3121]))
            ->toThrow(
                InvalidFieldValue::class,
                'Non-string field provided.'
            );
    });

    it('throw an exception for incorrect prefixed field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFields('.id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot start or end with a dot.'
            );
    });

    it('throw an exception for incorrect typed resource field', function (): void {
        $result = Filterable::for(FixtureUser::class);

        expect(fn () => $result->allowedFields('resource..id'))
            ->toThrow(
                InvalidFieldValue::class,
                'Field cannot contain consecutive dots.'
            );
    });
});

describe('fetch fields', function (): void {
    it('fetches all columns if no field was requested', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->allowedFields('id', 'name')
            ->toSql();

        $expected = FixtureUser::query()->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('fetches requested columns by resource', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest(['users' => 'email,id']))
            ->allowedFields('email', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select([
                FixtureUser::query()->from . '.email',
                FixtureUser::query()->from . '.id',
            ])
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('fetches requested columns', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('email,id'))
            ->allowedFields('email', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select([
                FixtureUser::query()->from . '.email',
                FixtureUser::query()->from . '.id',
            ])
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('can fetch columns using recommended `JSON:API kebab-case` format', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('first-name,id'), true)
            ->allowedFields('firstname', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select([
                FixtureUser::query()->from . '.firstname',
                FixtureUser::query()->from . '.id',
            ])
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('fetches non-exists columns if they are allowed fields', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('$weird->column[name],id'))
            ->allowedFields('$weird->column[name]', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select([
                FixtureUser::query()->from . '.$weird->column[name]',
                FixtureUser::query()->from . '.id',
            ])
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('fetches non-exists columns from resource if they are allowed fields', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('$weird->column[name],id'))
            ->allowedFields('$weird->column[name]', 'id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select([
                FixtureUser::query()->from . '.$weird->column[name]',
                FixtureUser::query()->from . '.id',
            ])
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('throws an exception when requested field is not an allowed field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('random-column'));

        expect(fn () => $result->allowedFields('name'))
            ->toThrow(
                InvalidFieldQuery::class,
                'Requested fields [users.random-column] are not allowed. Allowed fields are [users.name].'
            );
    });

    it('fetch requested nested fields from resource if they are in allowed dot fields', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest(['relatedModel' => 'id']))
            ->allowedFields('relatedModel.id')
            ->toSql();

        $expected = FixtureUser::query()
            ->select('relatedModel.id')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('fetch requested nested fields from resource if they are in allowed fields', function (): void {
        $query = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest(['relatedModel' => 'id']))
            ->allowedFields(['relatedModel' => 'id'])
            ->toSql();

        $expected = FixtureUser::query()
            ->select('relatedModel.id')
            ->toSql();

        expect($query)
            ->toEqual($expected);
    });

    it('throws an exception when requested related field is not an allowed field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest(['relatedModel' => 'random-column']));

        expect(fn () => $result->allowedFields('relatedModel.name'))
            ->toThrow(
                InvalidFieldQuery::class,
                'Requested fields [relatedModel.random-column] are not allowed. Allowed fields are [relatedModel.name].'
            );
    });

    it('throws an exception when requested dotted field is not an allowed field', function (): void {
        $result = Filterable::for(FixtureUser::class)
            ->withRequest(createQueryFieldRequest('relatedModel.random-column'));

        expect(fn () => $result->allowedFields('relatedModel.name'))
            ->toThrow(
                InvalidFieldQuery::class,
                'Requested fields [relatedModel.random-column] are not allowed. Allowed fields are [relatedModel.name].'
            );
    });
});
