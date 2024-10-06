<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;

uses(TestCase::class);

covers(ApiCollection::class);

beforeEach(function () {
    $this->payload = [];

    for ($i = 0; $i < 3; $i++) {
        $this->payload[] = fake()->word();
    }
});

it('sets the collects property if provided', function (): void {
    $collects = ApiResource::class;

    $collection = new ApiCollection($this->payload, $collects);

    expect($collection)
        ->toBeInstanceOf(ApiCollection::class)
        ->collects->toBe($collects)
        ->collection->toBeInstanceOf(Collection::class)
        ->each->toBeInstanceOf(ApiResource::class)
        ->collection->sequence(
            fn ($item) => $item
                ->resource->toBe($this->payload[0]),
            fn ($item) => $item
                ->resource->toBe($this->payload[1]),
            fn ($item) => $item
                ->resource->toBe($this->payload[2]),
        );
});

it('can get null when collects is missing', function (): void {
    $collection = new ApiCollection($this->payload);

    expect($collection)->collects->toBeNull();
});

it('can get null when collects is null', function (): void {
    $collection = new ApiCollection($this->payload, null);

    expect($collection)->collects->toBeNull();
});

it('can get null when resource is empty array', function (): void {
    $collection = new ApiCollection([]);

    expect($collection)
        ->collects->toBeNull()
        ->collection->first()->toBeNull();
});

it('can get null when resource is empty array & collects is defined', function (): void {
    $collection = new ApiCollection([], ApiResource::class);

    expect($collection)
        ->collects->toBe(ApiResource::class)
        ->collection->first()->toBeNull();
});

it('adds the api version to the response', function (): void {
    $collection = new ApiCollection($this->payload);

    config(['app.api' => '1.0']);
    $response = $collection->with(new Request());

    expect($response)->toBe([
        'apiVersion' => '1.0',
    ]);
});
