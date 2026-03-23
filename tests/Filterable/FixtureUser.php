<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Filterable;

use Illuminate\Foundation\Auth\User;

class FixtureUser extends User
{
    /**
     * @param array<string, string | int> $attributes
     */
    public function __construct(array $attributes = [])
    {
        static::unguard();

        $this->setTable('users');

        parent::__construct([
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@test.com',
            ...$attributes,
        ]);
    }
}
