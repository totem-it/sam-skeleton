<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Filterable\Concerns\UseFields;

class FilterableBuilder
{
    use UseFields;

    public function __construct(
        protected Request $request,
    ) {

    }

    public static function create(Request $request): static
    {
        return new static($request);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
