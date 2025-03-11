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
