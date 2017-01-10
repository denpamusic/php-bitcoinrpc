<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

class Client {
	private $client;

	private $id = 0;

	/**
	 * @param array $params
	 */
	public function __construct(Array $params = [])
	{
		if(isset($params['url'])) {
			$url_parts = parse_url($params['url']);

			if(isset($url_parts['scheme'])) {
				$params['scheme'] = $url_parts['scheme'];
			}

			if(isset($url_parts['host'])) {
				$params['host'] = $url_parts['host'];
			}

			if(isset($url_parts['port'])) {
				$params['port'] = $url_parts['port'];
			}

			if(isset($url_parts['user'])) {
				$params['username'] = $url_parts['user'];
			}

			if(isset($url_parts['pass'])) {
				$params['password'] = $url_parts['pass'];
			}
		}

		if(!isset($params['scheme'])) {
			$params['scheme'] = 'http';
		}

		if(!isset($params['host'])) {
			$params['host'] = '127.0.0.1';
		}

		if(!isset($params['port'])) {
			$params['port'] = 8332;
		}

		if(!isset($params['username'])) {
			$params['username'] = '';
		}

		if(!isset($params['password'])) {
			$params['password'] = '';
		}

		$this->client = new \GuzzleHttp\Client([
			'base_uri' => "${params['scheme']}://${params['host']}:${params['port']}",
			'auth'     => [
				$params['username'],
				$params['password']
			],
			'verify'   => (isset($params['ca']) && is_file($params['ca']) ? $params['ca'] : true),
		]);
	}

	/**
	 * @param string $method
	 * @param array $params
	 */
	public function request($method, $params = []) {
		try {
			if(!is_array($params)) {
				$params = [ $params ];
			}

			$response = $this->client->request('POST', '/', ['json' => [
				'method' => $method,
				'params' => $params,
				'id'     => $this->id++,
			]]);

			$response = json_decode((string)$response->getBody(), true);
			if(isset($response['error']) && !is_null($response['error'])) {
				throw new ClientException($response['error']['message'], $response['error']['code']);
			}

			return $response['result'];
		} catch(RequestException $e) {
			if ($e->hasResponse()) {
				$response = json_decode((string)$e->getResponse()->getBody(), true);
				if(isset($response['error'])) {
					$code = isset( $response['error']['code'] ) ? $response['error']['code'] : 500;
					$message = isset( $response['error']['message'] ) ? $response['error']['message'] : '';
					throw new ClientException($message, $code);
				}
			}
		} catch(ServerException $e) {}
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