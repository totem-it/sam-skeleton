<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Totem\SamSkeleton\Bundles\Auth\TrustOnlyAuthenticated;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(TrustOnlyAuthenticated::class);

beforeEach(function () {
    $this->uuid = fake()->uuid();

    $this->user = new FixtureUser([
        'email' => fake()->email(),
        'password' => bcrypt(fake()->password()),
        'uuid' => $this->uuid,
    ]);

    Route::middleware(TrustOnlyAuthenticated::class)->get('user/{uuid?}', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

describe('Middleware Logic', function (): void {
    beforeEach(function () {
        $this->middleware = new TrustOnlyAuthenticated();
    });

    it('allows access when user UUID matches the route UUID', function () {
        $request = Request::create('user/' . $this->uuid);
        $request->setUserResolver(fn () => $this->user);
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request));

        $response = $this->middleware->handle($request, fn ($request) => 'next step');

        expect($response)->toBe('next step');
    });

    it('throw an exception when user UUID does not match route UUID', function () {
        $request = Request::create('user/' . fake()->uuid());
        $request->setUserResolver(fn () => $this->user);
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request));

        expect(fn () => $this->middleware->handle($request, fn () => null))
            ->toThrow(AccessDeniedHttpException::class, __('The user is not allowed to modify it.'));
    });

    it('throws an exception if the user is not logged in', function () {
        $request = Request::create('user/' . fake()->uuid());

        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        expect(fn () => $this->middleware->handle($request, fn () => null))
            ->toThrow(AccessDeniedHttpException::class, __('The user is not allowed to modify it.'));
    });

    it('throws an exception if the route UUID is not defined', function () {
        $request = Request::create('user');
        $request->setUserResolver(fn () => $this->user);

        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        expect(fn () => $this->middleware->handle($request, fn () => null))
            ->toThrow(AccessDeniedHttpException::class, __('The user is not allowed to modify it.'));
    });
});

describe('Access via HTTP requests', function (): void {
    beforeEach(function () {
        $this->actingAs($this->user);
    });

    it('grants access when user UUID matches route UUID', function (): void {
        $response = $this->get('user/' . $this->uuid);

        $response
            ->assertOk()
            ->assertJson(['message' => 'Access granted']);
    });

    it('denies access when user UUID does not match route UUID', function (): void {
        $response = $this->get('user/' . fake()->uuid());

        $response
            ->assertForbidden()
            ->assertSee(__('The user is not allowed to modify it.'));
    });
});
