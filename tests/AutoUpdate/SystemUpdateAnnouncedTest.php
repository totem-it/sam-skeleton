<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\AutoUpdate;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Totem\SamSkeleton\AutoUpdate\SystemUpdateAnnounced;
use Totem\SamSkeleton\Tests\TestCase;

uses(TestCase::class);

covers(SystemUpdateAnnounced::class);

afterEach(function (): void {
    Carbon::setTestNow();
});

it('exposes the minutes given in the constructor', function (): void {
    expect(new SystemUpdateAnnounced(15))
        ->toBeInstanceOf(ShouldBroadcastNow::class)
        ->minutes->toBe(15);
});

it('broadcasts on the `system` channel', function (): void {
    $channels = (new SystemUpdateAnnounced(10))->broadcastOn();

    expect($channels)
        ->toBeArray()
        ->toHaveCount(1)
        ->sequence(
            fn ($channel) => $channel
                ->toBeInstanceOf(Channel::class)
                ->name->toBe('system'),
        );
});

it('broadcasts as `system.update`', function (): void {
    expect((new SystemUpdateAnnounced(10))->broadcastAs())
        ->toBe('system.update');
});

it('broadcasts the minutes and the calculated time', function (int $minutes): void {
    Carbon::setTestNow('2026-09-10 12:00:00');

    expect((new SystemUpdateAnnounced($minutes))->broadcastWith())
        ->toBe([
            'minutes' => $minutes,
            'at' => Carbon::parse('2026-09-10 12:00:00')->addMinutes($minutes)->toIso8601String(),
        ]);
})->with([
    'one minute' => 1,
    'default' => 10,
    'an hour' => 60,
    'a day' => 1440,
]);

it('is dispatchable', function (): void {
    Event::fake();

    SystemUpdateAnnounced::dispatch(7);

    Event::assertDispatched(
        event: SystemUpdateAnnounced::class,
        callback: fn (SystemUpdateAnnounced $event): bool => $event->minutes === 7
    );
});
