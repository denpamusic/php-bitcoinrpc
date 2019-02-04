<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use Denpa\Bitcoin\Exceptions\BadRemoteCallException;
use Denpa\Bitcoin\Requests\Request;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Client
{
    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Client configuration.
     *
     * @var \Denpa\Bitcoin\Config
     */
    protected $config;

    /**
     * Array of GuzzleHttp promises.
     *
     * @var array
     */
    protected $promises = [];

    /**
     * URL path.
     *
     * @var string
     */
    protected $path = '/';

    /**
     * JSON-RPC Id.
     *
     * @var int
     */
    protected $rpcId = 0;

    /**
     * Constructs new client.
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

        // init configuration
        $provider = $this->getConfigProvider();
        $this->config = new $provider($config);

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri' => $this->config->getDsn(),
            'auth'     => $this->config->getAuth(),
            'verify'   => $this->config->getCa(),
            'handler'  => $this->getHandler(),
        ]);
    }

    /**
     * Wait for all promises on object destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->wait();
    }

    /**
     * Gets client config.
     *
     * @return \Denpa\Bitcoin\Config
     */
    public function getConfig() : Config
    {
        return $this->config;
    }

    /**
     * Gets http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient() : ClientInterface
    {
        return $this->client;
    }

    /**
     * Sets http client.
     *
     * @param  \GuzzleHttp\ClientInterface
     *
     * @return self
     */
    public function setClient(ClientInterface $client) : self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Sets wallet for multi-wallet rpc request.
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
     * Makes request to Bitcoin Core.
     *
     * @param \Denpa\Bitcoin\Requests\Request $requests,...
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(...$requests) : ResponseInterface
    {
        try {
            $json = $this->makeJson(...$requests);
            $response = $this->client->post($this->path, $json);

            if ($response->hasError()) {
                // throw exception on error
                throw new BadRemoteCallException($response);
            }

            return $response;
        } catch (Throwable $exception) {
            throw exception()->handle($exception);
        }
    }

    /**
     * Makes async request to Bitcoin Core.
     *
     * @param \Denpa\Bitcoin\Requests\Request $requests,...
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(...$requests) : PromiseInterface
    {
        $json = $this->makeJson(...$requests);
        $promise = $this->client->postAsync($this->path, $json);

        $promise = new PromiseWrapper($promise);

        $this->promises[] = $promise;

        return $promise;
    }

    /**
     * Settle all promises.
     *
     * @return void
     */
    public function wait() : void
    {
        if (!empty($this->promises)) {
            Promise\settle($this->promises)->wait();
        }
    }

    /**
     * Makes request to Bitcoin Core.
     *
     * @param string $method
     * @param array  $params
     *
     * @return \GuzzleHttp\Promise\Promise|\Psr\Http\Message\ResponseInterface
     */
    public function __call(string $method, array $params = [])
    {
        if (strtolower(substr($method, -5)) == 'async') {
            return $this->sendAsync(
                new Request(substr($method, 0, -5), ...$params)
            );
        }

        return $this->send(new Request($method, ...$params));
    }

    /**
     * Get JSON-RPC id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->rpcId++;
    }

    /**
     * Gets config provider class name.
     *
     * @return string
     */
    protected function getConfigProvider() : string
    {
        return 'Denpa\\Bitcoin\\Config';
    }

    /**
     * Gets response handler class name.
     *
     * @return string
     */
    protected function getResponseHandler() : string
    {
        return 'Denpa\\Bitcoin\\Responses\\BitcoindResponse';
    }

    /**
     * Gets Guzzle handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandler() : HandlerStack
    {
        $stack = HandlerStack::create();

        $stack->push(
            Middleware::mapResponse(function (ResponseInterface $response) {
                $handler = $this->getResponseHandler();

                return new $handler($response);
            }),
            'bitcoind_response'
        );

        return $stack;
    }

    /**
     * Gets json array.
     *
     * @param \Denpa\Bitcoin\Requests\Request $requests,...
     *
     * @return array
     */
    protected function makeJson(...$requests) : array
    {
        $requests = array_map(function ($value) {
            return request_for($value)->serializeFor($this);
        }, $requests);

        return ['json' => count($requests) > 1 ? $requests : $requests[0]];
    }
}
