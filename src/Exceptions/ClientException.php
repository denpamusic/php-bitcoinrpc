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
        $classname = $this->getBasename();

        $class = $namespace."\\$classname";

        if (!class_exists($class)) {
            class_alias(static::class, $class);
        }

        return new $class(...$this->getConstructorParameters());
    }

    /**
     * Gets exception basename.
     *
     * @return string
     */
    protected function getBasename()
    {
        $pos = ($pos = strrpos(static::class, '\\')) !== false ? $pos + 1 : 0;
        return substr(static::class, $pos);
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
