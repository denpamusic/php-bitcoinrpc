<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Requests;

use Countable;
use ArrayAccess;
use Denpa\Traits\Collection;

class Batch implements Countable, ArrayAccess, RequestInterface
{
    use Collection;

    /**
     * @param array $requests
     * @param string $handler
     *
     * @return void
     */
    public function __constuct(array $requests, string $handler)
    {
        $this->collect($requests)->each(function (&$request) {
            $request = new $handler(...$request);
        });
    }

    /**
     * Gets request parameters
     *
     * @return array
     */
    public function getParams() : array
    {
        $params = [];

        $this->each(function ($request) use (&$params) {
            $params[] = $request->getParams();
        });

        return $params;
    }

    /**
     * Sets request parameters
     *
     * @param mixed $params,...
     *
     * @return self
     */
    public function setParams(...$params) : self
    {
        return $this->each(function (&$request) use ($params) {
            $request->setParams($params);
        });
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
        $this->each(function (&$request) use ($client) {
            $request->assign($client);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function serialize() : array
    {
        $this->each(function (&$request) {
            $request = $request->serialize();
        });

        return $this->all();
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
                'Batch' => 1,
            ],
        ];
    }
}
