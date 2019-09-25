<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Client path.
     *
     * @var string
     */
    public $path = '/';

    /**
     * Gets client id.
     *
     * @return int
     */
    public function id() : int;

    /**
     * Gets or sets client configuration.
     *
     * @param array|null $config
     *
     * @return \Denpa\Bitcoin\Config
     */
    public function config(?array $config = null) : Config;

    /**
     * Sets wallet.
     *
     * @param string $name
     *
     * @return self
     */
    public function wallet(string $name);

    /**
     * Sends request.
     *
     * @param mixed $requests,...
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(...$requests) : ResponseInterface;

    /**
     * Sends asynchronous request.
     *
     * @param mixed $requests,...
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(...$requests) : PromiseInterface;

    /**
     * Waits on promises to complete.
     *
     * @return void
     */
    public function wait() : void;
}
