<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;
use Totem\SamSkeleton\Bundles\Resource\ApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;
use Totem\SamSkeleton\Bundles\Resource\ChunkedApiCollection;
use Totem\SamSkeleton\Bundles\Resource\ChunksResponse;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureApiResource;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureChunkedCollection;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureEagerCollection;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureModel;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureOptionsCollection;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixturePrettyApiResource;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureProbeCollection;
use Totem\SamSkeleton\Tests\Resource\Fixtures\FixtureRespondingCollection;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(ChunkedApiCollection::class);

mutates(ChunksResponse::class);

beforeEach(function () {
    $this->request = Request::create('/');

    config(['app.api' => '1.2']);
});

dataset('payloads', [
    'empty collection' => [[], ApiResource::class],
    'list of arrays' => [[['a' => 'b'], ['a' => 'c']], ApiResource::class],
    'nullable collects' => [[['a' => 'b']], null],
    'models with optional attributes' => [[new FixtureModel(), new FixtureModel()], FixtureApiResource::class],
    'special characters' => [[['a' => '<script>"/\\ ąćę 🙂']], ApiResource::class],
    'thousand items' => [[...array_fill(0, 1000, ['a' => 'b'])], ApiResource::class],
]);

it('produces the same json as ApiCollection', function (array $resource, ?string $collects): void {
    $chunked = new ChunkedApiCollection($resource, $collects);
    $standard = new ApiCollection($resource, $collects);

    expect($chunked->toResponse($this->request)->getContent())
        ->toBe($standard->toResponse($this->request)->getContent());
})->with('payloads');

it('filters out missing values', function (): void {
    $collection = new ChunkedApiCollection(array_fill(0, 100, new FixtureModel()), FixtureApiResource::class);

    $data = $collection->toResponse($this->request)->getData(true)['data'][0];

    expect($data)
        ->not->toHaveKey('seventh')
        ->toHaveKey('eighth', 'default');
});

it('returns response with correct data', function (): void {
    $collection = new ChunkedApiCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class);

    $response = $collection->toResponse($this->request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->getStatusCode()->toBe(200)
        ->headers->get('Content-Type')->toBe('application/json')
        ->getData(true)->toBe(['data' => array_fill(0, 100, ['a' => 'b']), 'apiVersion' => '1.2']);
});

it('returns an empty data array when collection is empty', function (): void {
    $collection = new ChunkedApiCollection([], ApiResource::class);

    expect($collection->toResponse($this->request)->getContent())
        ->toBe('{"data":[],"apiVersion":"1.2"}');
});

it('merges additional data', function (): void {
    $collection = (new ChunkedApiCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class))
        ->additional(['foo' => 'bar']);

    expect($collection->toResponse($this->request)->getData())
        ->foo->toBe('bar')
        ->apiVersion->toBe('1.2');
});

it('keeps the underlying resource as the response original', function (): void {
    $collection = new ChunkedApiCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class);

    expect($collection->toResponse($this->request)->original)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(100);
});

it('calls the withResponse hook', function (): void {
    $collection = new FixtureRespondingCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class);

    expect($collection->toResponse($this->request)->headers->get('X-Fixture'))
        ->toBe('called');
});

