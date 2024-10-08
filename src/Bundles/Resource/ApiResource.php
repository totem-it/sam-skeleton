<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property bool $preserveKeys
 *
 * @method static ApiCollection collection(mixed $resource)
 */
class ApiResource extends JsonResource
{
    use AdditionalResourceData;

    public static function noContent(): self
    {
        return new self(true);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_bool($this->resource)) {
            return [];
        }

        return parent::toArray($request);
    }

    public function withResponse($request, $response): void
    {
        if (is_bool($this->resource)) {
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        }
    }

    protected static function newCollection($resource): ApiCollection
    {
        return new ApiCollection($resource, static::class);
    }

    protected function whenHasAttribute(string $attribute, $value = null, $default = null): mixed
    {
        if (array_key_exists($attribute, $this->resource->getAttributes())) {
            return $value instanceof Closure
                ? $value()
                : $this->resource->getAttribute($attribute);
        }

        return func_num_args() === 3 ? value($default) : new MissingValue();
    }
}
