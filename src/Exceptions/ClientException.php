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
    public function withNamespace($namespace)
    {
        $classname = $this->getBasename();

        $class = $namespace."\\$classname";

        if (class_exists($class)) {
            return new $class(...$this->getConstructorParameters());
        }

        return $this;
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
