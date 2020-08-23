<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

trait SerializableContainer
{
    /**
     * Returns the string representation of the object.
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->toContainer());
    }

    /**
     * Constructs object from serialized string.
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        $this->container = unserialize($serialized);
    }

    /**
     * Serializes the object to a value that can be serialized by json_encode().
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toContainer();
    }
}
