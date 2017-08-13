<?php

namespace Denpa\Bitcoin\Exceptions;

use RuntimeException;

class BitcoindException extends RuntimeException
{
    /**
     * Construct new bitcoind exception.
     *
     * @param object $error
     *
     * @return void
     */
    public function __construct($error)
    {
        parent::__construct($error['message'], $error['code']);
    }
}
