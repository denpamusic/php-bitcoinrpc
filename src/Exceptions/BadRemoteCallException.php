<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Exceptions;

use Psr\Http\Message\ResponseInterface;

class BadRemoteCallException extends ClientException
{
    /**
     * Response instance
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Constructs new bad remote call exception
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return void
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct($response->error['message'], $response->error['code']);
    }

    /**
     * Gets response object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns array of parameters
     *
     * @return array
     */
    protected function getConstructorParameters() : array
    {
        return [
            $this->getResponse(),
        ];
    }
}
