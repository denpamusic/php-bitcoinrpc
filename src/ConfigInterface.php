<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

interface ConfigInterface
{
    /**
     * Serializes config
     *
     * @return array
     */
    public function serialize() : array;
}
