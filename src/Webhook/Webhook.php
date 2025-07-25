<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Webhook;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

readonly class Webhook
{
    /**
     * @param \Laravel\Horizon\Events\JobDeleted $event
     */
    public function handle($event): void
    {
        $command = $this->unserialize();

        Log::log(
            level: $event->job->hasFailed() ? LogLevel::ERROR : LogLevel::DEBUG,
            message: 'Webhook',
            context: [
                'id' => $event->job->getJobId(),
                'class' => $event->job->resolveName(),
                'body' => $command && method_exists($command, 'toArray') ? $command->toArray() : '',
            ],
        );
    }

    public static function run(ShouldQueue $command): PendingDispatch
    {
        return new PendingDispatch($command);
    }

    private function unserialize(): false|object
    {
        return unserialize($event->job->payload()['data']['command'] ?? '', ['allowed_classes' => true]);
    }
}
