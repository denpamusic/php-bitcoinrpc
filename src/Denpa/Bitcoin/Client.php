<?php

namespace Denpa\Bitcoin;

use Closure;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    private $client = null;

    /**
     * JSON-RPC Id.
     *
     * @var int
     */
    private $rpcId = 0;

    /**
     * Class constructor.
     *
     * @param mixed $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // init defaults
        $config = $this->defaultConfig($this->parseUrl($config));

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri'    => "${config['scheme']}://${config['host']}:${config['port']}",
            'auth'        => [
                $config['user'],
                $config['pass'],
            ],
            'verify'      => isset($config['ca']) && is_file($config['ca']) ?
                $config['ca'] : true,
            'handler'     => isset($config['handler']) ?
                $config['handler'] : null,
        ]);
    }

    /**
     * Get http client config.
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
     * Get http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set http client.
     *
     * @param  \GuzzleHttp\ClientInterface
     *
     * @return void
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Make request to Bitcoin Core.
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

            return $this->handleResponse($response);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $this->handleResponse($exception->getResponse());
            }

            throw new ClientException('Error Communicating with Server', 500);
        }
    }

    /**
     * Make async request to Bitcoin Core.
     *
     * @param string       $method
     * @param mixed        $params
     * @param Closure|null $onFullfiled
     * @param Closure|null $onRejected
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
                try {
                    $response = $this->handleResponse($response);
                } catch (ClientException $exception) {
                    $response = $exception;
                }

                if ($onFullfiled instanceof Closure) {
                    $onFullfiled($response);
                }
            },
            function (RequestException $exception) use ($onRejected) {
                try {
                    if ($exception->hasResponse()) {
                        $response = $this->handleResponse(
                            $exception->getResponse()
                        );
                    }

                    throw new ClientException(
                        'Error Communicating with Server',
                        500
                    );
                } catch (ClientException $exception) {
                    $response = $exception;
                }

                if ($onRejected instanceof Closure) {
                    $onRejected($response);
                }
            }
        );

        return $promise;
    }

    /**
     * Magical method for making requests to Bitcoin Core.
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
     * Handle bitcoind response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function handleResponse(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->__toString(), true);

        if (isset($data['error'])) {
            throw new ClientException(
                $data['error']['message'],
                $data['error']['code']
            );
        }

        if ($response->getStatusCode() != 200) {
            throw new ClientException(
                'Error Communicating with Server',
                $response->getStatusCode()
            );
        }

        return isset($data['result']) ? $data['result'] : null;
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
            $parts = parse_url($config);
            if (!$parts) {
                throw new ClientException('Invalid url');
            }

            return $parts;
        }

        return $config;
    }
}
