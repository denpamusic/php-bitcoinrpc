<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Responses;

use ArrayAccess;
use Countable;
use Denpa\Bitcoin\Traits\Message;
use Denpa\Traits\Collection;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface, Countable, ArrayAccess
{
    use Message, Collection;

    /**
     * Response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response = null;

    /**
     * Error message.
     *
     * @var string
     */
    public $error;

    /**
     * Constructs new json response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return void
     */
    public function __construct($response)
    {
        if ($response instanceof ResponseInterface) {
            $this->response = $response;

            $response = json_decode($response->getBody()->__toString(), true);
        }

        if (isset($response['error'])) {
            $this->error = $response['error'];
        }

        if (isset($response['result'])) {
            $this->collect($response['result']);
        }
    }

    /**
     * Gets or sets psr response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response(?ResponseInterface $response = null) : ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        }

        return $this->response;
    }
}
