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
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        static::unguard();

        parent::__construct([...$attributes,
            'id' => 5,
            'is_published' => true,
        ]);
    }
}
