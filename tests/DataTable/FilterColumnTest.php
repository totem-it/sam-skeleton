<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\DataTable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\DataTable\FilterColumn;

uses(TestCase::class);

mutates(FilterColumn::class);

it('can create Column Filter', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => [],
        ]);
});

it('can create Column Filter from ENUM', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
        items: FixtureEnum::class,
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => [
                [
                    'title' => 'Hearts',
                    'value' => 'H',
                ],
                [
                    'title' => 'Diamonds',
                    'value' => 'D',
                ],
                [
                    'title' => 'Clubs',
                    'value' => 'C',
                ],
                [
                    'title' => 'Spades',
                    'value' => 'S',
                ],
            ],
        ]);
});

it('can create Column Filter from string', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
        items: 'abc',
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => ['abc'],
        ]);
});

it('can create Column Filter from indexed array', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
        items: [1, 2, 3],
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => [
                [
                    'title' => 1,
                    'value' => 1,
                ],
                [
                    'title' => 2,
                    'value' => 2,
                ],
                [
                    'title' => 3,
                    'value' => 3,
                ],
            ],
        ]);
});

it('can create Column Filter from nested array', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
        items: [
            ['value' => 58, 'title' => 'End'],
            ['value' => 10, 'title' => 'First'],
        ],
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => [
                [
                    'title' => 'End',
                    'value' => 58,
                ],
                [
                    'title' => 'First',
                    'value' => 10,
                ],
            ],
        ]);
});

it('can create Column Filter from assoc array', function (): void {
    $filterColumn = new FilterColumn(
        label: 'Job type',
        items: [
            '_58' => 'End',
            '_10' => 'First',
        ],
    );

    expect($filterColumn->toArray())
        ->toBeArray()
        ->toMatchArray([
            'label' => 'Job type',
            'icon' => null,
            'items' => [
                [
                    'title' => 'End',
                    'value' => '_58',
                ],
                [
                    'title' => 'First',
                    'value' => '_10',
                ],
            ],
        ]);
});
