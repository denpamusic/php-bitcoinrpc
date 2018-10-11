<?php

namespace Denpa\Bitcoin\Exceptions;

class BadConfigurationException extends ClientException
{
    /**
     * Configuration container.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructs new bad configuration exception.
     *
     * @param array $config
     * @param mixed $args,...
     *
     * @return void
     */
    public function __construct(array $config, ...$args)
    {
        $this->config = $config;

        parent::__construct(...$args);
    }

    /**
     * Gets config data.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns array of parameters.
     *
     * @return array
     */
    protected function getConstructorParameters()
    {
        return [
            $this->getConfig(),
            $this->getMessage(),
            $this->getCode(),
        ];
    }
}
