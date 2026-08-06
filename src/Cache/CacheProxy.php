<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

use BadMethodCallException;
use LogicException;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @template-covariant TClass of object
 *
 * @mixin TClass
 */
final class CacheProxy
{
    /**
     * @var array<string, \ReflectionMethod>
     */
    private static array $methods = [];

    /** @var array<string, \Totem\SamSkeleton\Cache\CachedQuery|null> */
    private static array $attributes = [];

    /**
     * @param TClass $target
     * @param non-empty-string|list<non-empty-string> $namespaces
     */
    public function __construct(
        private readonly object $target,
        private readonly string|array $namespaces,
        private readonly CacheProfile|null $profile,
        private readonly QueryCache $cache,
    ) {
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        $reflection = $this->method($method);
        $attribute = $this->attribute($reflection);

        return $this->cache->remember(
            namespaces: $this->namespaces,
            operation: $this->operation($method),
            callback: fn (): mixed => $this->target->{$method}(...$parameters),
            profile: $this->profile ?? $attribute->profile ?? CacheProfile::SHORT,
            arguments: $this->arguments($reflection, $parameters),
        );
    }

    private function operation(string $method): string
    {
        $class = $this->target::class;

        return class_basename($class) . '.' . substr(sha1($class), 0, 8) . ':' . $method;
    }

    /**
     * @param array<array-key, mixed> $parameters
     *
     * @return array<array-key, mixed>
     */
    private function arguments(ReflectionMethod $method, array $parameters): array
    {
        $arguments = [];
        $consumed = [];

        foreach ($method->getParameters() as $position => $parameter) {
            if ($parameter->isVariadic()) {
                break;
            }

            $name = $parameter->getName();

            if (array_key_exists($name, $parameters)) {
                $arguments[] = $parameters[$name];
                $consumed[] = $name;

                continue;
            }

            if (array_key_exists($position, $parameters)) {
                $arguments[] = $parameters[$position];
                $consumed[] = $position;

                continue;
            }

            if (! $parameter->isDefaultValueAvailable()) {
                return $parameters;
            }

            $arguments[] = $parameter->getDefaultValue();
        }

        foreach ($parameters as $key => $value) {
            if (! in_array($key, $consumed, true)) {
                $arguments[] = $value;
            }
        }

        return $arguments;
    }

    private function method(string $method): ReflectionMethod
    {
        $key = $this->target::class . '::' . $method;

        if (isset(self::$methods[$key])) {
            return self::$methods[$key];
        }

        if (! method_exists($this->target, $method)) {
            throw new BadMethodCallException(sprintf(
                'Method [%s] does not exist on [%s].',
                $method,
                $this->target::class
            ));
        }

        return self::$methods[$key] = new ReflectionMethod($this->target, $method);
    }

    private function attribute(ReflectionMethod $method): CachedQuery
    {
        $key = $method->class . '::' . $method->name;

        if (! array_key_exists($key, self::$attributes)) {
            $attributes = $method->getAttributes(CachedQuery::class);

            self::$attributes[$key] = $attributes === [] ? null : $attributes[0]->newInstance();
        }

        $attribute = self::$attributes[$key];

        if ($attribute === null) {
            throw new LogicException(sprintf(
                'Method [%s::%s] is not marked with #[CachedQuery] and must not be cached. '
                . 'Caching a write would skip every call after the first one.',
                $method->class,
                $method->name
            ));
        }

        $this->assertReturnsValue($method);

        return $attribute;
    }

    private function assertReturnsValue(ReflectionMethod $method): void
    {
        $type = $method->getReturnType();

        if ($type instanceof ReflectionNamedType && in_array($type->getName(), ['void', 'never'], true)) {
            throw new LogicException(sprintf(
                'Method [%s::%s] returns %s and cannot be cached.',
                $method->class,
                $method->name,
                $type->getName()
            ));
        }
    }
}
