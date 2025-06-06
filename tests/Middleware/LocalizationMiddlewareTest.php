<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Middleware;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Bundles\Middleware\LocalizationMiddleware;

use function Totem\SamSkeleton\Tests\createLangRequest;

uses(TestCase::class);

covers(LocalizationMiddleware::class);

beforeEach(function () {
    $this->middleware = new LocalizationMiddleware();
    $this->translator = resolve('translator');
    $this->lines = [
        config('app.locale') => 'test line for DEFAULT',
        'en' => 'test line for EN',
        'de' => 'test line for DEU',
    ];
    foreach ($this->lines as $locale => $line) {
        $this->translator->addLines(['test.fake_' => $line], $locale);
    }
});

it('can get locale', function (): void {
    expect([
        $this->translator->getLocale(),
        __('test.fake_', [], config('app.locale')),
    ])->sequence(
        fn ($data) => $data->toBe(config('app.locale')),
        fn ($data) => $data->toBe($this->lines[config('app.locale')]),
    );
});

describe('locale behavior', function (): void {
    it('can parse header', function ($payload, $value): void {
        $this->middleware->handle(createLangRequest($payload), fn () => null);

        expect([
            $this->translator->getLocale(),
            __('test.fake_', [], $value),
        ])->sequence(
            fn ($data) => $data->toBe($value),
            fn ($data) => $data->toBe($this->lines[$value]),
        );
    })->with([
        'single locale' => ['de', 'de'],
        'locale with country' => ['en-US', 'en'],
        'locale with country and quality value' => ['en-US;q=0.5', 'en'],
        'multiple locales' => ['en, de', 'en'],
        'multiple locales with countries' => ['en-US, de-DE', 'en'],
        'multiple locales with countries and quality values' => ['en-US;q=0.8, de-DE;q=0.9', 'de'],
        'mixed locale values' => ['de-DE, de;q=0.7, fr;q=0.9, en;q=0.8, *;q=0.5', 'de'],
    ]);

    it('does not set locale', function ($payload): void {
        $this->middleware->handle(createLangRequest($payload), fn () => null);

        expect([
            $this->translator->getLocale(),
            __('test.fake_', [], config('app.locale')),
            app()->getLocale(),
        ])->sequence(
            fn ($data) => $data->toBe(config('app.locale')),
            fn ($data) => $data->toBe($this->lines[config('app.locale')]),
            fn ($data) => $data->not->toBe($payload),
        );
    })->with([
        'header is not sent' => [''],
        'header has asterisk' => ['*'],
        'incompatible locale' => [fake()->sentence(1)],
        'monkey string' => [' ; ,;'],
    ]);

    it('does not set locale when header is missing', function () {
        $request = createLangRequest();
        $request->headers->replace();

        $this->middleware->handle($request, fn () => null);

        expect([
            $this->translator->getLocale(),
            __('test.fake_', [], config('app.locale')),
            app()->getLocale(),
        ])->sequence(
            fn ($data) => $data->toBe(config('app.locale')),
            fn ($data) => $data->toBe($this->lines[config('app.locale')]),
            fn ($data) => $data->toBe(config('app.locale')),
        );
    });
});

it('not changing return callback result', function ($payload): void {
    /** @var Request $middleware */
    $middleware = $this->middleware->handle(createLangRequest($payload), fn (Request $request) => $request);

    expect($middleware)
        ->toBeInstanceOf(Request::class)
        ->headers->get('accept-language')->toContain($payload);
})->with([
    'country code' => ['pl'],
    'empty' => [''],
    'empty space' => ['                            '],
    'whitespace' => ['             a               '],
    'asterisk' => ['*'],
    'monkey string' => [' ; ,;'],
]);

it('not set accept-language header when request header is missing', function () {
    $request = createLangRequest();
    $request->headers->replace();

    /** @var Request $middleware */
    $middleware = $this->middleware->handle($request, fn (Request $request) => $request);

    expect($middleware)
        ->toBeInstanceOf(Request::class)
        ->headers->get('accept-language')->toBeNull();
});

describe('middleware', function (): void {
    it('is invoked with the correct accept-language header', function ($payload) {
        /** @var LocalizationMiddleware $mock */
        $mock = $this->partialMock(LocalizationMiddleware::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('parseHeader')
            ->once()
            ->andReturn('')
            ->getMock();

        $mock->handle(createLangRequest($payload), fn () => null);
    })->with([
        'country code' => ['pl'],
        'whitespace in middle & end' => ['j             a               '],
        'monkey string' => [' ; ,;'],
        'mixed locale values' => ['de-DE, de;q=0.7, fr;q=0.9, en;q=0.8, *;q=0.5'],
    ]);

    it('is ignored if the accept-language header is invalid', function ($payload) {
        /** @var LocalizationMiddleware $mock */
        $mock = $this->partialMock(LocalizationMiddleware::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldNotReceive('parseHeader')
            ->getMock();

        $mock->handle(createLangRequest($payload), fn () => null);
    })->with([
        'empty string' => [''],
        'empty space' => ['                            '],
        'whitespace in start & end' => ['             a               '],
        'asterisk' => ['*'],
    ]);

    it('is ignored if the accept-language header is missing', function () {
        /** @var LocalizationMiddleware $mock */
        $mock = $this->partialMock(LocalizationMiddleware::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldNotReceive('parseHeader')
            ->getMock();

        $request = createLangRequest();
        $request->headers->replace();

        $mock->handle($request, fn () => null);
    });
});
