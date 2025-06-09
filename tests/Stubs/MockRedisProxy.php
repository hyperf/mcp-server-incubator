<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\McpServer\Stubs;

use Hyperf\Redis\RedisProxy;

class MockRedisProxy extends RedisProxy
{
    private array $data = [];

    private array $expiries = [];

    public function __construct()
    {
        // Don't call parent constructor to avoid Redis connection
    }

    public function hMSet(string $key, array $data): bool
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        // Convert all values to strings to match Redis behavior
        $stringData = array_map(fn ($value) => (string) $value, $data);
        $this->data[$key] = array_merge($this->data[$key], $stringData);
        return true;
    }

    public function hSet(string $key, string $field, mixed $value): int
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][$field] = (string) $value;
        return 1;
    }

    public function hGet(string $key, string $field): false|string
    {
        return $this->data[$key][$field] ?? false;
    }

    public function hGetAll(string $key): array
    {
        return $this->data[$key] ?? [];
    }

    public function exists(string $key): int
    {
        return isset($this->data[$key]) ? 1 : 0;
    }

    public function expire(string $key, int $seconds): bool
    {
        $this->expiries[$key] = time() + $seconds;
        return true;
    }

    public function ttl(string $key): int
    {
        if (! isset($this->expiries[$key])) {
            return -1; // No expiry set
        }

        $remaining = $this->expiries[$key] - time();
        return $remaining > 0 ? $remaining : -2; // -2 means expired
    }

    public function del(string $key): int
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key], $this->expiries[$key]);

            return 1;
        }
        return 0;
    }

    public function keys(string $pattern): array
    {
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = "/^{$pattern}$/";

        return array_filter(array_keys($this->data), function ($key) use ($pattern) {
            return preg_match($pattern, $key);
        });
    }

    // Helper methods for testing
    public function clearAll(): void
    {
        $this->data = [];
        $this->expiries = [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getExpiries(): array
    {
        return $this->expiries;
    }
}
