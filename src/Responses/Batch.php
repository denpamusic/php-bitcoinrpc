<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Responses;

use ArrayAccess;
use Countable;
use Denpa\Bitcoin\Traits\Message;
use Denpa\Traits\Collection;
use Psr\Http\Message\ResponseInterface;

class Batch implements ResponseInterface, Countable, ArrayAccess
{
    use Message, Collection;

    /**
     * Response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response = null;

    /**
     * @param ResponseInterface $response
     * @param string            $handler
     *
     * @return void
     */
    public function __constuct($response, string $handler)
    {
        $responses = json_decode($response->getBody()->__toString(), true);

        $this->collect($responses)->each(function (&$response) {
            $response = new $handler($response);
        });

        $this->response = $response;
    }
}
