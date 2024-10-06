<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;

uses(TestCase::class);

covers(ApiResource::class);

beforeEach(function () {
    $this->request = Request::create('/');
});

it('can get array', function (): void {
    $resource = new ApiResource(['foo' => 'bar']);

    $array = $resource->toArray($this->request);

    expect($array)->toBe(['foo' => 'bar']);
});

it('can get empty array when resource is boolean true', function (): void {
    $resource = new ApiResource(true);

    $array = $resource->toArray($this->request);

    expect($array)->toBe([]);
});

it('can includes apiVersion in the response', function (): void {
    config(['app.api' => '1.0']);
    $resource = new ApiResource(['foo' => 'bar']);

    $with = $resource->with($this->request);

    expect($with)->toBe(['apiVersion' => '1.0']);
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
    $resource = [fake()->word(), fake()->word()];

    $apiCollection = ApiResource::collection($resource);

    expect($apiCollection)
        ->toBeInstanceOf(ApiCollection::class)
        ->toHaveCount(2)
        ->sequence(
            fn ($item) => $item->resource->toBe($resource[0]),
            fn ($item) => $item->resource->toBe($resource[1]),
        )
        ->each()->toBeInstanceOf(ApiResource::class);
});

test('resources may have optional attributes', function (): void {
    class FixtureModel extends Model
    {
    }

    FixtureModel::unguard();

    $post = new FixtureModel([
        'id' => 5,
        'is_published' => true,
    ]);

    $resource = new FixtureApiResource($post);

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
