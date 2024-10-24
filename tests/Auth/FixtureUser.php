<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Auth;

use Illuminate\Foundation\Auth\User;

class FixtureUser extends User
{
    public function __construct(array $attributes = [])
    {
        static::unguard();

        parent::__construct([...$attributes,
            'password' => 'aaa',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);
    }
}
