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

it('can get array', function (): void {
    $resource = new ApiResource($this->resource);

    $array = $resource->toArray($this->request);

    expect($array)->toBe($this->resource);
});

it('can get empty array when resource is boolean true', function (): void {
    $resource = new ApiResource(true);

    $array = $resource->toArray($this->request);

    expect($array)->toBe([]);
});

it('returns an instance of ApiResource with true value', function (): void {
    $response = ApiResource::noContent();

    expect($response)->toBeInstanceOf(ApiResource::class)
        ->resource->toBeTrue();
});

it('can get HTTP no content status', function (): void {
    $resource = ApiResource::noContent();

    $response = $resource->toResponse($this->request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->getStatusCode()->toBe(Response::HTTP_NO_CONTENT);
});

it('can get no content status code when resource is boolean', function ($booleanValue): void {
    $resource = new ApiResource($booleanValue);
    $response = new JsonResponse();

    $resource->withResponse($this->request, $response);

    expect($response->getStatusCode())->toBe(Response::HTTP_NO_CONTENT);
})->with([
    'true' => [fn () => true],
    'false' => [fn () => false],
]);

it('can create Collection via collection() method', function (): void {
    $resource = fake()->words();

    $apiCollection = ApiResource::collection($resource);

    expect($apiCollection)
        ->toBeInstanceOf(ApiCollection::class)
        ->toHaveCount(3)
        ->each(function ($item, $index) use ($resource) {
            $item->toBeInstanceOf(ApiResource::class)->resource->toBe($resource[$index]);
        });
});

test('resources may have optional attributes', function (): void {
    $resource = new FixtureApiResource(new FixtureModel());

    expect($resource->toArray($this->request))
        ->toHaveKeys(['id', 'first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth'])
        ->sequence(
            fn ($item) => $item->toBe(5),
            fn ($item) => $item->toBeTrue(),
            fn ($item) => $item->toBeTrue(),
            fn ($item) => $item->toBe('override value'),
            fn ($item) => $item->toBeTrue(),
            fn ($item) => $item->toBeTrue(),
            fn ($item) => $item->toBeTrue(),
            fn ($item) => $item->toBeInstanceOf(MissingValue::class),
            fn ($item) => $item->toBe('default'),
        );
});

it('get response with correct data', function (): void {
    $resource = new ApiResource($this->resource);
    $response = $resource->toResponse($this->request);

    expect($response->getData())
        ->data->toMatchArray($this->resource)
        ->apiVersion->toBe(config('app.api'));
});
