<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Database\Eloquent\Model;

class FixtureModel extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->setAttribute('id', 5);
        $this->setAttribute('is_published', true);

        parent::__construct($attributes);
        static::unguard();
    }
}
