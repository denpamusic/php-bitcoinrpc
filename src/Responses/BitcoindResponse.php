<?php

namespace Denpa\Bitcoin\Responses;

use Denpa\Bitcoin\Traits\Collection;
use Denpa\Bitcoin\Traits\ReadOnlyArray;
use Denpa\Bitcoin\Traits\SerializableContainer;

class BitcoindResponse extends Response implements
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use Collection, ReadOnlyArray, SerializableContainer;
}
