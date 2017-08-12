<?php

namespace Denpa\Bitcoin;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * Block header response.
     *
     * @var array
     */
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
        'nextblockhash' => '00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048',
    ];

    /**
     * Transaction error response.
     *
     * @var array
     */
    private static $rawTransactionError = [
        'code'    => -5,
        'message' => 'No information available about transaction',
    ];

    /**
     * Set up test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->bitcoind = new Client();
    }

    /**
     * Test url parser.
     *
     * @param string $url
     * @param string $scheme
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     *
     * @return void
     *
     * @dataProvider urlProvider
     */
    public function testUrlParser($url, $scheme, $host, $port, $user, $pass)
    {
        $bitcoind = new Client($url);

        $this->assertInstanceOf(Client::class, $bitcoind);

        $base_uri = $bitcoind->getConfig('base_uri');

        $this->assertEquals($base_uri->getScheme(), $scheme);
        $this->assertEquals($base_uri->getHost(), $host);
        $this->assertEquals($base_uri->getPort(), $port);

        $auth = $bitcoind->getConfig('auth');
        $this->assertEquals($auth[0], $user);
        $this->assertEquals($auth[1], $pass);
    }

    /**
     * Data provider for url expander test.
     *
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['https://localhost', 'https', 'localhost', 8332, '', ''],
            ['https://localhost:8000', 'https', 'localhost', 8000, '', ''],
            ['http://localhost', 'http', 'localhost', 8332, '', ''],
            ['http://localhost:8000', 'http', 'localhost', 8000, '', ''],
            ['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', ''],
            ['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
        ];
    }

    /**
     * Test url parser with invalid url.
     *
     * @return array
     */
    public function testUrlParserWithInvalidUrl()
    {
        try {
            $bitcoind = new Client('cookies!');

            $this->expectException(ClientException::class);
        } catch (ClientException $e) {
            $this->assertEquals('Invalid url', $e->getMessage());
        }
    }

    /**
     * Test client getter and setter.
     *
     * @return void
     */
    public function testClientSetterGetter()
    {
        $bitcoind = new Client('http://old_client.org');
        $this->assertInstanceOf(Client::class, $bitcoind);

        $base_uri = $bitcoind->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'old_client.org');

        $oldClient = $bitcoind->getClient();
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $oldClient);

        $newClient = new \GuzzleHttp\Client(['base_uri' => 'http://new_client.org']);
        $bitcoind->setClient($newClient);

        $base_uri = $bitcoind->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'new_client.org');
    }

    /**
     * Test handler config option.
     *
     * @return void
     */
    public function testHandlerOption()
    {
        $bitcoind = new Client();

        $this->assertInstanceOf(
            \GuzzleHttp\HandlerStack::class,
            $bitcoind->getConfig('handler')
        );

        $bitcoind = new Client([
            'handler' => new MockHandler(),
        ]);

        $this->assertInstanceOf(
            MockHandler::class,
            $bitcoind->getConfig('handler')
        );
    }

    /**
     * Test ca config option.
     *
     * @return void
     */
    public function testCaOption()
    {
        $bitcoind = new Client();

        $this->assertEquals(null, $bitcoind->getConfig('ca'));

        $bitcoind = new Client([
            'ca' => __FILE__,
        ]);

        $this->assertEquals(__FILE__, $bitcoind->getConfig('verify'));
    }

    /**
     * Test simple request.
     *
     * @return void
     */
    public function testRequest()
    {
        $guzzle = $this->mockGuzzle([
            $this->blockHeaderResponse(),
        ]);

        $response = $this->bitcoind
            ->setClient($guzzle)
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $this->assertEquals(self::$blockHeaderResponse, $response);
    }

    /**
     * Test async request.
     *
     * @return void
     */
    public function testAsyncRequest()
    {
        $guzzle = $this->mockGuzzle([
            $this->blockHeaderResponse(),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function ($response) {
                return is_array($response) &&
                    $response == self::$blockHeaderResponse;
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $promise->wait();
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testMagic()
    {
        $guzzle = $this->mockGuzzle([
            $this->blockHeaderResponse(),
        ]);

        $response = $this->bitcoind
            ->setClient($guzzle)
            ->getBlockHeader(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $this->assertEquals(self::$blockHeaderResponse, $response);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testAsyncMagic()
    {
        $guzzle = $this->mockGuzzle([
            $this->blockHeaderResponse(),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function ($response) {
                return is_array($response) &&
                    $response == self::$blockHeaderResponse;
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->getBlockHeaderAsync(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $promise->wait();
    }

    /**
     * Test request exception.
     *
     * @return void
     */
    public function testRequestException()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(200),
        ]);

        try {
            $response = $this->bitcoind
                ->setClient($guzzle)
                ->getRawTransaction(
                    '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
                );

            $this->expectException(ClientException::class);
        } catch (ClientException $e) {
            $this->assertEquals(self::$rawTransactionError['message'], $e->getMessage());
            $this->assertEquals(self::$rawTransactionError['code'], $e->getCode());
        }
    }

    /**
     * Test async request exception.
     *
     * @return void
     */
    public function testAsyncRequestException()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(200),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function ($exception) {
                return $exception instanceof ClientException &&
                    $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $promise->wait();
    }

    /**
     * Test request exception with error code.
     *
     * @return void
     */
    public function testRequestExceptionWithServerErrorCode()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(500),
        ]);

        try {
            $this->bitcoind
                ->setClient($guzzle)
                ->getRawTransaction(
                    '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
                );

            $this->expectException(ClientException::class);
        } catch (ClientException $exception) {
            $this->assertEquals(
                self::$rawTransactionError['message'],
                $exception->getMessage()
            );
            $this->assertEquals(
                self::$rawTransactionError['code'],
                $exception->getCode()
            );
        }
    }

    /**
     * Test async request exception with error code.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithServerErrorCode()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(500),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function ($exception) {
                return $exception instanceof ClientException &&
                    $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $promise->wait();
    }

    /**
     * Test request exception with empty response body.
     *
     * @return void
     */
    public function testRequestExceptionWithEmptyResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            new Response(500),
        ]);

        try {
            $response = $this->bitcoind
                ->setClient($guzzle)
                ->getRawTransaction(
                    '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
                );

            $this->expectException(ClientException::class);
        } catch (ClientException $exception) {
            $this->assertEquals(
                'Error Communicating with Server',
                $exception->getMessage()
            );
            $this->assertEquals(500, $exception->getCode());
        }
    }

    /**
     * Test async request exception with empty response body.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithEmptyResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            new Response(500),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function ($exception) {
                return $exception instanceof ClientException &&
                    $exception->getMessage() == 'Error Communicating with Server' &&
                    $exception->getCode() == 500;
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $promise->wait();
    }

    /**
     * Test request exception with response.
     *
     * @return void
     */
    public function testRequestExceptionWithResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithResponse(),
        ]);

        try {
            $response = $this->bitcoind
                ->setClient($guzzle)
                ->getRawTransaction(
                    '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
                );

            $this->expectException(ClientException::class);
        } catch (ClientException $exception) {
            $this->assertEquals(
                self::$rawTransactionError['message'],
                $exception->getMessage()
            );
            $this->assertEquals(
                self::$rawTransactionError['code'],
                $exception->getCode()
            );
        }
    }

    /**
     * Test async request exception with response.
     *
     * @expectedException GuzzleHttp\Exception\RequestException
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithResponse(),
        ]);

        $onRejected = $this->mockCallable([
            $this->callback(function ($exception) {
                return $exception instanceof ClientException &&
                    $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $promise->wait();
    }

    /**
     * Test request exception with no response.
     *
     * @return void
     */
    public function testRequestExceptionWithNoResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithoutResponse(),
        ]);

        try {
            $response = $this->bitcoind
                ->setClient($guzzle)
                ->getRawTransaction(
                    '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
                );

            $this->expectException(ClientException::class);
        } catch (ClientException $exception) {
            $this->assertEquals(
                'Error Communicating with Server',
                $exception->getMessage()
            );
            $this->assertEquals(500, $exception->getCode());
        }
    }

    /**
     * Test async request exception with no response.
     *
     * @expectedException GuzzleHttp\Exception\RequestException
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithNoResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithoutResponse(),
        ]);

        $onRejected = $this->mockCallable([
            $this->callback(function ($exception) {
                return $exception instanceof ClientException &&
                    $exception->getMessage() == 'Error Communicating with Server' &&
                    $exception->getCode() == 500;
            }),
        ]);

        $promise = $this->bitcoind
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $promise->wait();
    }

    /**
     * Get Closure mock.
     *
     * @param array $with
     *
     * @return callable
     */
    protected function mockCallable(array $with = [])
    {
        $callable = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with(...$with);

        return $callable;
    }

    /**
     * Get Guzzle mock client.
     *
     * @param array $queue
     *
     * @return \GuzzleHttp\Client
     */
    protected function mockGuzzle(array $queue = [])
    {
        return new \GuzzleHttp\Client([
            'handler' => new MockHandler($queue),
        ]);
    }

    /**
     * Make block header response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function blockHeaderResponse($code = 200)
    {
        $json = json_encode([
            'result' => self::$blockHeaderResponse,
            'error'  => null,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Make raw transaction error response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function rawTransactionError($code = 500)
    {
        $json = json_encode([
            'result' => null,
            'error'  => self::$rawTransactionError,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Return exception with response.
     *
     * @return Closure
     */
    protected function requestExceptionWithResponse()
    {
        $exception = function ($request) {
            return new RequestException(
                'test',
                $request,
                $this->rawTransactionError()
            );
        };

        return $exception;
    }

    /**
     * Return exception without response.
     *
     * @return Closure
     */
    protected function requestExceptionWithoutResponse()
    {
        $exception = function ($request) {
            return new RequestException('test', $request);
        };

        return $exception;
    }
}
