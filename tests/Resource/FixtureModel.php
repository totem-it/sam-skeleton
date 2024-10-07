<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Database\Eloquent\Model;

class FixtureModel extends Model
{
    protected $attributes = [
        'id' => 5,
        'is_published' => true,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::unguard();
    }
}
