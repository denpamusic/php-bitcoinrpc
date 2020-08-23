<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Responses;

use Denpa\Bitcoin\Traits\Message;
use Psr\Http\Message\ResponseInterface;

abstract class Response implements ResponseInterface
{
    use Message;

    /**
     * Response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Data container.
     *
     * @var array
     */
    protected $container = [];

    /**
     * Constructs new json response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return void
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->container = json_decode((string) $response->getBody(), true);
    }

    /**
     * Gets raw response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Sets response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return self
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Checks if response has error.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return isset($this->container['error']);
    }

    /**
     * Gets error object.
     *
     * @return array|null
     */
    public function error(): ?array
    {
        return $this->container['error'] ?? null;
    }

    /**
     * Checks if response has result.
     *
     * @return bool
     */
    public function hasResult(): bool
    {
        return isset($this->container['result']);
    }

    /**
     * Gets result array.
     *
     * @return mixed
     */
    public function result()
    {
        return $this->container['result'] ?? null;
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return self
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $new = clone $this;

        return $new->setResponse(
            $this->response->withStatus($code, $reasonPhrase)
        );
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }
}
