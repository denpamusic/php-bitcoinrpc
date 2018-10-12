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
        $this->registerHandler([$this, 'namespaceHandler']);
        $this->registerHandler([$this, 'requestExceptionHandler']);
    }

    /**
     * Handle namespace change.
     *
     * @param \Exception|\Error $exception
     *
     * @return void
     */
    protected function namespaceHandler($exception)
    {
        if ($this->namespace && $exception instanceof ClientException) {
            return $exception->withNamespace($this->namespace);
        }
    }

    /**
     * Handle request exception.
     *
     * @param \Exception|\Error $exception
     *
     * @return void
     */
    protected function requestExceptionHandler($exception)
    {
        if ($exception instanceof RequestException) {
            if (
                $exception->hasResponse() &&
                $exception->getResponse()->hasError()
            ) {
                return new BadRemoteCallException($exception->getResponse());
            }

            return new ConnectionException(
                $exception->getRequest(),
                $exception->getMessage(),
                $exception->getCode()
            );
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
     * @param \Exception|\Error $exception
     *
     * @return void
     */
    public function handle($exception)
    {
        foreach ($this->handlers as $handler) {
            $result = $handler($exception);

            if ($result instanceof Exception) {
                $exception = $result;
            }
        }

        throw $exception;
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
