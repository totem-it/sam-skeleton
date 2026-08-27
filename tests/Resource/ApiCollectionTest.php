<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureChunkedCollection;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureModel;
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

test('collect property returns null when collects is null', function (): void {
    $collection = new ApiCollection($this->resource, null);

    expect($collection->collects)->toBeNull();
});

it('returns an empty collection when resource is empty array', function (): void {
    $collection = new ApiCollection([], ApiResource::class);

    expect($collection)
        ->collects->toBe(ApiResource::class)
        ->collection->toBeEmpty();
});

it('returns response with correct data', function (): void {
    $collection = new ApiCollection($this->resource, ApiResource::class);

    $response = $collection->toResponse($this->request);

    expect($response->getData())
        ->data->toMatchArray($this->resource)
        ->apiVersion->toBe(config('app.api'));
});

it('is not chunked by default', function (): void {
    config(['app.api' => '1.2']);

    $collection = new ApiCollection([['a' => 'b']], ApiResource::class);

    expect($collection->toResponse($this->request)->getContent())
        ->toBe('{"data":[{"a":"b"}],"apiVersion":"1.2"}');
});

test('chunked returns the same instance', function (): void {
    $collection = new ApiCollection($this->resource, ApiResource::class);

    expect($collection->chunked())
        ->toBe($collection);
});

it('keeps subclass overrides when chunked', function (): void {
    $collection = (new FixtureChunkedCollection([new FixtureModel()]))->chunked();

    expect($collection->toResponse($this->request)->getData(true))
        ->not->toHaveKey('apiVersion')
        ->toHaveKey('data');
});
