<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Exception\RequestException;

class Client
{
    /**
     * Guzzle Client.
     */
    private $client = null;

    /**
     * JSON-RPC Id.
     */
    private $rpcId = 0;

    /**
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (isset($params['url'])) {
            $urlParts = parse_url($params['url']);

            foreach (['scheme', 'host', 'port', 'user', 'pass'] as $v) {
                if (isset($urlParts[$v])) {
                    $params[$v] = $urlParts[$v];
                }
            }
        }

        // init defaults
        $params = $this->defaultConfig($params);

        // construct client
        $this->client = new \GuzzleHttp\Client([
            'base_uri'    => "${params['scheme']}://${params['host']}:${params['port']}",
            'auth'        => [
                $params['user'],
                $params['pass'],
            ],
            'verify'      => (isset($params['ca']) && is_file($params['ca']) ? $params['ca'] : true),
            'handler'     => (isset($params['handler']) ? $params['handler'] : null),
        ]);
    }

    /**
     * @param string $option
     */
    public function getConfig($option = null)
    {
        return (isset($this->client) && $this->client instanceof \GuzzleHttp\Client) ? $this->client->getConfig($option) : false;
    }

    /**
     * @param void
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \GuzzleHttp\Client
     */
    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $params
     */
    private function defaultConfig(array $params = [])
    {
        $defaults = [
            'scheme' => 'http',
            'host'   => '127.0.0.1',
            'port'   => 8332,
            'user'   => '',
            'pass'   => '',
        ];

        foreach ($defaults as $k => $v) {
            $params[$k] = (!isset($params[$k]) ? $v : $params[$k]);
        }

        return $params;
    }

    /**
     * @param string $method
     * @param array  $params
     */
    public function request($method, $params = [])
    {
        try {
            $response = $this->client->request('POST', '/', ['json' => [
                'method' => strtolower($method),
                'params' => (!is_array($params) ? [$params] : $params),
                'id'     => $this->rpcId++,
            ]]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = json_decode((string) $e->getResponse()->getBody(), true);
                if (isset($response['error'])) {
                    $code = isset($response['error']['code']) ? $response['error']['code'] : 500;
                    $message = isset($response['error']['message']) ? $response['error']['message'] : '';
                    throw new ClientException($message, $code);
                }
            }
            throw new ClientException('Error Communicating with Server', 500);
        }

        $response = json_decode((string) $response->getBody(), true);
        if (isset($response['error']) && !is_null($response['error'])) {
            throw new ClientException($response['error']['message'], $response['error']['code']);
        }

        return $response['result'];
    }

    /**
     * @param string $method
     * @param array  $params
     */
    public function __call($method, array $params = [])
    {
        return $this->request($method, $params);
    }
}
