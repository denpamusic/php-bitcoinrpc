<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

trait SerializableContainer
{
    /**
     * Returns array containing all the necessary state of the object.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'container' => $this->toContainer(),
        ];
    }

    /**
     * Restores the object state from the given data array.
     *
     * @param array $serialized
     */
    public function __unserialize(array $serialized)
    {
        $this->container = $serialized['container'];
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
