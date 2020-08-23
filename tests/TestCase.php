<?php

namespace Denpa\Bitcoin\Tests;

use Denpa\Bitcoin\Responses\BitcoindResponse;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->history = [];
    }

    /**
     * Block header response.
     *
     * @var array
     */
    protected static $getBlockResponse = [
        'hash'          => '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
        'confirmations' => 449162,
        'height'        => null,
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
        'tx'            => [
            'bedb088c480e5f7424a958350f2389c839d17e27dae13643632159b9e7c05482',
            '59b36164c777b34aee28ef623ec34700371d33ff011244d8ee22d02b0547c13b',
            'ead6116a07f2a6911ac93eb0ae00ce05d49c7bb288f2fb9c338819e85414cf2c',
            null,
        ],
        'test1'         => [
            'test2' => [
                'test4' => [
                    'amount' => 3,
                ],
            ],
            'test3' => [
                'test5' => [
                    'amount' => 4,
                ],
            ],
        ],
    ];

    /**
     * Transaction error response.
     *
     * @var array
     */
    protected static $rawTransactionError = [
        'code'    => -5,
        'message' => 'No information available about transaction',
    ];

    /**
     * Balance response.
     *
     * @var float
     */
    protected static $balanceResponse = 0.1;

    /**
     * Get error 500 message.
     *
     * @return string
     */
    protected function error500(): string
    {
        return 'Server error: `POST /` '.
            'resulted in a `500 Internal Server Error` response';
    }

    /**
     * Get Closure mock.
     *
     * @param array $with
     *
     * @return callable
     */
    protected function mockCallable(array $with = []): callable
    {
        $callable = $this->getMockBuilder(stdClass::class)
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
     * @param array                    $queue
     * @param \GuzzleHttp\HandlerStack $handler
     *
     * @return \GuzzleHttp\Client
     */
    protected function mockGuzzle(
        array $queue = [],
        HandlerStack $handler = null
    ): GuzzleClient {
        $handler = $handler ?: $this->bitcoind->getClient()->getConfig('handler');

        if ($handler) {
            $middleware = Middleware::history($this->history);
            $handler->push($middleware);
            $handler->setHandler(new MockHandler($queue));
        }

        return new GuzzleClient([
            'handler' => $handler,
        ]);
    }

    /**
     * Make block header response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getBlockResponse(int $code = 200): ResponseInterface
    {
        $json = json_encode([
            'result' => self::$getBlockResponse,
            'error'  => null,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Get getbalance command response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getBalanceResponse(int $code = 200): ResponseInterface
    {
        $json = json_encode([
            'result' => self::$balanceResponse,
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
    protected function rawTransactionError(int $code = 500): ResponseInterface
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
     * @return callable
     */
    protected function requestExceptionWithResponse(): callable
    {
        $exception = function ($request) {
            return new RequestException(
                'test',
                $request,
                new BitcoindResponse($this->rawTransactionError())
            );
        };

        return $exception;
    }

    /**
     * Return exception without response.
     *
     * @return callable
     */
    protected function requestExceptionWithoutResponse(): callable
    {
        $exception = function ($request) {
            return new RequestException('test', $request);
        };

        return $exception;
    }

    /**
     * Make request body.
     *
     * @param string $method
     * @param int    $id
     * @param mixed  $params,...
     *
     * @return array
     */
    protected function makeRequestBody(
        string $method,
        int $id,
        ...$params
    ): array {
        return [
            'method' => $method,
            'params' => (array) $params,
            'id'     => $id,
        ];
    }

    /**
     * Get request url from history.
     *
     * @param int $index
     *
     * @return \Psr\Http\Message\UriInterface|null
     */
    protected function getHistoryRequestUri(int $index = 0): ?UriInterface
    {
        if (isset($this->history[$index])) {
            return $this->history[$index]['request']->getUri();
        }
    }

    /**
     * Get request body from history.
     *
     * @param int $index
     *
     * @return mixed
     */
    protected function getHistoryRequestBody(int $index = 0)
    {
        if (isset($this->history[$index])) {
            return json_decode(
                $this->history[$index]['request']->getBody()->getContents(),
                true
            );
        }
    }
}
