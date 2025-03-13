<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property bool $is_published
 */
class FixtureModel extends Model
{
    public function __construct(array $attributes = [])
    {
        static::unguard();

        parent::__construct([...$attributes,
            'id' => 5,
            'is_published' => true,
        ]);
    }
}
