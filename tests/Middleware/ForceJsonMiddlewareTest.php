<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Middleware;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Bundles\Middleware\ForceJsonMiddleware;

use function Totem\SamSkeleton\Tests\createAcceptRequest;

uses(TestCase::class);

covers(ForceJsonMiddleware::class);

beforeEach(function () {
    $this->middleware = new ForceJsonMiddleware();
});

it('returns the callback result', function (): void {
    $middleware = $this->middleware->handle(createAcceptRequest(), fn (Request $request) => $request);

    expect($middleware)
        ->toBeInstanceOf(Request::class)
        ->headers->get('Accept')->toContain('application/json')
        ->getAcceptableContentTypes()->toContain('application/json');
});

it('replaced wildcard Accept header to `application/json`', function (): void {
    $this->middleware->handle(createAcceptRequest(), function (Request $request) {
        expect($request)
            ->headers->get('Accept')->toContain('application/json')
            ->getAcceptableContentTypes()->toContain('application/json');
    });
});

it('leaves Accept header to original value', function ($payload): void {
    $this->middleware->handle(createAcceptRequest($payload), function (Request $request) use ($payload) {
        expect($request)
            ->headers->get('Accept')->toContain($payload)
            ->getAcceptableContentTypes()->toContain($payload);
    });
})->with([
    'application/json',
    'application/xml',
    'application/x-www-form-urlencoded',
    'text/html',
    'text/plain',
    'text/csv',
]);
