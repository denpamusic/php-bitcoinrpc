<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

use function Denpa\Bitcoin\dot_get;
use function Denpa\Bitcoin\dot_set;
use function Denpa\Bitcoin\dot_delete;

trait Collection
{
    use Serializable;

    /**
     * @var bool
     */
    public $changed = false;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param mixed $items
     *
     * @return self
     */
    public function collect($items) : self
    {
        $this->items = (array) $items;
        return $this;
    }

    /**
     * @return array
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function get(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->items;
        }

        return dot_get($this->items, $key, $default);
    }

    /**
     * @param mixed $value,...
     *
     * @return void
     */
    public function set(...$value) : void
    {
        dot_set($this->items, $this->getKey($value), $value[0]);

        $this->changed = true;
    }

    /**
     * @param mixed $value,...
     *
     * @return void
     */
    public function push(...$value) : void
    {
        $key = $this->getKey($callback);
        $items = dot_get($this->items, $key) ?? [];

        array_push($items, $value);
        dot_set($this->items, $key, $items);

        $this->changed = true;
    }

    /**
     * @param mixed $callback,...
     *
     * @return void
     */
    public function each(...$callback) : void
    {
        $key = $this->getKey($callback);
        $items = dot_get($this->items, $key) ?? [];

        if (!is_array($items)) {
            throw new InvalidArgumentException(
                'method each() should be called on array'
            );
        }

        array_walk($items, $callback[0]);
        dot_set($this->items, $key, $items)

        $this->changed = true;
    }

    /**
     * @param mixed $array,...
     *
     * @return void
     */
    public function merge(...$array) : void
    {
        $key = $this->getKey($array);
        $items = dot_get($this->items, $key);

        if (!is_array($items)) {
            throw new InvalidArgumentException(
                'method merge() should be called on array'
            );
        }

        $result = array_merge($items, ...$array);
        dot_set($this->items, $key, $result);

        $this->changed = true;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        dot_delete($this->items, $key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key) : bool
    {
        dot_get($this->items, $key, null, $exists);
        return $exists;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key) : bool
    {
        return !is_null(dot_get($this->items, $key));
    }

    /**
     * @param string $key
     *
     * @return float
     */
    public function sum(string $key) : float
    {
        return array_sum((array) dot_get($this->items, $key));
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function count(string $key) : int
    {
        return count((array) dot_get($this->items, $key));
    }

    /**
     * @param string $key
     *
     * @return float
     */
    public function avg(string $key) : float
    {
        return $this->sum($key) / $this->count($key);
    }

    /**
     * @param string $key
     *
     * @return float
     */
    public function max(string $key) : float
    {
        return max((array) dot_get($this->items, $key));
    }

    /**
     * @param string $key
     *
     * @return float
     */
    public function min(string $key) : float
    {
        return min((array) dot_get($this->items, $key));
    }

    /**
     * @param string $key
     * @param int    $number
     *
     * @return mixed
     */
    public function random(string $key, int $number = 1)
    {
        $array = (array) dot_get($this->items, $key);
        $result = [];

        foreach ((array) array_rand($array, $number) as $key) {
            $result[] = $array[$key];
        }

        return $this->collapse($result);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->set($value, $offset);
    }

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->exists($offset);
    }

    /**
     * Unsets the offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function getKey(array &$args)
    {
        if (count($args) == 1) {
            // if only one arg, assume that key is not set
            return null;
        }

        return array_shift($args);
    }
}
