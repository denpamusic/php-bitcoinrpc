<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Middleware;

use Psr\Http\Message\ResponseInterface;

class Response extends Middleware
{
    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleResponse(ResponseInterface $response) : ResponseInterface
    {
        $handler = $this->config->get(
            'response.handler', 'Denpa\\Bitcoin\\Responses\\Response'
        );

        return new $handler($response);
    }
}
