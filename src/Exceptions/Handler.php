<?php

namespace Denpa\Bitcoin\Exceptions;

use Exception;
use GuzzleHttp\Exception\RequestException;

class Handler
{
    /**
     * Exception namespace.
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * Handler instance.
     *
     * @var static
     */
    protected static $instance = null;

    /**
     * Handler functions array.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Constructs exception handler.
     *
     * @return void
     */
    protected function __construct()
    {
        $this->registerHandler([$this, 'defaultHandler']);
    }

    /**
     * Default handler function.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    protected function defaultHandler(Exception $exception)
    {
        if ($exception instanceof RequestException) {
            if ($exception->hasResponse()) {
                $response = $exception->getResponse();

                if ($response->hasError()) {
                    return new BadRemoteCallException($response);
                }
            }

            return new ConnectionException(
                $exception->getRequest(),
                $exception->getMessage(),
                $exception->getCode()
            );
        }

        if ($this->namespace && $exception instanceof ClientException) {
            return $exception->withNamespace($this->namespace);
        }
    }

    /**
     * Registers new handler function.
     *
     * @param callable $handler
     *
     * @return static
     */
    public function registerHandler(callable $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Handles exception.
     *
     * @param \Exception $exception
     * @param bool       $throw
     *
     * @return void
     */
    public function handle(Exception $exception, $throw = true)
    {
        try {
            foreach ($this->handlers as $handler) {
                $result = $handler($exception);

                if ($result instanceof Exception) {
                    $exception = $result;
                } elseif ($result === false) {
                    return;
                }
            }

            throw $exception;
        } catch (Exception $exception) {
            if (!$throw) {
                return $exception;
            }

            throw $exception;
        }
    }

    /**
     * Sets exception namespace.
     *
     * @param string $namespace
     *
     * @return static
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Gets handler instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Clears instance.
     *
     * @return void
     */
    public static function clearInstance()
    {
        self::$instance = null;
    }
}
