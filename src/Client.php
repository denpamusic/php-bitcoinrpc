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
        $config = $this->defaultConfig($this->parseUrl($config));

        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            Middleware::mapResponse(function (ResponseInterface $response) {
                return BitcoindResponse::createFrom($response);
            }),
            'json_response'
        );

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri'    => "${config['scheme']}://${config['host']}:${config['port']}",
            'auth'        => [
                $config['user'],
                $config['pass'],
            ],
            'verify'      => isset($config['ca']) && is_file($config['ca']) ?
                $config['ca'] : true,
            'handler'     => $handlerStack,
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
            ) ? $this->client->getConfig($option) : false;
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
     * Makes request to Bitcoin Core.
     *
     * @param string $method
     * @param mixed  $params
     *
     * @return array
     */
    public function request($method, $params = [])
    {
        try {
            $json = [
                'method' => strtolower($method),
                'params' => (array) $params,
                'id'     => $this->rpcId++,
            ];

            $response = $this->client->request('POST', '/', ['json' => $json]);

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
        } catch (Exceptions\BitcoindException $exception) {
            throw $exception;
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
            ->requestAsync('POST', '/', ['json' => $json]);

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
            'scheme' => 'http',
            'host'   => '127.0.0.1',
            'port'   => 8332,
            'user'   => '',
            'pass'   => '',
        ];

        return array_merge($defaults, $config);
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
	 * @param callable                            $callback
	 *
	 * @return void
	 */
	protected function asyncFulfilled(ResponseInterface $response, callable $callback)
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
	 * @param callable                               $callback
	 *
	 * @return void
	 */
	protected function asyncRejected(RequestException $exception, callable $callback)
	{
		if (
			$exception->hasResponse() &&
			$exception->getResponse()->hasError()
		) {
			$exception = new Exceptions\BitcoindException(
				$exception->getResponse()->error()
			);
		}

		$exception = new Exceptions\ClientException(
			$exception->getMessage(),
			$exception->getCode()
		);

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
