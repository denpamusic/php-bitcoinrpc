<?php

namespace Denpa\Bitcoin\Exceptions;

use Exception;

abstract class ClientException extends Exception
{
    /**
     * Returns class name in provided namespace.
     *
     * @param string $namespace
     *
     * @return \Exception
     */
    protected function withNamespace($namespace)
    {
        $classname = basename(static::class);

        $class = $namespace."\\$classname";

        if (!class_exists($class)) {
            class_alias(static::class, $class);
        }

        return new $class(...$this->getConstructorParameters());
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    protected function getConstructorParameters()
    {
        return [];
    }
}
