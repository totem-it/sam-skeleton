<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Bundles\Resource\AdditionalResourceData;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(AdditionalResourceData::class);

beforeEach(function () {
    $this->request = Request::create('/');
    $this->resource = new ApiResource([fake()->word() => fake()->word()]);
});

it('adds the api version to the response', function (): void {
    $with = $this->resource->with($this->request);

    expect($with)->toBe(['apiVersion' => config('app.api')]);
});

it('can includes apiVersion in the response', function () {
    $response = $this->resource->toResponse($this->request);

    expect($response->getData()->apiVersion)->toBe(config('app.api'));
});

it('use api version from app config', function () {
    expect(config('app.api'))->toBe(config('app.api'));

    config(['app.api' => '1.2']);
    $response = $this->resource->with($this->request);

    expect($response)->toBe(['apiVersion' => '1.2']);
});
