<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\AutoUpdate;

use Illuminate\Console\Command;

class UpdateNoticeCommand extends Command
{
    protected $signature = 'sam:update-notice
                    {minutes=10 : Minutes left until the update}';

    protected $description = 'Broadcast an upcoming update notice to all users';

    public function handle(): int
    {
        $config = config('broadcasting.default');

        if ($config !== 'reverb') {
            $this->error(sprintf('Update notice works only on `broadcasting.connections` set to `reverb`. Current [%s].', $config));

            return self::INVALID;
        }

        $minutes = (int) $this->argument('minutes');

        if ($minutes < 1) {
            $this->error('The `minutes` argument must be a positive integer.');

            return self::INVALID;
        }

        SystemUpdateAnnounced::dispatch($minutes);

        $this->info(sprintf('Update notice broadcast: %d min.', $minutes));

        return self::SUCCESS;
    }
}
