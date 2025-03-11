<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\DataTable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\DataTable\DataTableView;
use Totem\SamSkeleton\DataTable\FilterColumn;
use Totem\SamSkeleton\DataTable\Header;

uses(TestCase::class);

mutates(DataTableView::class);

it('can create a base instance', function () {
    $data = DataTableView::make('custom_view');

    expect($data->toArray())
        ->toBeArray()
        ->toMatchArray([
            'name' => 'custom_view',
            'headers' => [],
            'filters' => [],
        ]);
});

it('can add header', function () {
    $data = DataTableView::make('custom_view');

    $data->addHeader(new Header('values', 'text'));

    expect($data->headers)
        ->toHaveCount(1)
        ->toHaveKey('values');
});

it('can add multiple headers', function () {
    $data = DataTableView::make('custom_view');

    $data->addHeaders([
        new Header('first', 'text_1'),
        new Header('second', 'text_2'),
    ]);

    expect($data->headers)
        ->toHaveCount(2)
        ->toHaveKeys(['first', 'second']);
});

it('can add filter', function () {
    $data = DataTableView::make('custom_view');

    $data->addFilter(new FilterColumn('title'));

    expect($data->filters)
        ->toHaveCount(1)
        ->toHaveKey('title');
});

it('can add multiple filters', function () {
    $data = DataTableView::make('custom_view');

    $data->addFilters([
        new FilterColumn('first'),
        new FilterColumn('second'),
    ]);

    expect($data->filters)
        ->toHaveCount(2)
        ->toHaveKeys(['first', 'second']);
});
