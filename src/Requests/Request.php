<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Requests;

use Denpa\Bitcoin\Client;

class Request implements RequestInterface
{
    /**
     * Request parameters.
     *
     * @var array
     */
    protected $params;

    /**
     * {@inheritdoc}
     *
     * @param string $method
     * @param mixed  $params,...
     *
     * @return void
     */
    public function __construct(string $method, ...$params)
    {
        $this->method = $method;
        $this->params = (array) $params;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @param \Denpa\Bitcoin\Client $client
     *
     * @return self
     */
    public function assign(Client $client) : self
    {
        $this->id = $client->id();

        if ($client->config()->get('preserve_case', false)) {
            $this->method = strtolower($this->method);
        }

        return self
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function serialize() : array
    {
        return [
            'jsonrpc' => '2.0',
            'method'  => $this->method,
            'params'  => $this->params,
            'id'      => $this->id,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function options() : array
    {
        return [
            'meta' => [
                'Batch' => 0,
            ],
        ];
    }
}
