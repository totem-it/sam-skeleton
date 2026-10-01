<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ValueObject;

use Orchestra\Testbench\TestCase;
use Totem\SamSkeleton\ValueObject\ParseValueObject;

uses(TestCase::class);

mutates(ParseValueObject::class);

beforeEach(function (): void {
    $this->dummy = new FixtureParseValueObject();
});

it('can get from trimOrNull method', function ($payload, $value): void {
    expect($this->dummy->callTrimOrNull($payload))
        ->toBe($value);
})->with([
    'trim string when string' => [' some value   ', 'some value'],
    'null when empty string' => ['', null],
    'null when null' => [null, null],
]);

it('can get from intOrNull method', function ($payload, $value): void {
    expect($this->dummy->callIntOrNull($payload))
        ->toBe($value);
})->with([
    'int when string with number' => ['72', 72],
    'int when empty string' => ['', 0],
    'null when null' => [null, null],
]);
