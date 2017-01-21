<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

class Client {
	/**
	 * Guzzle Client
	 */
	private $client;

	/**
	 * JSON-RPC Id
	 */
	private $id = 0;

	/**
	 * @param array $params
	 */
	public function __construct(Array $params = []) {
		if(isset($params['url'])) {
			$url_parts = parse_url($params['url']);

			foreach(['scheme', 'host', 'port', 'user', 'pass'] as $v) {
				if(isset($url_parts[$v])) {
					$params[$v] = $url_parts[$v];
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
				$params['pass']
			],
			'verify'      => (isset($params['ca']) && is_file($params['ca']) ? $params['ca'] : true),
			'handler'     => (isset($params['handler']) ? $params['handler'] : null),
		]);
	}

	/**
	 * @param string $option
	 */
	public function getConfig($option = null) {
		return (isset($this->client) && $this->client instanceof \GuzzleHttp\Client) ? $this->client->getConfig($option) : false;
	}

	/**
	 * @param array $params
	 */
	private function defaultConfig(Array $params = []) {
		$defaults = [
			'scheme' => 'http',
			'host'   => '127.0.0.1',
			'port'   => 8332,
			'user'   => '',
			'pass'   => ''
		];

		foreach($defaults as $k => $v) {
			$params[$k] = (!isset($params[$k]) ? $v : $params[$k]);
		}

		return $params;
	}

	/**
	 * @param string $method
	 * @param array $params
	 */
	public function request($method, $params = []) {
		try {
			$response = $this->client->request('POST', '/', ['json' => [
				'method' => strtolower($method),
				'params' => (!is_array($params) ? [ $params ] : $params),
				'id'     => $this->id++
			]]);
		} catch(RequestException $e) {
			if ($e->hasResponse()) {
				$response = json_decode((string)$e->getResponse()->getBody(), true);
				if(isset($response['error'])) {
					$code = isset( $response['error']['code'] ) ? $response['error']['code'] : 500;
					$message = isset( $response['error']['message'] ) ? $response['error']['message'] : '';
					throw new ClientException($message, $code);
				}
			}
		}

		$response = json_decode((string)$response->getBody(), true);
		if(isset($response['error']) && !is_null($response['error'])) {
			throw new ClientException($response['error']['message'], $response['error']['code']);
		}

		return $response['result'];
	}

	/**
	 * @param string $method
	 * @param array $params
	 */
	public function __call($method, Array $params = []) {
		return $this->request($method, $params);
	}
}

?>