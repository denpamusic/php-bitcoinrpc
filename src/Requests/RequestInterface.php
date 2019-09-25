<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Request;

use Denpa\Bitcoin\Client;

interface RequestInterface
{
    /**
     * Request method.
     *
     * @var string
     */
    public $method;

    /**
     * Request id.
     *
     * @var int
     */
    public $id = 0;

    /**
     * Gets request parameters.
     *
     * @return array
     */
    public function getParams() : array;

    /**
     * Sets request parameters.
     *
     * @param mixed $params,...
     *
     * @return self
     */
    public function setParams(...$params);

    /**
     * Assigns client to the request.
     *
     * @param \Denpa\Bitcoin\Client $client
     *
     * @return self
     */
    public function assign(Client $client);

    /**
     * Serializes request.
     *
     * @return array
     */
    public function serialize() : array;

    /**
     * Gets request options.
     *
     * @return array
     */
    public function options() : array;
}
