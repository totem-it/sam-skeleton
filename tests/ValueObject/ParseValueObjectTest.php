<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ValueObject;

use Orchestra\Testbench\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->dummy = $this->partialMock(FixtureParseValueObject::class)->shouldAllowMockingProtectedMethods();
});

it('can get from trimOrNull method', function ($payload, $value): void {
    expect($this->dummy->trimOrNull($payload))
        ->toBe($value);
})->with([
    'trim string when string' => [' some value   ', 'some value'],
    'null when empty string' => ['', null],
    'null when null' => [null, null],
]);

it('can get from intOrNull method', function ($payload, $value): void {
    expect($this->dummy->intOrNull($payload))
        ->toBe($value);
})->with([
    'int when string with number' => ['72', 72],
    'int when empty string' => ['', 0],
    'null when null' => [null, null],
]);
