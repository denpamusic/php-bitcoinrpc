<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

use Psr\Http\Message\StreamInterface;

trait Message
{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return float
     */
    public function getProtocolVersion(): float
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param float|string $version
     *
     * @return self
     */
    public function withProtocolVersion($version): self
    {
        $new = clone $this;

        return $new->setResponse(
            $this->response->withProtocolVersion($version)
        );
    }

    /**
     * Retrieves all message header values.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * Returns an instance with the provided value replacing the specified header.
     *
     * @param string       $name
     * @param string|array $value
     *
     * @return self
     */
    public function withHeader($name, $value): self
    {
        $new = clone $this;

        return $new->setResponse($this->response->withHeader($name, $value));
    }

    /**
     * Returns an instance with the specified header appended with the given value.
     *
     * @param string       $name
     * @param string|array $value
     *
     * @return self
     */
    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;

        return $new->setResponse($this->response->withAddedHeader($name, $value));
    }

    /**
     * Returns an instance without the specified header.
     *
     * @param string $name
     *
     * @return self
     */
    public function withoutHeader($name): self
    {
        $new = clone $this;

        return $new->setResponse($this->response->withoutHeader($name));
    }

    /**
     * Gets the body of the message.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    /**
     * Returns an instance with the specified message body.
     *
     * @param \Psr\Http\Message\StreamInterface $body
     *
     * @return self
     */
    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;

        return $new->setResponse($this->response->withBody($body));
    }
}
