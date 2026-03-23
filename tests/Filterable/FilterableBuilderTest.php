<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Filterable\Filterable;
use Totem\SamSkeleton\Filterable\FilterableBuilder;

uses(TestCase::class);

it('can create a base instance from Filterable', function (): void {
    $result = Filterable::create();

    expect($result)
        ->toBeInstanceOf(FilterableBuilder::class)
        ->getRequest()->toBeInstanceOf(Request::class);
});

it('can create a base instance', function (): void {
    $result = FilterableBuilder::create(app('request'));

    expect($result)
        ->toBeInstanceOf(FilterableBuilder::class)
        ->getRequest()->toBeInstanceOf(Request::class);
});
