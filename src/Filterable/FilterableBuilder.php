<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable;

use Illuminate\Http\Request;

class FilterableBuilder
{
    public function __construct(
        protected Request $request,
    ) {

    }

    public static function create(Request $request): self
    {
        return new self($request);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
