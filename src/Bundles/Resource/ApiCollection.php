<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollection extends ResourceCollection
{
    use AdditionalResourceData;

    public function __construct($resource, ?string $collects = null)
    {
        if ($collects !== null) {
            $this->collects = $collects;
        }

        parent::__construct($resource);
    }
}
