<?php

namespace Denpa\Bitcoin\Traits;

use Psr\Http\Message\StreamInterface;

trait Message
{
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
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
    public function getHeaders()
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
    public function hasHeader($name)
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
    public function getHeader($name)
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
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * Returns an instance with the provided value replacing the specified header.
     *
     * @param string       $name
     * @param string|array $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;

        return $new->setResponse($this->response->withHeader($name, $value));
    }

    /**
     * Returns an instance with the specified header appended with the given value.
     *
     * @param string|array $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;

        return $new->setResponse($this->response->withAddedHeader($name, $value));
    }

    /**
     * Returns an instance without the specified header.
     *
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        $new = clone $this;

        return $new->setResponse($this->response->withoutHeader($name));
    }

    /**
     * Gets the body of the message.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * Returns an instance with the specified message body.
     *
     * @param \Psr\Http\Message\StreamInterface $body
     *
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;

        return $new->setResponse($this->response->withBody($body));
    }
}
