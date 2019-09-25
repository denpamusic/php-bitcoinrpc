<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Exceptions;

use Psr\Http\Message\RequestInterface;

class ConnectionException extends ClientException
{
    /**
     * Request object.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Constructs new connection exception.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param mixed                              $args,...
     *
     * @return void
     */
    public function __construct(RequestInterface $request, ...$args)
    {
        $this->request = $request;

        parent::__construct(...$args);
    }

    /**
     * Gets request object.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    protected function getConstructorParameters() : array
    {
        return [
            $this->getRequest(),
            $this->getMessage(),
            $this->getCode(),
        ];
    }
}
