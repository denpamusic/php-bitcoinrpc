<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Exceptions;

use Throwable;
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
     * @var self
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
     * @param \Throwable $exception
     *
     * @return \Throwable|null
     */
    protected function namespaceHandler(Throwable $exception) : ?Throwable
    {
        if ($this->namespace && $exception instanceof ClientException) {
            return $exception->withNamespace($this->namespace);
        }

        return null;
    }

    /**
     * Handle request exception.
     *
     * @param \Throwable $exception
     *
     * @return \Throwable|null
     */
    protected function requestExceptionHandler(Throwable $exception) : ?Throwable
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

        return null;
    }

    /**
     * Registers new handler function.
     *
     * @param callable $handler
     *
     * @return self
     */
    public function registerHandler(callable $handler) : self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Handles exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function handle(Throwable $exception) : void
    {
        foreach ($this->handlers as $handler) {
            $result = $handler($exception);

            if ($result instanceof Throwable) {
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
     * @return self
     */
    public function setNamespace($namespace) : self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Gets handler instance.
     *
     * @return self
     */
    public static function getInstance() : self
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
    public static function clearInstance() : void
    {
        self::$instance = null;
    }
}
