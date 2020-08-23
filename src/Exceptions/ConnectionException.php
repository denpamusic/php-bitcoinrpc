<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Exceptions;

use GuzzleHttp\Psr7\Request;

class ConnectionException extends ClientException
{
    /**
     * Request object.
     *
     * @var \GuzzleHttp\Psr7\Request
     */
    protected $request;

    /**
     * Constructs new connection exception.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     * @param mixed                    $args,...
     *
     * @return void
     */
    public function __construct(Request $request, ...$args)
    {
        $this->request = $request;

        parent::__construct(...$args);
    }

    /**
     * Gets request object.
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    protected function getConstructorParameters(): array
    {
        return [
            $this->getRequest(),
            $this->getMessage(),
            $this->getCode(),
        ];
    }
}
