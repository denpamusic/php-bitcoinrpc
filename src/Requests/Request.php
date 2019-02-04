<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Requests;

use Denpa\Bitcoin\Client;

class Request
{
    /**
     * Request method.
     *
     * @var array
     */
    protected $method;

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $params;

    /**
     * Constructs new request.
     *
     * @param string  $method
     * @param mixed   $params,...
     *
     * @return void
     */
    public function __construct(string $method, ...$params)
    {
        $this->method = $method;
        $this->params = (array) $params;
    }

    /**
     * Gets request method.
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Gets request parameters.
     *
     * @return array
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * Sets request parameters.
     *
     * @param mixed $params,...
     *
     * @return array
     */
    public function setParams(...$params) : void
    {
        $this->params = (array) $params;
    }

    /**
     * Serializes request as array for client.
     *
     * @param \Denpa\Bitcoin\Client $client
     *
     * @return array
     */
    public function serializeFor(Client $client) : array
    {
        return [
            'method' => $client->getConfig()['preserve_case'] ?
                $this->method : strtolower($this->method),
            'params' => $this->params,
            'id'     => $client->getId(),
        ];
    }
}
