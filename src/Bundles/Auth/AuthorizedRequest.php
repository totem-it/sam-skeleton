<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Auth;

use Illuminate\Contracts\Container\BindingResolutionException;

trait AuthorizedRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        try {
            return $this->container?->make('auth')->check() ?? false;
        } catch (BindingResolutionException) {
            return false;
        }
    }
}
