<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Middleware;

use Illuminate\Http\Response;
use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\Bundles\Middleware\LocalizationMiddleware;

use function Totem\SamSkeleton\Tests\createLangRequest;

uses(TestCase::class);

beforeEach(function () {
    $this->lines = [
        config('app.locale') => 'test line for DEFAULT',
        'en' => 'test line for EN',
        'de' => 'test line for DEU',
    ];
    foreach ($this->lines as $locale => $line) {
        app('translator')->addLines(['test.fake_' => $line], $locale);
    }
    $this->middleware = new LocalizationMiddleware;
    $this->translator = app('translator');
});

it('can get locale', function () {
    expect([
        $this->translator->getLocale(),
        __('test.fake_', [], config('app.locale')),
    ])->sequence(
        fn ($data) => $data
            ->toBe(config('app.locale')),
        fn ($data) => $data
            ->toBe($this->lines[config('app.locale')]),
    );
});

it('can parse', function ($payload, $value) {
    $this->middleware->handle(createLangRequest($payload), fn () => new Response);

    expect([
        $this->translator->getLocale(),
        __('test.fake_', [], $value),
    ])->sequence(
        fn ($data) => $data
            ->toBe($value),
        fn ($data) => $data
            ->toBe($this->lines[$value]),
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

it('does not set locale', function ($payload) {
    $this->middleware->handle(createLangRequest($payload), fn () => new Response);

    expect([
        $this->translator->getLocale(),
        __('test.fake_', [], config('app.locale')),
    ])->sequence(
        fn ($data) => $data
            ->toBe(config('app.locale')),
        fn ($data) => $data
            ->toBe($this->lines[config('app.locale')]),
    );
})->with([
    'header is not sent' => [''],
    'header has asterix' => ['*'],
]);
