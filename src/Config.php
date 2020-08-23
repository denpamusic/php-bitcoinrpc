<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use Denpa\Bitcoin\Traits\Collection;
use Denpa\Bitcoin\Traits\ImmutableArray;

class Config implements \ArrayAccess, \Countable
{
    use Collection;
    use ImmutableArray;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $config = [
        'scheme'        => 'http',
        'host'          => '127.0.0.1',
        'port'          => 8332,
        'user'          => null,
        'password'      => null,
        'ca'            => null,
        'timeout'       => false,
        'preserve_case' => false,
    ];

    /**
     * Constructs new configuration.
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->set($config);
    }

    /**
     * Gets CA file from config.
     *
     * @return string|null
     */
    public function getCa(): ?string
    {
        if (isset($this->config['ca']) && is_file($this->config['ca'])) {
            return $this->config['ca'];
        }

        return null;
    }

    /**
     * Gets authentication array.
     *
     * @return array
     */
    public function getAuth(): array
    {
        return [
            $this->config['user'],
            $this->config['password'],
        ];
    }

    /**
     * Gets DSN string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        $scheme = $this->config['scheme'] ?? 'http';

        return $scheme.'://'.
            $this->config['host'].':'.
            $this->config['port'];
    }

    /**
     * Merge config.
     *
     * @param array $config
     *
     * @return self
     */
    public function set(array $config = []): self
    {
        // use same var name as laravel-bitcoinrpc
        $config['password'] = $config['password'] ?? $config['pass'] ?? null;

        if (is_null($config['password'])) {
            // use default value from getDefaultConfig()
            unset($config['password']);
        }

        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Gets config as array.
     *
     * @return array
     */
    protected function toArray(): array
    {
        return $this->config;
    }
}
