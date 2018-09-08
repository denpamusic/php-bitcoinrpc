<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client = null;

    /**
     * Client configuration.
     *
     * @var array
     */
    protected $config;

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
        $this->config = $this->defaultConfig($this->parseUrl($config));

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri' => $this->getDsn(),
            'auth'     => $this->getAuth(),
            'verify'   => $this->getCa(),
            'handler'  => $this->getHandler(),
        ]);
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
        return (
                isset($this->client) &&
                $this->client instanceof ClientInterface
            ) ? $this->client->getConfig($option) : null;
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
            $json = [
                'method' => strtolower($method),
                'params' => (array) $params,
                'id'     => $this->rpcId++,
            ];

            $response = $this->client->request('POST', $this->path, ['json' => $json]);

            if ($response->hasError()) {
                // throw exception on error
                throw new Exceptions\BitcoindException($response->error());
            }

            return $response;
        } catch (RequestException $exception) {
            if (
                $exception->hasResponse() &&
                $exception->getResponse()->hasError()
            ) {
                throw new Exceptions\BitcoindException($exception->getResponse()->error());
            }

            throw new Exceptions\ClientException(
                $exception->getMessage(),
                $exception->getCode()
            );
        }
    }

    /**
     * Makes async request to Bitcoin Core.
     *
     * @param string        $method
     * @param mixed         $params
     * @param callable|null $onFullfiled
     * @param callable|null $onRejected
     *
     * @return \GuzzleHttp\Promise\Promise
     */
    public function requestAsync(
        $method,
        $params = [],
        callable $onFullfiled = null,
        callable $onRejected = null)
    {
        $json = [
            'method' => strtolower($method),
            'params' => (array) $params,
            'id'     => $this->rpcId++,
        ];

        $promise = $this->client
            ->requestAsync('POST', $this->path, ['json' => $json]);

        $promise->then(
            function (ResponseInterface $response) use ($onFullfiled) {
                $this->asyncFulfilled($response, $onFullfiled);
            },
            function (RequestException $exception) use ($onRejected) {
                $this->asyncRejected($exception, $onRejected);
            }
        );

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
        $method = str_ireplace('async', '', $method, $count);
        if ($count > 0) {
            return $this->requestAsync($method, ...$params);
        }

        return $this->request($method, $params);
    }

    /**
     * Set default config values.
     *
     * @param array $config
     *
     * @return array
     */
    protected function defaultConfig(array $config = [])
    {
        $defaults = [
            'scheme'     => 'http',
            'host'       => '127.0.0.1',
            'port'       => 8332,
            'user'       => '',
            'password'   => '',
        ];

        // use same var name as laravel-bitcoinrpc
        if (
            !array_key_exists('password', $config) &&
            array_key_exists('pass', $config)
        ) {
            $config['password'] = $config['pass'];
            unset($config['pass']);
        }

        return array_merge($defaults, $config);
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
        $scheme = $this->config['scheme'] ?? 'http';

        return $scheme.'://'.
            $this->config['host'].':'.
            $this->config['port'];
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
                return BitcoindResponse::createFrom($response);
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
                throw new Exceptions\ClientException('Invalid url');
            }

            return $parts;
        }

        return $config;
    }

    /**
     * Handles async request success.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable|null                       $callback
     *
     * @return void
     */
    protected function asyncFulfilled(ResponseInterface $response, callable $callback = null)
    {
        $error = null;
        if ($response->hasError()) {
            $error = new Exceptions\BitcoindException($response->error());
        }

        if (is_callable($callback)) {
            $callback($error ?: $response);
        }
    }

    /**
     * Handles async request failure.
     *
     * @param \GuzzleHttp\Exception\RequestException $exception
     * @param callable|null                          $callback
     *
     * @return void
     */
    protected function asyncRejected(RequestException $exception, callable $callback = null)
    {
        if (
            $exception->hasResponse() &&
            $exception->getResponse()->hasError()
        ) {
            $exception = new Exceptions\BitcoindException(
                $exception->getResponse()->error()
            );
        }

        if ($exception instanceof RequestException) {
            $exception = new Exceptions\ClientException(
                $exception->getMessage(),
                $exception->getCode()
            );
        }

        if (is_callable($callback)) {
            $callback($exception);
        }
    }

    /**
     * Converts amount from satoshi to bitcoin.
     *
     * @param int $amount
     *
     * @return float
     */
    public static function toBtc($amount)
    {
        return bcdiv((int) $amount, 1e8, 8);
    }

    /**
     * Converts amount from bitcoin to satoshi.
     *
     * @param float $amount
     *
     * @return int
     */
    public static function toSatoshi($amount)
    {
        return bcmul(static::toFixed($amount, 8), 1e8);
    }

    /**
     * Brings number to fixed pricision without rounding.
     *
     * @param float $number
     * @param int   $precision
     *
     * @return string
     */
    public static function toFixed($number, $precision = 8)
    {
        $number = $number * pow(10, $precision);

        return bcdiv($number, pow(10, $precision), $precision);
    }
}
