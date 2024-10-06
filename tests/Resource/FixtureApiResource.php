<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Resource;

use Illuminate\Http\Request;
use Totem\SamSkeleton\Bundles\Resource\ApiResource;

class FixtureApiResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first' => $this->whenHasAttribute('is_published'),
            'second' => $this->whenHasAttribute('is_published', 'override value'),
            'third' => $this->whenHasAttribute('is_published', function () {
                return 'override value';
            }),
            'fourth' => $this->whenHasAttribute('is_published', $this->is_published, 'default'),
            'fifth' => $this->whenHasAttribute('is_published', $this->is_published, function () {
                return 'default';
            }),
            'sixth' => $this->whenHasAttribute('is_published', $this->is_published, fn () => 'default'),
            'seventh' => $this->whenHasAttribute('other_attribute'),
            'eighth' => $this->whenHasAttribute('other_attribute', null, 'default'),
        ];
    }
}
