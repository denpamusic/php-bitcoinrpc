<?php

declare(strict_types=1);

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
    public function withNamespace($namespace): Exception
    {
        $classname = $this->getClassName();

        $class = $namespace."\\$classname";

        if (class_exists($class)) {
            return new $class(...$this->getConstructorParameters());
        }

        return $this;
    }

    /**
     * Gets exception class name.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        $pos = ($pos = strrpos(static::class, '\\')) !== false ? $pos + 1 : 0;

        return substr(static::class, $pos);
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    abstract protected function getConstructorParameters();
}
