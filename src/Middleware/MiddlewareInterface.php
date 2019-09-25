<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Middleware;

use Denpa\Bitcoin\ConfigInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * Creates middleware instance
     *
     * @param \Denpa\Bitcoin\ConfigInterface $config
     *
     * @return callable
     */
    public static function middleware(ConfigInterface $config) : callable;

    /**
     * Handles middleware calls
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function __invoke(RequestInterface $request, array $options) : RequestInterface;

    /**
     * Handles requests
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function handleRequest(RequestInterface $request, array $options) : RequestInterface;

    /**
     * Handles responses
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleResponse(ResponseInterface $response) : ResponseInterface;
}