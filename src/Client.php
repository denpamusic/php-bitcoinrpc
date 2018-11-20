<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use Denpa\Bitcoin\Exceptions\BadConfigurationException;
use Denpa\Bitcoin\Exceptions\BadRemoteCallException;
use Denpa\Bitcoin\Traits\HandlesAsync;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Client
{
    use HandlesAsync;

    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Client configuration.
     *
     * @var array
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
            $config = $this->parseUrl($config);
        }

        // init defaults
        $this->config = $this->mergeDefaultConfig($config);

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri' => $this->getDsn(),
            'auth'     => $this->getAuth(),
            'verify'   => $this->getCa(),
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
     * @param string|null $option
     *
     * @return mixed
     */
    public function getConfig(?string $option = null)
    {
        if (is_null($option)) {
            return $this->config;
        }
        
        if (array_key_exists($option, $this->config)) {
            return $this->config[$option];
        }

        return null;
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
     * @param string $method
     * @param mixed  $params
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, ...$params) : ResponseInterface
    {
        try {
            $response = $this->client
                ->post($this->path, $this->makeJson($method, $params));

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
     * @param string        $method
     * @param mixed         $params
     * @param callable|null $fulfilled
     * @param callable|null $rejected
     *
     * @return \GuzzleHttp\Promise\Promise
     */
    public function requestAsync(
        string $method,
        $params = [],
        ?callable $fulfilled = null,
        ?callable $rejected = null) : Promise\Promise
    {
        $promise = $this->client
            ->postAsync($this->path, $this->makeJson($method, $params));

        $promise->then(function ($response) use ($fulfilled) {
            $this->onSuccess($response, $fulfilled);
        });

        $promise->otherwise(function ($exception) use ($rejected) {
            try {
                exception()->handle($exception);
            } catch (Throwable $exception) {
                $this->onError($exception, $rejected);
            }
        });

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
            return $this->requestAsync(substr($method, 0, -5), ...$params);
        }

        return $this->request($method, ...$params);
    }

    /**
     * Gets default configuration.
     *
     * @return array
     */
    protected function getDefaultConfig() : array
    {
        return [
            'scheme'        => 'http',
            'host'          => '127.0.0.1',
            'port'          => 8332,
            'user'          => null,
            'password'      => null,
            'ca'            => null,
            'preserve_case' => false,
        ];
    }

    /**
     * Merge config with default values.
     *
     * @param array $config
     *
     * @return array
     */
    protected function mergeDefaultConfig(array $config = []) : array
    {
        // use same var name as laravel-bitcoinrpc
        $config['password'] = $config['password'] ?? $config['pass'] ?? null;

        if (is_null($config['password'])) {
            // use default value from getDefaultConfig()
            unset($config['password']);
        }

        return array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Gets CA file from config.
     *
     * @return string|null
     */
    protected function getCa() : ?string
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
    protected function getAuth() : array
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
    protected function getDsn() : string
    {
        $scheme = $this->config['scheme'] ?? 'http';

        return $scheme.'://'.
            $this->config['host'].':'.
            $this->config['port'];
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
     * Expand URL into components.
     *
     * @param string $url
     *
     * @return array
     */
    protected function parseUrl(string $url) : array
    {
        $allowed = ['scheme', 'host', 'port', 'user', 'pass'];

        $parts = (array) parse_url($url);
        $parts = array_intersect_key($parts, array_flip($allowed));

        if (!$parts || empty($parts)) {
            throw new BadConfigurationException(
                ['url' => $url],
                'Invalid url'
            );
        }

        return $parts;
    }

    /**
     * Construct json request.
     *
     * @param string $method
     * @param mixed  $params
     *
     * @return array
     */
    protected function makeJson(string $method, $params = []) : array
    {
        return [
            'json' => [
                'method' => $this->config['preserve_case'] ?
                    $method : strtolower($method),
                'params' => (array) $params,
                'id'     => $this->rpcId++,
            ],
        ];
    }
}
