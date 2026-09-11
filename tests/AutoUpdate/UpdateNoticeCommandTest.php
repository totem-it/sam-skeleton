<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\AutoUpdate;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Console\Command\Command;
use Totem\SamSkeleton\AutoUpdate\SystemUpdateAnnounced;
use Totem\SamSkeleton\AutoUpdate\UpdateNoticeCommand;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(UpdateNoticeCommand::class);

beforeEach(function (): void {
    Event::fake();
});

it('is registered in the console kernel', function (): void {
    expect($this->app->make(Kernel::class)->all())
        ->toHaveKey('sam:update-notice');
});

it('fails when the broadcaster is not `reverb`', function (string|null $driver): void {
    config()->set('broadcasting.default', $driver);

    $this->artisan('sam:update-notice')
        ->expectsOutputToContain(sprintf(
            'Update notice works only on `broadcasting.connections` set to `reverb`. Current [%s].',
            $driver
        ))
        ->assertExitCode(Command::INVALID);

    Event::assertNotDispatched(SystemUpdateAnnounced::class);
})->with([
    'null driver' => 'null',
    'log' => 'log',
    'pusher' => 'pusher',
    'not set' => null,
]);

it('fails when the minutes argument is not a positive integer', function (string $minutes): void {
    config()->set('broadcasting.default', 'reverb');

    $this->artisan('sam:update-notice', ['minutes' => $minutes])
        ->expectsOutputToContain('The `minutes` argument must be a positive integer.')
        ->assertExitCode(Command::INVALID);

    Event::assertNotDispatched(SystemUpdateAnnounced::class);
})->with([
    'zero' => '0',
    'negative' => '-5',
    'not a number' => 'abc',
    'empty' => '',
]);

it('broadcasts the notice with the given minutes', function (): void {
    config()->set('broadcasting.default', 'reverb');

    $this->artisan('sam:update-notice', ['minutes' => '25'])
        ->expectsOutputToContain('Update notice broadcast: 25 min.')
        ->assertExitCode(Command::SUCCESS);

    Event::assertDispatched(
        event: SystemUpdateAnnounced::class,
        callback: fn (SystemUpdateAnnounced $event): bool => $event->minutes === 25
    );
});

it('broadcasts 10 minutes by default', function (): void {
    config()->set('broadcasting.default', 'reverb');

    $this->artisan('sam:update-notice')
        ->expectsOutputToContain('Update notice broadcast: 10 min.')
        ->assertExitCode(Command::SUCCESS);

    Event::assertDispatched(
        event: SystemUpdateAnnounced::class,
        callback: fn (SystemUpdateAnnounced $event): bool => $event->minutes === 10
    );
});
