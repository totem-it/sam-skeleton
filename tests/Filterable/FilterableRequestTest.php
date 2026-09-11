<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Totem\SamSkeleton\Filterable\FilterableRequest;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

it('can get requested fields by type', function (): void {
    $request = new FilterableRequest([
        'fields' => [
            'resource' => 'name,email',
        ],
    ]);

    expect($request->fields())
        ->toEqual([
            'resource' => [
                'name',
                'email',
            ],
        ]);
});

it('can get requested fields without a resource', function (): void {
    $request = new FilterableRequest([
        'fields' => 'name,email',
    ]);

    expect($request->fields())
        ->toEqual([
            '*' => [
                'name',
                'email',
            ],
        ]);
});

it('can sort by requested field', function (): void {
    $request = new FilterableRequest([
        'sort' => 'name',
    ]);

    expect($request->sort())
        ->toEqual([
            'name' => 'asc',
        ]);
});

it('can sort by multiple requested fields', function (): void {
    $request = new FilterableRequest([
        'sort' => 'name,email',
    ]);

    expect($request->sort())
        ->toEqual([
            'name' => 'asc',
            'email' => 'asc',
        ]);
});

it('can sort descending by requested fields', function (): void {
    $request = new FilterableRequest([
        'sort' => '-name',
    ]);

    expect($request->sort())
        ->toEqual([
            'name' => 'desc',
        ]);
});

it('can sort descending by multiple requested fields', function (): void {
    $request = new FilterableRequest([
        'sort' => 'name,-email',
    ]);

    expect($request->sort())
        ->toEqual([
            'name' => 'asc',
            'email' => 'desc',
        ]);
});

describe('cached parsing', function (): void {
    it('parses the fields once', function (): void {
        $request = new FilterableRequest(['fields' => 'name,email']);

        $first = $request->fields();
        $request->merge(['fields' => 'id']);

        expect($request->fields())
            ->toBe($first)
            ->toBe(['*' => ['name', 'email']]);
    });

    it('parses the filters once', function (): void {
        $request = new FilterableRequest(['filter' => ['name' => 'John']]);

        $first = $request->filters();
        $request->merge(['filter' => ['email' => 'test']]);

        expect($request->filters())
            ->toBe($first)
            ->toHaveCount(1)
            ->and($request->filters()[0]->field)->toBe('name');
    });

    it('parses the includes once', function (): void {
        $request = new FilterableRequest(['include' => 'profile,roles']);

        $first = $request->includes();
        $request->merge(['include' => 'other']);

        expect($request->includes())
            ->toBe($first)
            ->toBe(['profile', 'roles']);
    });

    it('parses the sorts once', function (): void {
        $request = new FilterableRequest(['sort' => 'name']);

        $first = $request->sort();
        $request->merge(['sort' => '-email']);

        expect($request->sort())
            ->toBe($first)
            ->toBe(['name' => 'asc']);
    });
});
