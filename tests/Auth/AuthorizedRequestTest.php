<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Route;
use Totem\SamSkeleton\Bundles\Auth\AuthorizedRequest;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

mutates(AuthorizedRequest::class);

beforeEach(function () {
    $this->user = new FixtureUser();

    Route::get('/', fn (FixtureRequest $request) => response()->json(['message' => $request->method()]));
});

describe('Authorization logic', function () {
    it('grants access to authorized users', function (): void {
        $request = new FixtureRequest();
        $request->setContainer(app());

        $this->actingAs($this->user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies access to unauthorized users when container is not provided', function (): void {
        $request = new FixtureRequest();

        expect($request->authorize())->toBeFalse();
    });

    it('denies access when container is empty', function (): void {
        $request = new FixtureRequest();
        $request->setContainer(new Container());

        expect($request->authorize())->toBeFalse();
    });
});

describe('Access via HTTP requests', function (): void {
    it('grants access to authorized users', function (): void {
        $this->actingAs($this->user);

        $this->get('/')
            ->assertOk()
            ->assertJson(['message' => 'GET']);
    });

    it('throw an exception for unauthorized users', function (): void {
        $response = $this->get('/');

        expect(fn () => $response->json())
            ->toThrow(AuthorizationException::class, __('This action is unauthorized.'));
    });
});
