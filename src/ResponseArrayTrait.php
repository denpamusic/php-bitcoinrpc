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

        $parts = explode('.', $key);
        $result = $this->result();

        foreach ($parts as $part) {
            if (!$result || !isset($result[$part])) {
                return;
            }

            $result = $result[$part];
        }

        return $result;
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
        return array_key_exists($key, $this->result());
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
        return isset($this->result()[$key]);
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
}
