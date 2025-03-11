<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\DataTable;

use Orchestra\Testbench\TestCase;
use Pest\Expectation;
use Totem\SamSkeleton\DataTable\DataTable;
use Totem\SamSkeleton\DataTable\DataTableView;
use Totem\SamSkeleton\DataTable\FilterColumn;
use Totem\SamSkeleton\DataTable\Header;
use UnexpectedValueException;

uses(TestCase::class);

mutates(DataTable::class);

it('can create a base instance', function () {
    $data = DataTable::make();

    expect($data->toArray())
        ->toBeArray()
        ->toMatchArray([
            'defaultView' => null,
            'views' => [],
        ]);
});

it('can set views', function () {
    $views = [DataTableView::make('my_key')];

    $static = DataTable::make($views);

    expect($static->toArray())
        ->toMatchArray([
            'defaultView' => 'my_key',
            'views' => [
                'my_key' => [
                    'name' => 'my_key',
                    'headers' => [],
                    'filters' => [],
                ],
            ],
        ]);

    $fluent = new DataTable();
    $fluent->setViews($views);

    expect($fluent->toArray())
        ->toMatchArray([
            'defaultView' => 'my_key',
            'views' => [
                'my_key' => [
                    'name' => 'my_key',
                    'headers' => [],
                    'filters' => [],
                ],
            ],
        ]);
});

it('can set views from ENUM', function () {
    $data = DataTable::make(FixtureEnum::class);

    expect($data)
        ->defaultView->toBe('H')
        ->views->toHaveKeys([
            'H',
            'D',
            'C',
            'S',
        ])
        ->each(
            fn (DataTableView|Expectation $view) => $view
                ->toBeInstanceOf(DataTableView::class)
                ->headers->toBe([])
                ->filters->toBe([])
        );
});

it('can add extra view', function () {
    $views = [DataTableView::make('first')];

    $data = DataTable::make($views);

    expect($data->views)
        ->toHaveCount(1)
        ->toHaveKeys([
            'first',
        ]);

    $data->addView(DataTableView::make('second'));

    expect($data->views)
        ->toHaveCount(2)
        ->toHaveKeys([
            'first',
            'second',
        ]);
});

it('can set default view', function () {
    $views = [
        DataTableView::make('first'),
        DataTableView::make('second'),
    ];

    $data = DataTable::make();
    $data->setViews($views);
    $data->setDefaultView('second');

    expect($data->toArray())
        ->toMatchArray([
            'defaultView' => 'second',
            'views' => [
                'first' => [
                    'name' => 'first',
                    'headers' => [],
                    'filters' => [],
                ],
                'second' => [
                    'name' => 'second',
                    'headers' => [],
                    'filters' => [],
                ],
            ],
        ]);
});

it('can mutate view', function () {
    $data = DataTable::make();

    $data->addView(DataTableView::make('my_view'));
    $data->view('my_view')
        ->addFilter(new FilterColumn(
            label: 'Filter Label',
            icon: 'custom_icon',
            items: FixtureEnum::class,
        ))
        ->addHeader(new Header(
            value: 'header_value',
            title: 'Header title',
            align: 'end',
            filterable: false,
        ));

    expect($data->views['my_view']->toArray())
        ->toMatchArray([
            'name' => 'my_view',
            'headers' => [
                'header_value' => [
                    'value' => 'header_value',
                    'title' => 'Header title',
                    'align' => 'end',
                    'filterable' => false,
                ],
            ],
            'filters' => [
                'Filter Label' => [
                    'label' => 'Filter Label',
                    'icon' => 'custom_icon',
                    'items' => [
                        ['value' => 'H', 'title' => 'Hearts'],
                        ['value' => 'D', 'title' => 'Diamonds'],
                        ['value' => 'C', 'title' => 'Clubs'],
                        ['value' => 'S', 'title' => 'Spades'],
                    ],
                ],
            ],
        ]);
});

it('throws an exception when mutate not registered view', function () {
    $data = DataTable::make();

    expect(fn () => $data->view('empty'))
        ->toThrow(
            exception: UnexpectedValueException::class,
            exceptionMessage: 'View key [empty] is not registered.'
        );
});
