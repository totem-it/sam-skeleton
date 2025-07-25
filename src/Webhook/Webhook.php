<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Webhook;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Events\JobDeleted;
use Psr\Log\LogLevel;

readonly class Webhook
{
    public function handle(JobDeleted $event): void
    {
        $command = $this->unserialize($event);

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

    private function unserialize(JobDeleted $event): false|object
    {
        return unserialize($event->job->payload()['data']['command'] ?? '', ['allowed_classes' => true]);
    }
}
