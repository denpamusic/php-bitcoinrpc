<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use Throwable;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    /**
     * Client configuration
     *
     * @var \Denpa\Bitcoin\Config
     */
    protected $config;

    /**
     * Configuration provider class name
     *
     * @var string
     */
    protected $configProvider = 'Denpa\\Bitcoin\\Config';

    /**
     * json-rpc request id
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Array of GuzzleHttp promises
     *
     * @var array
     */
    protected $promises = [];

    /**
     * Constructs new client instance
     *
     * @param array|string $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        if (is_string($config)) {
            $config = split_url($config);
        }

        $this->config = new $this->configProvider($config);
    }

    /**
     * Waits for all promises to resolve
     *
     * @return void
     */
    public function __destruct()
    {
        $this->wait();
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function id() : int
    {
        return $this->id++;
    }

    /**
     * {@inheritdoc}
     *
     * @param array|null $config
     *
     * @return \Denpa\Bitcoin\Config
     */
    public function config(?array $config = null) : Config
    {
        if (!is_null($config)) {
            $this->config->set($config);
        }

        return $this->config;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     *
     * @return self
     */
    public function wallet(string $name) : self
    {
        $this->path = "/wallet/$name";

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $requests,...
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(...$requests) : ResponseInterface
    {
        $result = null;

        $success = function (ResponseInterface $response) use (&$result) {
            if ($response->error) {
                throw new BadRemoteCallException($response);
            }

            $result = $response;
        };

        $failure = function (Throwable $exception) {
            throw $exception;
        };

        try {
            $this->sendAsync(...$requests)->then($success, $failure)->wait();
        } catch (Throwable $exception) {
            throw exception()->handle($exception);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $requests,...
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(...$requests) : PromiseInterface
    {
        $handler = $this->config->get(
            'request.handler', 'Denpa\\Bitcoin\\Requests\\Request'
        );

        $request = request_for($requests, $handler)->assign($this);

        $promise = $this
            ->guzzle()
            ->postAsync(
                $this->path,
                ['json' => $request->serialize()],
                $request->options()
            );

        $promises[] = new PromiseWrapper($promise);

        return $promise;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function wait() : void
    {
        Promise\settle($this->promises)->wait();
    }

    /**
     * Makes json-rpc request via magic call
     *
     * @param string $method
     * @param array  $params
     *
     * @return \GuzzleHttp\Promise\Promise|\Psr\Http\Message\ResponseInterface
     */
    public function __call(string $method, array $params = [])
    {
        if (strtolower(substr($method, -5)) == 'async') {
            return $this->sendAsync([substr($method, 0, -5) => $params]);
        }

        return $this->send([$method => $params]);
    }

    /**
     * Gets guzzle client instance
     *
     * @return \GuzzleHttp\Client
     */
    protected function guzzle() : GuzzleHttp
    {
        static $guzzle = null;

        if (!$guzzle || $this->config->changed) {
            $guzzle = new GuzzleHttp($this->config->serialize());
            $this->config->changed = false;
        }

        return $guzzle
    }
}
