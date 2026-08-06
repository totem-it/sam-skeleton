<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Cache;

use Carbon\CarbonInterval;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class QueryCache
{
    private const string VERSION_PREFIX = 'cache-version:';
    private const int VERSION_LOCK_SECONDS = 5;
    private const int VERSION_LOCK_WAIT = 3;

    /**
     * Longest readable argument segment. Beyond this the arguments are hashed,
     * which keeps the key under the 250-byte limit memcached enforces.
     */
    private const int READABLE_LIMIT = 120;

    /**
     * Printable ASCII without the space. A segment outside it is hashed as well:
     * memcached rejects keys containing spaces or control bytes.
     */
    private const string PRINTABLE = '/^[\x21-\x7e]+$/';

    /** @var array<string, int> */
    private array $versions = [];

    /**
     * Get the cached query result or execute the callback and cache it.
     *
     * @template TValue
     *
     * @param non-empty-string|list<non-empty-string> $namespaces
     * @param \Closure(): TValue $callback
     * @param array<array-key, mixed> $arguments
     *
     * @return TValue
     */
    public function remember(
        string|array $namespaces,
        string $operation,
        Closure $callback,
        CacheProfile $profile = CacheProfile::SHORT,
        array $arguments = [],
    ): mixed {
        return Cache::flexible($this->key($namespaces, $operation, $arguments), $profile->ttl(), $callback);
    }

    /**
     * Bump the version of every given namespace, orphaning its entries.
     *
     * @param non-empty-string ...$namespaces
     */
    public function invalidate(string ...$namespaces): void
    {
        if ($namespaces === []) {
            return;
        }

        foreach ($this->namespaces(array_values($namespaces)) as $name) {
            $key = self::VERSION_PREFIX . $name;

            try {
                $this->versions[$name] = (int) Cache::lock($key . ':lock', self::VERSION_LOCK_SECONDS)
                    ->block(self::VERSION_LOCK_WAIT, static function () use ($key): int {
                        $next = ((int) Cache::get($key, 0)) + 1;

                        Cache::forever($key, $next);

                        return $next;
                    });
            } catch (LockTimeoutException) {
                unset($this->versions[$name]);
            }
        }
    }

    /**
     * @param non-empty-string $namespace
     */
    public function version(string $namespace): int
    {
        return $this->versions[$namespace] ??= $this->readVersion($namespace);
    }

    /**
     * @param non-empty-string|list<non-empty-string> $namespaces
     * @param array<array-key, mixed> $arguments
     */
    public function key(string|array $namespaces, string $operation, array $arguments = []): string
    {
        $prefix = implode('|', array_map(
            fn (string $namespace): string => $namespace . ':v' . $this->version($namespace),
            $this->namespaces($namespaces),
        ));

        $key = $prefix . ':' . $operation;

        if ($arguments === []) {
            return $key;
        }

        return $key . ':' . $this->fingerprint($arguments);
    }

    public function flushMemory(): void
    {
        $this->versions = [];
    }

    /**
     * @param string|list<string> $namespaces
     *
     * @return non-empty-list<non-empty-string>
     */
    private function namespaces(string|array $namespaces): array
    {
        $unique = [];

        foreach (is_string($namespaces) ? [$namespaces] : $namespaces as $namespace) {
            if (preg_match(self::PRINTABLE, $namespace) !== 1) {
                throw new InvalidArgumentException('A cache namespace must be a non-empty name of printable ASCII.');
            }

            $unique[$namespace] = $namespace;
        }

        if ($unique === []) {
            throw new InvalidArgumentException('A cached entry must depend on at least one namespace.');
        }

        return array_values($unique);
    }

    private function readVersion(string $name): int
    {
        $key = self::VERSION_PREFIX . $name;

        $version = Cache::get($key);

        if ($version === null) {
            Cache::add($key, 1, CarbonInterval::years(10));

            $version = Cache::get($key, 1);
        }

        return (int) $version;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    private function fingerprint(array $arguments): string
    {
        $values = array_values($arguments);

        if (count($values) === 1 && is_array($values[0]) && $values[0] !== []) {
            $values = $values[0];
        }

        $segment = $this->values($values);

        return strlen($segment) <= self::READABLE_LIMIT && preg_match(self::PRINTABLE, $segment) === 1
            ? $segment
            : sha1($segment);
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function values(array $values): string
    {
        if (! array_is_list($values)) {
            throw new InvalidArgumentException(sprintf(
                'Cache arguments must be lists, [%s] given as a key.',
                implode(', ', array_keys($values))
            ));
        }

        return implode(',', array_map($this->render(...), $values));
    }

    private function render(mixed $value): string
    {
        return match (true) {
            is_int($value) => (string) $value,
            is_string($value) => "'" . addcslashes($value, "\\'") . "'",
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => 'null',
            is_array($value) => '[' . $this->values($value) . ']',
            default => throw new InvalidArgumentException(sprintf(
                'Cache arguments must be ints, strings, bools, nulls or lists of them, [%s] given.',
                get_debug_type($value)
            )),
        };
    }
}
