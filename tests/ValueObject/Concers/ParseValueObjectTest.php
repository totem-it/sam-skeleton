<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ValueObject\Concerns;

use Pest\Expectation;
use Totem\SamSkeleton\Bundles\ValueObject\Concerns\ParseValueObject;
use TypeError;

beforeEach(function () {
    $this->dummy = new class () {
        use ParseValueObject;

        public function testTrimOrNull(string|null $value): string|null
        {
            return self::trimOrNull($value);
        }

        public function testIntOrNull(string|null $value): int|null
        {
            return self::intOrNull($value);
        }
    };
});

describe('trimOrNull', function () {
    it('can get trimmed string', function () {
        expect($this->dummy->testTrimOrNull('  some value  '))
            ->toBe('some value');
    });

    it('can get null when argument is', function ($payload) {
        expect($this->dummy->testTrimOrNull($payload))
            ->toBeNull();
    })->with([
        'empty string' => '',
        'null' => null
    ]);

    it('throws exception when argument type is', function ($payload) {
        expect(fn () => $this->dummy->testTrimOrNull($payload))
            ->toThrow(TypeError::class);
    })->with([
        'int' => fake()->randomNumber(),
        'float' => fake()->randomFloat(),
        'boolean' => fake()->boolean(),
    ]);
});

describe('intOrNull()', function () {
    it('can get int', function () {
        expect([
            $this->dummy->testIntOrNull('42'),
            $this->dummy->testIntOrNull('')
        ])
            ->sequence(
                fn (Expectation $value) => $value
                    ->toBe(42),
                fn (Expectation $value) => $value
                    ->toBe(0),
            );
    });

    it('can get null when argument is null', function () {
        expect($this->dummy->testIntOrNull(null))
            ->toBeNull();
    });

    it('throws exception when argument type is', function ($payload) {
        expect(fn () => $this->dummy->testIntOrNull($payload))
            ->toThrow(TypeError::class);
    })->with([
        'int' => fake()->randomNumber(),
        'float' => fake()->randomFloat(),
        'boolean' => fake()->boolean(),
    ]);
});
