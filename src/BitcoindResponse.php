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
    use MessageTrait;

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
     * Gets data by using key with dotted notation.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->result();
        }

        $parts = explode('.', $key);
        $result = $this->result();

        foreach ($parts as $part) {
            if (!$result || !isset($result[$part])) {
                return;
            }

            $result = $result[$part];
        }

        return $result;
    }

    /**
     * Checks if key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->result());
    }

    /**
     * Checks if key exists and not null.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->result()[$key]);
    }

    /**
     * Checks if response contains value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        return in_array($value, $this->result());
    }

    /**
     * Gets response keys.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->result());
    }

    /**
     * Gets response values.
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->result());
    }

    /**
     * Counts response items.
     *
     * @return int
     */
    public function count()
    {
        return count($this->result());
    }

    /**
     * Get response item by key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function __invoke($key = null)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new ClientException('Cannot modify json response object');
    }

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->result()[$offset]);
    }

    /**
     * Unsets the offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new ClientException('Cannot modify json response object');
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->result()[$offset]) ?
            $this->result()[$offset] : null;
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
        $this->container = unserialize($this->container);
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
