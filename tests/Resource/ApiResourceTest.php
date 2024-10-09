<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Symfony\Component\HttpFoundation\Response;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(ApiResource::class);

beforeEach(function () {
    $this->request = Request::create('/');
    $this->resource = [fake()->word() => fake()->word()];
});

it('returns the resource as an array', function (): void {
    $resource = new ApiResource($this->resource);

    $array = $resource->toArray($this->request);

    expect($array)->toBe($this->resource);
});

it('returns empty array when resource is boolean true', function (): void {
    $resource = new ApiResource(true);

    $array = $resource->toArray($this->request);

    expect($array)->toBe([]);
});

it('returns an instance of ApiResource with true value', function (): void {
    $response = ApiResource::noContent();

    expect($response)
        ->toBeInstanceOf(ApiResource::class)
        ->resource->toBeTrue();
});

it('returns HTTP no content status', function (): void {
    $resource = ApiResource::noContent();

    $response = $resource->toResponse($this->request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->getStatusCode()->toBe(Response::HTTP_NO_CONTENT);
});

it('returns default collection class', function (): void {
    $resource = fake()->words();

    $apiCollection = ApiResource::collection($resource);

    expect($apiCollection)
        ->toBeInstanceOf(ApiCollection::class)
        ->toHaveCount(3)
        ->each(fn ($item, $index) => $item->toBeInstanceOf(ApiResource::class)->resource->toBe($resource[$index]));
});

test('optional attributes are handled correctly', function (): void {
    $resource = new FixtureApiResource(new FixtureModel());

    expect($resource->toArray($this->request))
        ->sequence(
            fn ($item, $key) => $key->toBe('id')->and($item)->toBe(5),
            fn ($item, $key) => $key->toBe('first')->and($item)->toBeTrue(),
            fn ($item, $key) => $key->toBe('second')->and($item)->toBeTrue(),
            fn ($item, $key) => $key->toBe('third')->and($item)->toBe('override value'),
            fn ($item, $key) => $key->toBe('fourth')->and($item)->toBeTrue(),
            fn ($item, $key) => $key->toBe('fifth')->and($item)->toBeTrue(),
            fn ($item, $key) => $key->toBe('sixth')->and($item)->toBeTrue(),
            fn ($item, $key) => $key->toBe('seventh')->and($item)->toBeInstanceOf(MissingValue::class),
            fn ($item, $key) => $key->toBe('eighth')->and($item)->toBe('default'),
        );
});

it('get response with correct data', function (): void {
    $resource = new ApiResource($this->resource);
    $response = $resource->toResponse($this->request);

    expect($response->getData())
        ->data->toMatchArray($this->resource)
        ->apiVersion->toBe(config('app.api'));
});
