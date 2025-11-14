<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\ServiceProvider;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Totem\SamSkeleton\Tests\Repository\FixtureRepository;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->testCase = new FixtureRepository();
});

it('can throw missing exception', function (): void {
    expect(fn () => $this->testCase->testableMissing('Missing data.'))
        ->toThrow(
            exception: UnprocessableEntityHttpException::class,
            exceptionMessage: 'Missing data.'
        );
});

it('can throw locked exception', function (): void {
    expect(fn () => $this->testCase->testableLocked('Locked entity.'))
        ->toThrow(
            exception: LockedHttpException::class,
            exceptionMessage: 'Locked entity.'
        );
});

it('can throw not found exception', function (): void {
    expect(fn () => $this->testCase->testableNotFound('No data.'))
        ->toThrow(
            exception: HttpException::class,
            exceptionMessage: 'No data.'
        );
});
