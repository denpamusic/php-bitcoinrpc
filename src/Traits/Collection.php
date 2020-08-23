<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

use InvalidArgumentException;

trait Collection
{
    /**
     * Current key.
     *
     * @var string
     */
    protected $current;

    /**
     * Gets data by using key with dotted notation.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function get(?string $key = null)
    {
        $key = $this->constructKey($key);

        if (is_null($key)) {
            $array = $this->toArray();

            return $this->count() == 1 ? end($array) : $array;
        }

        return $this->parseKey($key, function ($part, $result) {
            if (isset($result[$part])) {
                return $result[$part];
            }
        });
    }

    /**
     * Checks if key exists.
     *
     * @param string|null $key
     *
     * @return bool
     */
    public function exists(?string $key = null): bool
    {
        $key = $this->constructKey($key);

        return $this->parseKey($key, function ($part, $result) {
            return array_key_exists($part, $result);
        });
    }

    /**
     * Checks if key exists and not null.
     *
     * @param string|null $key
     *
     * @return bool
     */
    public function has(?string $key = null): bool
    {
        $key = $this->constructKey($key);

        return $this->parseKey($key, function ($part, $result) {
            return isset($result[$part]);
        });
    }

    /**
     * Gets first element.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function first(?string $key = null)
    {
        $value = $this->get($key);

        if (is_array($value)) {
            return reset($value);
        }

        return $value;
    }

    /**
     * Gets last element.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function last(?string $key = null)
    {
        $value = $this->get($key);

        if (is_array($value)) {
            return end($value);
        }

        return $value;
    }

    /**
     * Checks if response contains value.
     *
     * @param mixed       $needle
     * @param string|null $key
     *
     * @return bool
     */
    public function contains($needle, ?string $key = null): bool
    {
        $value = $this->get($key);

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'method contains() should be called on array'
            );
        }

        return in_array($needle, $value);
    }

    /**
     * Sets current key.
     *
     * @param string|null $key
     *
     * @return self
     */
    public function key(?string $key = null): self
    {
        $new = clone $this;
        $new->current = $key;

        return $new;
    }

    /**
     * Gets response keys.
     *
     * @param string|null $key
     *
     * @return array
     */
    public function keys(?string $key = null): array
    {
        $value = $this->get($key);

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'method keys() should be called on array'
            );
        }

        return array_keys($value);
    }

    /**
     * Gets response values.
     *
     * @param string|null $key
     *
     * @return array
     */
    public function values(?string $key = null): array
    {
        $value = $this->get($key);

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'method values() should be called on array'
            );
        }

        return array_values($value);
    }

    /**
     * Gets random value.
     *
     * @param int         $number
     * @param string|null $key
     *
     * @return mixed
     */
    public function random(int $number = 1, ?string $key = null)
    {
        $value = $this->get($key);

        if (is_array($value)) {
            $keys = array_keys($value);
            $keysLength = count($keys);

            shuffle($keys);

            if ($number > $keysLength) {
                $number = $keysLength;
            }

            for ($result = [], $count = 0; $count < $number; $count++) {
                $result[$keys[$count]] = $value[$keys[$count]];
            }

            return count($result) > 1 ? $result : current($result);
        }

        return $value;
    }

    /**
     * Counts response items.
     *
     * @param string|null $key
     *
     * @return int
     */
    public function count(?string $key = null): int
    {
        if (is_null($this->constructKey($key))) {
            return count($this->toArray());
        }

        $value = $this->get($key);

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'method count() should be called on array'
            );
        }

        return count($value);
    }

    /**
     * Flattens multi-dimensional array.
     *
     * @param string|null $key
     *
     * @return array
     */
    public function flatten(?string $key = null): array
    {
        $array = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator((array) $this->get($key))
        );

        $tmp = [];
        foreach ($array as $value) {
            $tmp[] = $value;
        }

        return $tmp;
    }

    /**
     * Gets sum of values.
     *
     * @param string|null $key
     *
     * @return float
     */
    public function sum(?string $key = null): float
    {
        return array_sum($this->flatten($key));
    }

    /**
     * Gets response item by key.
     *
     * @param string|null $key
     *
     * @return self
     */
    public function __invoke(?string $key = null): self
    {
        return $this->key($key);
    }

    /**
     * Converts response to string on current key.
     *
     * @return string
     */
    public function __toString(): string
    {
        $value = $this->get();

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Constructs full key.
     *
     * @param string|null $key
     *
     * @return string|null
     */
    protected function constructKey(?string $key = null): ?string
    {
        if (!is_null($key) && !is_null($this->current)) {
            return $this->current.'.'.$key;
        }

        if (is_null($key) && !is_null($this->current)) {
            return $this->current;
        }

        return $key;
    }

    /**
     * Parses dotted notation.
     *
     * @param array|string $key
     * @param callable     $callback
     * @param array|null   $result
     *
     * @return mixed
     */
    protected function parseKey($key, callable $callback, ?array $result = null)
    {
        $parts = is_array($key) ? $key : explode('.', trim($key, '.'));
        $result = $result ?: $this->toArray();

        while (!is_null($part = array_shift($parts))) {
            if ($part == '*') {
                array_walk($result, function (&$value) use ($parts, $callback) {
                    $value = $this->parseKey($parts, $callback, $value);
                });

                return $result;
            }

            if (!$return = $callback($part, $result)) {
                return $return;
            }

            $result = $result[$part];
        }

        return $return;
    }
}
