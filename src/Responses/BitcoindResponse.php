<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Responses;

use Denpa\Bitcoin\Traits\Collection;
use Denpa\Bitcoin\Traits\ImmutableArray;
use Denpa\Bitcoin\Traits\SerializableContainer;

class BitcoindResponse extends Response implements
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use Collection;
    use ImmutableArray;
    use SerializableContainer;

    /**
     * Gets array representation of response object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this->result();
    }

    /**
     * Gets root container of response object.
     *
     * @return array
     */
    public function toContainer(): array
    {
        return $this->container;
    }
}
