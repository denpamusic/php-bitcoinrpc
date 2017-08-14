<?php

namespace Denpa\Bitcoin;

trait ReadOnlyArrayTrait
{
    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new Exceptions\ClientException('Cannot modify readonly object');
    }

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->result()[$offset]);
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
        throw new Exceptions\ClientException('Cannot modify readonly object');
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
        return isset($this->result()[$offset]) ?
            $this->result()[$offset] : null;
    }
}
