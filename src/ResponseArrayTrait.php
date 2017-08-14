<?php

namespace Denpa\Bitcoin;

trait ResponseArrayTrait
{
    /**
     * Gets data by using key with dotted notation.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->result();
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
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->parseKey($key, function ($part, $result) {
            return array_key_exists($part, $result);
        });
    }

    /**
     * Checks if key exists and not null.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->parseKey($key, function ($part, $result) {
            return isset($result[$part]);
        });
    }

    /**
     * Checks if response contains value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        return in_array($value, $this->result());
    }

    /**
     * Gets response keys.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->result());
    }

    /**
     * Gets response values.
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->result());
    }

    /**
     * Gets random value.
     *
     * @return mixed
     */
    public function random($key = null)
    {
        $values = $this->get($key);

        if (is_array($values)) {
            $key = mt_rand(0, count($values) - 1);

            return $values[$key];
        }

        return $values;
    }

    /**
     * Counts response items.
     *
     * @return int
     */
    public function count()
    {
        return count($this->result());
    }

    /**
     * Get response item by key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function __invoke($key = null)
    {
        return $this->get($key);
    }

    /**
     * Parses dotted notation.
     *
     * @param string   $key
     * @param callable $callback
     *
     * @return mixed
     */
    protected function parseKey($key, callable $callback)
    {
        $parts = explode('.', $key);
        $result = $this->result();

        foreach ($parts as $part) {
            if (!$return = $callback($part, $result)) {
                return $return;
            }

            $result = $result[$part];
        }

        return $return;
    }
}
