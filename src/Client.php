<?php

namespace Denpa\Bitcoin;

use Denpa\Bitcoin\Exceptions\BadConfigurationException;
use Denpa\Bitcoin\Exceptions\BadRemoteCallException;
use Exception;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;

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
     * @param mixed $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // init defaults
        $this->config = $this->mergeDefaultConfig($this->parseUrl($config));

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
        if (!empty($this->promises)) {
            Promise\settle($this->promises)->wait();
        }
    }

    /**
     * Gets http client config.
     *
     * @param string|null $option
     *
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return $this->client->getConfig($option);
    }

    /**
     * Gets http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets http client.
     *
     * @param  \GuzzleHttp\ClientInterface
     *
     * @return static
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Sets wallet for multi-wallet rpc request.
     *
     * @param string $name
     *
     * @return static
     */
    public function wallet($name)
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
     * @return array
     */
    public function request($method, ...$params)
    {
        try {
            $response = $this->client
                ->post($this->path, $this->makeJson($method, $params));

            if ($response->hasError()) {
                // throw exception on error
                throw new BadRemoteCallException($response);
            }

            return $response;
        } catch (Exception $exception) {
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
        $method,
        $params = [],
        callable $fulfilled = null,
        callable $rejected = null)
    {
        $promise = $this->client
            ->postAsync($this->path, $this->makeJson($method, $params));

        $promise->then(function ($response) use ($fulfilled) {
            $this->onSuccess($response, $fulfilled);
        });

        $promise->otherwise(function ($exception) use ($rejected) {
            $this->onError($exception, $rejected);
        });

        $this->promises[] = $promise;

        return $promise;
    }

    /**
     * Makes request to Bitcoin Core.
     *
     * @param string $method
     * @param array  $params
     *
     * @return array
     */
    public function __call($method, array $params = [])
    {
        if (strtolower(substr($method, -5)) == 'async') {
            return $this->requestAsync(substr($method, 0, -5), ...$params);
        }

        return $this->request($method, ...$params);
    }

    /**
     * Handles async request success.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable|null                       $callback
     *
     * @return void
     */
    protected function onSuccess(ResponseInterface $response, callable $callback = null)
    {
        if (!is_null($callback)) {
            $callback($response);
        }
    }

    /**
     * Handles async request failure.
     *
     * @param \Exception    $exception
     * @param callable|null $callback
     *
     * @return void
     */
    protected function onError(Exception $exception, callable $callback = null)
    {
        if (!is_null($callback)) {
            try {
                exception()->handle($exception);
            } catch (Exception $exception) {
                $callback($exception);
            }
        }
    }

    /**
     * Gets default configuration.
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'scheme'        => 'http',
            'host'          => '127.0.0.1',
            'port'          => 8332,
            'user'          => '',
            'password'      => '',
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
    protected function mergeDefaultConfig(array $config = [])
    {
        // use same var name as laravel-bitcoinrpc
        if (
            !array_key_exists('password', $config) &&
            array_key_exists('pass', $config)
        ) {
            $config['password'] = $config['pass'];
            unset($config['pass']);
        }

        return array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Gets CA file from config.
     *
     * @return string|null
     */
    protected function getCa()
    {
        if (isset($this->config['ca']) && is_file($this->config['ca'])) {
            return $this->config['ca'];
        }
    }

    /**
     * Gets authentication array.
     *
     * @return array
     */
    protected function getAuth()
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
    protected function getDsn()
    {
        $scheme = isset($this->config['scheme']) ?
            $this->config['scheme'] : 'http';

        return $scheme.'://'.
            $this->config['host'].':'.
            $this->config['port'];
    }

    /**
     * Gets response handler class name.
     *
     * @return string
     */
    protected function getResponseHandler()
    {
        return 'Denpa\\Bitcoin\\Responses\\BitcoindResponse';
    }

    /**
     * Gets Guzzle handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function getHandler()
    {
        $stack = HandlerStack::create();

        $stack->push(
            Middleware::mapResponse(function (ResponseInterface $response) {
                $handler = $this->getResponseHandler();

                return new $handler($response);
            }),
            'json_response'
        );

        return $stack;
    }

    /**
     * Expand URL config into components.
     *
     * @param mixed $config
     *
     * @return array
     */
    protected function parseUrl($config)
    {
        if (is_string($config)) {
            $allowed = ['scheme', 'host', 'port', 'user', 'pass'];

            $parts = (array) parse_url($config);
            $parts = array_intersect_key($parts, array_flip($allowed));

            if (!$parts || empty($parts)) {
                throw new BadConfigurationException(
                    ['url' => $config],
                    'Invalid url'
                );
            }

            return $parts;
        }

        return $config;
    }

    /**
     * Construct json request.
     *
     * @param string $method
     * @param mixed  $params
     *
     * @return array
     */
    protected function makeJson($method, $params = [])
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
