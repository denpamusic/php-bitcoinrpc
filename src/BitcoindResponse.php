<?php

namespace Denpa\Bitcoin;

use Psr\Http\Message\ResponseInterface;

class BitcoindResponse implements
    ResponseInterface,
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use MessageTrait, ResponseArrayTrait, ReadOnlyArrayTrait;

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
        $this->container = json_decode($response->getBody(), true);
    }

    /**
     * Gets raw response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Checks if response has error.
     *
     * @return bool
     */
    public function hasError()
    {
        return isset($this->container['error']);
    }

    /**
     * Gets error object.
     *
     * @return object|null
     */
    public function error()
    {
        if ($this->hasError()) {
            return $this->container['error'];
        }
    }

    /**
     * Checks if response has result.
     *
     * @return bool
     */
    public function hasResult()
    {
        return isset($this->container['result']);
    }

    /**
     * Gets result array.
     *
     * @return array|null
     */
    public function result()
    {
        if ($this->hasResult()) {
            return $this->container['result'];
        }
    }

    /**
     * Returns the string representation of the object.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->container);
    }

    /**
     * Constructs object from serialized string.
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->container = unserialize($serialized);
    }

    /**
     * Serializes the object to a value that can be serialized by json_encode().
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->container;
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * Creates new json response from response interface object.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Denpa\Bitcoin\BitcoindResponse
     */
    public static function createFrom(ResponseInterface $response)
    {
        return new self($response);
    }
}
