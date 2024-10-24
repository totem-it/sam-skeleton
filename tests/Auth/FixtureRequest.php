<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Totem\SamSkeleton\Bundles\Auth\AuthorizedRequest;

class FixtureRequest extends FormRequest
{
    use AuthorizedRequest;
}
