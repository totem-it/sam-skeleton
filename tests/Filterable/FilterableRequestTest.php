<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\FilterableRequest;

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
