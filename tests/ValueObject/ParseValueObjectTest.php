<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ValueObject;

use Mockery;

beforeEach(function () {
    $this->dummy = Mockery::mock(FixtureParseValueObject::class)->makePartial();
});

it('can get from trimOrNull method', function ($payload, $value): void {
    expect($this->dummy->shouldAllowMockingProtectedMethods()->trimOrNull($payload))
        ->toBe($value);
})->with([
    'trim string when string' => [' some value   ', 'some value'],
    'null when empty string' => ['', null],
    'null when null' => [null, null],
]);

it('can get from intOrNull method', function ($payload, $value): void {
    expect($this->dummy->shouldAllowMockingProtectedMethods()->intOrNull($payload))
        ->toBe($value);
})->with([
    'int when string with number' => ['72', 72],
    'int when empty string' => ['', 0],
    'null when null' => [null, null],
]);
