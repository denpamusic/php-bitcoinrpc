<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Middleware;

use Denpa\Bitcoin\ConfigInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Middleware implements MiddlewareInterface
{
    /**
     * Next handler.
     *
     * @var callable
     */
    protected $next;

    /**
     * Client configuration.
     *
     * @var \Denpa\Bitcoin\ConfigInterface
     */
    protected $config;

    /**
     * @param callable                       $handler
     * @param \Denpa\Bitcoin\ConfigInterface $config
     *
     * @return void
     */
    public function __construct(callable $handler, ConfigInterface $config)
    {
        $this->next = $handler;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Denpa\Bitcoin\ConfigInterface
     *
     * @return callable
     */
    public static function middleware(ConfigInterface $config) : callable
    {
        return function (callable $handler) use (&$config) {
            return new static($handler, $config);
        };
    }

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function __invoke(RequestInterface $request, array $options) : RequestInterface
    {
        $request = $this->requestHandler($request, $options);
        $success = [$this, 'responseHandler'];

        return ($this->next)($request, $options)->then($success);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function handleRequest(RequestInterface $request, array $options) : RequestInterface
    {
        return $request;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleResponse(ResponseInterface $response) : ResponseInterface
    {
        return $response;
    }
}
