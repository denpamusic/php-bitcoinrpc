<?php

namespace Denpa\Bitcoin\Exception;

use Exception;

class Handler
{
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
     * @param bool       $return
     *
     * @return void
     */
    public function handle(Exception $exception, $return = false)
    {
        try {
            $handlers = array_reverse($this->handlers);

            foreach ($handlers as $handler) {
                if ($handler($exception) === false) {
                    return;
                }
            }

            throw $exception;
        } catch (Exception $exception) {
            if ($return) {
                return $exception;
            }

            throw $exception;
        }
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
}
