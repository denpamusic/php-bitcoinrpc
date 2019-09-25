<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use ArrayAccess;
use GuzzleHttp\HandlerStack;
use Denpa\Bitcoin\Traits\Collection;

class Config implements ArrayAccess
{
    use Collection;

    /**
     * Configuration defaults
     *
     * @var array
     */
    protected $defaults = [
        'scheme'        => 'http',
        'host'          => '127.0.0.1',
        'port'          => 8332,
        'user'          => null,
        'password'      => null,
        'ca'            => null,
        'preserve_case' => false,
        'request'       => ['handler' => 'Denpa\\Bitcoin\\Requests\\Request'],
        'response'      => ['handler' => 'Denpa\\Bitcoin\\Responses\\Response'],
    ];

    /**
     * Constructs new configuration
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->collect($this->defaults)->merge($config);

        $this->set('response.middleware', [
            'Denpa\\Bitcoin\\Middleware\\Response',
            'Denpa\\Bitcoin\\Middleware\\BatchHeader',
        ]);
    }

    /**
     * Serializes config
     *
     * @return array
     */
    public function serialize() : array
    {
        return [
            'base_uri' => $this->getBaseUri(),
            'auth'     => $this->getAuth(),
            'verify'   => $this->getCa(),
            'handler'  => $this->getHandler(),
        ];
    }

    /**
     * Gets CA file from config
     *
     * @return string|null
     */
    protected function getCa() : ?string
    {
        if ($this->has('ca') && is_file($this->get('ca'))) {
            return $this->get('ca');
        }

        return null;
    }

    /**
     * Gets authentication array
     *
     * @return array
     */
    protected function getAuth() : array
    {
        return [
            $this->get('user'),
            $this->get('password', $this->get('pass')),
        ];
    }

    /**
     * Gets base uri
     *
     * @return string
     */
    protected function getBaseUri() : string
    {
        return $this->get('scheme', 'http').'://'.
            $this->get('host').':'.
            $this->get('port');
    }

    /**
     * Gets Guzzle handler stack
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandler() : HandlerStack
    {
        $stack = HandlerStack::create();

        foreach ($this->get('response.middleware', []) as $middleware) {
            $stack->push($middleware::middleware($this));
        }

        return $stack;
    }
}
