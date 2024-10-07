<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(ApiCollection::class);

beforeEach(function () {
    $this->resource = [fake()->words()];
    $this->request = Request::create('/');
});

it('sets the collects property if provided', function (): void {
    $collection = new ApiCollection($this->resource, ApiResource::class);

    expect($collection)
        ->toBeInstanceOf(ApiCollection::class)
        ->collects->toBe(ApiResource::class)
        ->collection->each(
            fn ($item, $index) => $item
                ->toBeInstanceOf(ApiResource::class)
                ->resource->toBe($this->resource[$index])
        );
});

it('can get null when collects is missing', function (): void {
    $collection = new ApiCollection($this->resource);

    expect($collection->collects)->toBeNull();
});

it('can get null when collects is null', function (): void {
    $collection = new ApiCollection($this->resource, null);

    expect($collection)->collects->toBeNull();
});

it('can get null when resource is empty array & collects is defined', function (): void {
    $collection = new ApiCollection([], ApiResource::class);

    expect($collection)
        ->collects->toBe(ApiResource::class)
        ->collection->toBeEmpty();
});

it('get response with correct data', function (): void {
    $collection = new ApiCollection($this->resource, ApiResource::class);

    $response = $collection->toResponse($this->request);

    expect($response->getData())
        ->data->toMatchArray($this->resource)
        ->apiVersion->toBe(config('app.api'));
});
