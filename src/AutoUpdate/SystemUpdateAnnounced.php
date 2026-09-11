<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\AutoUpdate;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class SystemUpdateAnnounced implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly int $minutes,
    ) {
    }

    /**
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return [new Channel('system')];
    }

    public function broadcastAs(): string
    {
        return 'system.update';
    }

    /**
     * @return array{minutes: int, at: string}
     */
    public function broadcastWith(): array
    {
        return [
            'minutes' => $this->minutes,
            'at' => now()->addMinutes($this->minutes)->toIso8601String(),
        ];
    }
}
