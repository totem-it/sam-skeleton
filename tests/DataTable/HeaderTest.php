<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\DataTable;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\DataTable\Header;

uses(TestCase::class);

mutates(Header::class);

it('can create Header', function (): void {
    $header = new Header(
        value: 'task_printed',
        title: 'Printed sets',
    );

    expect($header->toArray())
        ->toBeArray()
        ->toMatchArray([
            'value' => 'task_printed',
            'title' => 'Printed sets',
        ]);
});

it('can create full Header', function (): void {
    $header = new Header(
        value: 'task_printed',
        title: 'Printed sets',
        sortable: false,
        customFilter: 'job',
    );

    expect($header->toArray())
        ->toBeArray()
        ->toMatchArray([
            'value' => 'task_printed',
            'title' => 'Printed sets',
            'sortable' => false,
            'customFilter' => 'job',
        ]);
});

it('can set sortable to true', function (): void {
    $header = new Header(
        value: 'name',
        title: 'Name',
        sortable: true,
    );

    expect($header->toArray())
        ->toHaveKey('sortable', true);
});

it('preserves all properties in toArray', function (): void {
    $header = new Header(
        value: 'created_at',
        title: 'Created At',
        sortable: true,
        customFilter: 'date_filter',
    );

    expect($header->toArray())
        ->toMatchArray([
            'value' => 'created_at',
            'title' => 'Created At',
            'sortable' => true,
            'customFilter' => 'date_filter',
        ]);
});

it('returns non null values from toArray method', function (): void {
    $header = new Header(
        value: 'test',
        title: 'Test',
    );

    expect($header->toArray())
        ->toMatchArray([
            'value' => 'test',
            'title' => 'Test',
        ]);
});

describe('disableSort()', function (): void {
    it('can disable sort', function (): void {
        $header = Header::make('Actions', 'actions')->disableSort();

        expect($header->toArray())
            ->toHaveKey('sortable', false);
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Name', 'name');

        expect($header->disableSort())
            ->toBe($header);
    });
});

describe('disableFilter()', function (): void {
    it('can disable filtering', function (): void {
        $header = Header::make('Actions', 'actions')->disableFilter();

        expect($header->toArray())
            ->toHaveKey('filterable', false);
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Name', 'name');

        expect($header->disableFilter())
            ->toBe($header);
    });
});

describe('align()', function (): void {
    it('can set alignment', function (): void {
        $header = Header::make('Actions', 'actions')->align('right');

        expect($header->toArray())
            ->toHaveKey('align', 'right');
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Name', 'name');

        expect($header->align('end'))
            ->toBe($header);
    });
});

describe('description()', function (): void {
    it('can set description', function (): void {
        $header = Header::make('Status', 'status')->description('Current status of the item');

        expect($header->toArray())
            ->toHaveKey('description', 'Current status of the item');
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Name', 'name');

        expect($header->description(''))
            ->toBe($header);
    });
});

describe('filterBy()', function (): void {
    it('can set custom filter', function (): void {
        $header = Header::make('Category', 'category')->filterBy('category_filter');

        expect($header->toArray())
            ->toHaveKey('customFilter', 'category_filter');
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Name', 'name');

        expect($header->filterBy('custom'))
            ->toBe($header);
    });
});

describe('hide()', function (): void {
    it('can set hide with boolean', function (): void {
        $header = Header::make('Internal', 'internal')->hide(true);

        expect($header->toArray())
            ->toHaveKey('hide', true);
    });

    it('can set hide with string', function (): void {
        $header = Header::make('Internal', 'internal')->hide('sm');

        expect($header->toArray())
            ->toHaveKey('hide', 'sm');
    });

    it('returns self for chaining', function (): void {
        $header = Header::make('Field', 'field');

        expect($header->hide(''))
            ->toBe($header);
    });
});

it('can chain multiple methods', function (): void {
    $header = Header::make('Price', 'price')
        ->align('right')
        ->disableSort()
        ->disableFilter()
        ->description('Product price in USD')
        ->filterBy('price_range')
        ->hide('md');

    expect($header->toArray())
        ->toMatchArray([
            'value' => 'price',
            'title' => 'Price',
            'align' => 'right',
            'sortable' => false,
            'filterable' => false,
            'description' => 'Product price in USD',
            'customFilter' => 'price_range',
            'hide' => 'md',
        ]);
});