describe('fallback to the standard response', function (): void {
    it('falls back for a paginator', function (): void {
        $items = collect([['a' => 'b'], ['a' => 'c']]);

        $chunked = new ChunkedApiCollection(
            new LengthAwarePaginator($items, 10, 2, 1, ['path' => '/']),
            ApiResource::class,
        );
        $standard = new ApiCollection(
            new LengthAwarePaginator($items, 10, 2, 1, ['path' => '/']),
            ApiResource::class,
        );

        $response = $chunked->toResponse($this->request);

        expect($response->getData())
            ->links->not->toBeNull()
            ->meta->not->toBeNull()
            ->and($response->getContent())->toBe($standard->toResponse($this->request)->getContent());
    });

    it('falls back when the collection keys are not a list', function (): void {
        $resource = ['first' => ['a' => 'b'], 'second' => ['a' => 'c']];

        $chunked = new ChunkedApiCollection($resource, ApiResource::class);
        $standard = new ApiCollection($resource, ApiResource::class);

        expect($chunked->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->toContain('"first"');
    });

    it('falls back for an empty collection', function (): void {
        $chunked = new ChunkedApiCollection([], ApiResource::class);
        $standard = new ApiCollection([], ApiResource::class);

        expect($chunked->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent());
    });

    it('falls back when the resource uses pretty printed json options', function (): void {
        $resource = array_fill(0, 100, ['a' => 'b']);

        $chunked = new ChunkedApiCollection($resource, FixturePrettyApiResource::class);
        $standard = new ApiCollection($resource, FixturePrettyApiResource::class);

        expect($chunked->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->toContain("\n");
    });
});

it('fails the same way as ApiCollection on malformed utf-8', function (): void {
    $resource = array_fill(0, 100, ['a' => "\xB1\x31"]);

    $failure = fn (string $class) => rescue(
        fn () => (new $class($resource, ApiResource::class))->toResponse($this->request),
        fn (Throwable $e) => [$e::class, $e->getMessage()],
        report: false,
    );

    expect($failure(ChunkedApiCollection::class))
        ->toBe($failure(ApiCollection::class))
        ->toBe([InvalidArgumentException::class, 'Malformed UTF-8 characters, possibly incorrectly encoded']);
});

describe('json options declared on the collection', function (): void {
    afterEach(fn () => FixtureOptionsCollection::$options = 0);

    it('falls back when the collection asks for pretty printing', function (): void {
        FixtureOptionsCollection::$options = JSON_PRETTY_PRINT;
        $resource = array_fill(0, 100, ['a' => 'ąćę']);

        $chunked = (new FixtureOptionsCollection($resource))->chunked();
        $standard = new FixtureOptionsCollection($resource);

        expect($chunked->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->toContain("\n");
    });

    it('honours options that do not prevent chunking', function (): void {
        FixtureOptionsCollection::$options = JSON_UNESCAPED_UNICODE;
        $resource = array_fill(0, 100, ['a' => 'ąćę']);

        $chunked = (new FixtureOptionsCollection($resource))->chunked();
        $standard = new FixtureOptionsCollection($resource);

        expect($chunked->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->toContain('ąćę');
    });
});

describe('chunk threshold', function (): void {
    it('falls back below the threshold', function (): void {
        $collection = new FixtureProbeCollection(array_fill(0, 99, ['a' => 'b']), ApiResource::class);
        $standard = new ApiCollection(array_fill(0, 99, ['a' => 'b']), ApiResource::class);

        expect($collection->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->and($collection->toArrayCalls)->toBe(1);
    });

    it('chunks from the threshold up', function (): void {
        $collection = new FixtureProbeCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class);
        $standard = new ApiCollection(array_fill(0, 100, ['a' => 'b']), ApiResource::class);

        expect($collection->toResponse($this->request)->getContent())
            ->toBe($standard->toResponse($this->request)->getContent())
            ->and($collection->toArrayCalls)->toBe(0);
    });

    it('honours a threshold lowered by a subclass', function (): void {
        $collection = new FixtureEagerCollection([['a' => 'b']], ApiResource::class);

        expect($collection->toResponse($this->request)->getContent())
            ->toBe('{"data":[{"a":"b"}],"apiVersion":"1.2"}')
            ->and($collection->toArrayCalls)->toBe(0);
    });
});

it('does not wrap when there is no wrapper and no envelope', function (): void {
    ApiResource::withoutWrapping();

    $resource = array_fill(0, 100, new FixtureModel());
    $chunked = (new FixtureChunkedCollection($resource))->chunked();
    $standard = new FixtureChunkedCollection($resource);

    expect($chunked->toResponse($this->request)->getContent())
        ->toBe($standard->toResponse($this->request)->getContent())
        ->toStartWith('[');

    ApiResource::wrap('data');
});
