<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

use BadMethodCallException;

trait ReadOnlyArray
{
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
        throw new BadMethodCallException('Cannot modify readonly object');
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
        return isset($this->result()[$offset]);
    }

    /**
     * Unsets the offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        throw new BadMethodCallException('Cannot modify readonly object');
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
        return $this->result()[$offset] ?? null;
    }
}
