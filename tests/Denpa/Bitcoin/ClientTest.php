<?php

namespace Denpa\Bitcoin;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class ClientTest extends TestCase {
	private static $blockHeaderResponse = [
		'hash'          => '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
		'confirmations' => 449162,
		'height'        => 0,
		'version'       => 1,
		'versionHex'    => '00000001',
		'merkleroot'    => '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
		'time'          => 1231006505,
		'mediantime'    => 1231006505,
		'nonce'         => 2083236893,
		'bits'          => '1d00ffff',
		'difficulty'    => 1,
		'chainwork'     => '0000000000000000000000000000000000000000000000000000000100010001',
		'nextblockhash' => '00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048'
	];

	private static $rawTransactionError = [
		'code'    => -5,
		'message' => 'No information available about transaction'
	];

	/**
	 * @dataProvider urlProvider
	 */
	public function testUrlParser($url, $scheme, $host, $port, $user, $pass) {
		$bitcoind = new Client(['url' => $url]);

		$this->assertInstanceOf(Client::class, $bitcoind);

		$base_uri = $bitcoind->getConfig('base_uri');

		$this->assertEquals($base_uri->getScheme(), $scheme);
		$this->assertEquals($base_uri->getHost(), $host);
		$this->assertEquals($base_uri->getPort(), $port);

		$auth = $bitcoind->getConfig('auth');
		$this->assertEquals($auth[0], $user);
		$this->assertEquals($auth[1], $pass);
	}

	public function urlProvider() {
		return [
			['https://localhost', 'https', 'localhost', 8332, '', ''],
			['https://localhost:8000', 'https', 'localhost', 8000, '', ''],
			['http://localhost', 'http', 'localhost', 8332, '', ''],
			['http://localhost:8000', 'http', 'localhost', 8000, '', ''],
			['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', ''],
			['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
		];
	}

	public function testInstance() {
		$blockHeaderJson = json_encode([
			'result' => self::$blockHeaderResponse,
			'error'  => null,
			'id'     => 0,
		]);

		$rawTransactionErrorJson = json_encode([
			'result' => null,
			'error'  => self::$rawTransactionError,
			'id'	 => 0,
		]);

		$queue = [
			new Response(200, [], $blockHeaderJson),
			new Response(200, [], $blockHeaderJson),
			new Response(200, [], $rawTransactionErrorJson),
			new Response(500, [], $rawTransactionErrorJson),
			new Response(500),
		];

		$bitcoind = new Client(['handler' => MockHandler::createWithMiddleware($queue)]);

		$this->assertInstanceOf(Client::class, $bitcoind);

		return $bitcoind;
	}

	/**
	 * @depends testInstance
	 */
	public function testRequest(Client $bitcoind) {
		$response = $bitcoind->request('getblockheader', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

		$this->assertArraySubset(self::$blockHeaderResponse, $response);

		return $bitcoind;
	}

	/**
	 * @depends testRequest
	 */
	public function testMagic(Client $bitcoind) {
		$response = $bitcoind->getBlockHeader('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

		$this->assertArraySubset(self::$blockHeaderResponse, $response);

		return $bitcoind;
	}

	/**
	 * @depends testMagic
	 */
	public function testClientException(Client $bitcoind) {
		try {
			$response = $bitcoind->getRawTransaction('4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b');
			$this->expectException(ClientException::class);
		} catch(ClientException $e) {
			$this->assertEquals(self::$rawTransactionError['message'], $e->getMessage());
			$this->assertEquals(self::$rawTransactionError['code'], $e->getCode());
		}

		return $bitcoind;
	}

	/**
	 * @depends testClientException
	 */
	public function testClientExceptionWithServerErrorCode(Client $bitcoind) {
		try {
			$response = $bitcoind->getRawTransaction('4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b');
			$this->expectException(ClientException::class);
		} catch(ClientException $e) {
			$this->assertEquals(self::$rawTransactionError['message'], $e->getMessage());
			$this->assertEquals(self::$rawTransactionError['code'], $e->getCode());
		}

		return $bitcoind;
	}

	/**
	 * @depends testClientExceptionWithServerErrorCode
	 */
	public function testClientExceptionWithNoResponseBody(Client $bitcoind) {
		try {
			$response = $bitcoind->getRawTransaction('4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b');
			$this->expectException(ClientException::class);
		} catch(ClientException $e) {
			$this->assertEquals('Error Communicating with Server', $e->getMessage());
			$this->assertEquals(500, $e->getCode());
		}

		return $bitcoind;
	}
}

?>