<?php

namespace Denpa\Bitcoin\Traits;

trait SerializableContainer
{
    /**
     * Gets container.
     *
     * @return array
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the string representation of the object.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->container);
    }

    /**
     * Constructs object from serialized string.
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->container = unserialize($serialized);
    }

    /**
     * Serializes the object to a value that can be serialized by json_encode().
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->container;
    }
}
