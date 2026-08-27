<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Resource;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use InvalidArgumentException;
use JsonException;
use ReflectionException;

/**
 * @mixin \Illuminate\Http\Resources\Json\ResourceCollection
 */
trait ChunksResponse
{
    protected bool $chunked = false;

    /**
     * Minimal number of items for the chunked encoding.
     */
    protected int $chunkThreshold = 100;

    public function chunked(int|null $threshold = null): static
    {
        $this->chunked = true;
        $this->chunkThreshold = $threshold ?? $this->chunkThreshold;

        return $this;
    }

    public function toResponse($request): JsonResponse
    {
        $envelope = array_merge_recursive($this->with($request), $this->additional);

        try {
            $options = $this->jsonOptions();
        } catch (ReflectionException) {
            return parent::toResponse($request);
        }

        if ($this->shouldChunk($envelope, $options) === false) {
            return parent::toResponse($request);
        }

        $response = new JsonResponse(
            data: $this->encodeChunked($request, $envelope, $options),
            json: true,
        );
        $response->original = $this->resource;

        $this->withResponse($request, $response);

        return $response;
    }

    /**
     * @param array<array-key, mixed> $envelope
     */
    private function shouldChunk(array $envelope, int $options): bool
    {
        return $this->chunked
            && $this->collection->count() >= $this->chunkThreshold
            && $this->collection->first() instanceof JsonResource
            && ($options & JSON_PRETTY_PRINT) === 0
            && $this->resource instanceof AbstractPaginator === false
            && $this->resource instanceof AbstractCursorPaginator === false
            && ($envelope === [] || array_is_list($envelope) === false)
            && array_is_list($this->collection->all());
    }

    /**
     * @param array<array-key, mixed> $envelope
     */
    private function encodeChunked(Request $request, array $envelope, int $options): string
    {
        $json = '[';
        $separator = '';

        foreach ($this->collection as $item) {
            $json .= $separator . $this->encode($item->resolve($request), $options);
            $separator = ',';
        }

        $json .= ']';
        $wrapper = static::$wrap ?? ($envelope === [] ? null : 'data');

        if ($wrapper === null) {
            return $json;
        }

        $json = '{' . $this->encode($wrapper, $options) . ':' . $json;

        if ($envelope !== []) {
            $json .= ',' . substr($this->encode($envelope, $options), 1, -1);
        }

        return $json . '}';
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function encode(mixed $value, int $options): string
    {
        try {
            return json_encode($value, $options | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
