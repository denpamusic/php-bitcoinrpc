<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BatchHeader extends Middleware
{
    /**
     * Request options
     *
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function handleRequest(RequestInterface $request, array $options) : RequestInterface
    {
        $this->options = $options;

        return $request;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleResponse(ResponseInterface $response) : ResponseInterface
    {
        if (isset($this->options['meta']) && is_array($this->options['meta'])) {
            foreach($options['meta'] as $key => $value) {
                $response = $response->withAddedHeader('X-Meta-'.$key, $value);
            }
        }

        return $response;
    }
}
