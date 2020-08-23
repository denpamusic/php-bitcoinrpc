<?php

namespace Denpa\Bitcoin\Tests;

use Denpa\Bitcoin;
use Denpa\Bitcoin\Exceptions\BadConfigurationException;
use Denpa\Bitcoin\Exceptions\Handler as ExceptionHandler;

class FunctionsTest extends TestCase
{
    /**
     * Test satoshi to btc converter.
     *
     * @param int    $satoshi
     * @param string $bitcoin
     *
     * @return void
     *
     * @dataProvider satoshiBtcProvider
     */
    public function testToBtc(int $satoshi, string $bitcoin): void
    {
        $this->assertEquals($bitcoin, Bitcoin\to_bitcoin($satoshi));
    }

    /**
     * Test bitcoin to satoshi converter.
     *
     * @param int    $satoshi
     * @param string $bitcoin
     *
     * @return void
     *
     * @dataProvider satoshiBtcProvider
     */
    public function testToSatoshi(int $satoshi, string $bitcoin): void
    {
        $this->assertEquals($satoshi, Bitcoin\to_satoshi($bitcoin));
    }

    /**
     * Test bitcoin to ubtc/bits converter.
     *
     * @param int    $ubtc
     * @param string $bitcoin
     *
     * @return void
     *
     * @dataProvider bitsBtcProvider
     */
    public function testToBits(int $ubtc, string $bitcoin): void
    {
        $this->assertEquals($ubtc, Bitcoin\to_ubtc($bitcoin));
    }

    /**
     * Test bitcoin to mbtc converter.
     *
     * @param float  $mbtc
     * @param string $bitcoin
     *
     * @return void
     *
     * @dataProvider mbtcBtcProvider
     */
    public function testToMbtc(float $mbtc, string $bitcoin): void
    {
        $this->assertEquals($mbtc, Bitcoin\to_mbtc($bitcoin));
    }

    /**
     * Test float to fixed converter.
     *
     * @param float  $float
     * @param int    $precision
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider floatProvider
     */
    public function testToFixed(
        float $float,
        int $precision,
        string $expected
    ): void {
        $this->assertSame($expected, Bitcoin\to_fixed($float, $precision));
    }

    /**
     * Test url parser.
     *
     * @param string      $url
     * @param string      $scheme
     * @param string      $host
     * @param int|null    $port
     * @param string|null $user
     * @param string|null $password
     *
     * @return void
     *
     * @dataProvider urlProvider
     */
    public function testSplitUrl(
        string $url,
        string $scheme,
        string $host,
        ?int $port,
        ?string $user,
        ?string $pass
    ): void {
        $parts = Bitcoin\split_url($url);

        $this->assertEquals($parts['scheme'], $scheme);
        $this->assertEquals($parts['host'], $host);
        foreach (['port', 'user', 'pass'] as $part) {
            if (!is_null(${$part})) {
                $this->assertEquals($parts[$part], ${$part});
            }
        }
    }

    /**
     * Test url parser with invalid url.
     *
     * @return array
     */
    public function testSplitUrlWithInvalidUrl(): void
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Invalid url');

        Bitcoin\split_url('cookies!');
    }

    /**
     * Test exception handler helper.
     *
     * @return void
     */
    public function testExceptionHandlerHelper(): void
    {
        $this->assertInstanceOf(ExceptionHandler::class, Bitcoin\exception());
    }

    /**
     * Provides url strings and parts.
     *
     * @return array
     */
    public function urlProvider(): array
    {
        return [
            ['https://localhost', 'https', 'localhost', null, null, null],
            ['https://localhost:8000', 'https', 'localhost', 8000, null, null],
            ['http://localhost', 'http', 'localhost', null, null, null],
            ['http://localhost:8000', 'http', 'localhost', 8000, null, null],
            ['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', null],
            ['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
        ];
    }

    /**
     * Provides satoshi and bitcoin values.
     *
     * @return array
     */
    public function satoshiBtcProvider(): array
    {
        return [
            [1000, '0.00001000'],
            [2500, '0.00002500'],
            [-1000, '-0.00001000'],
            [100000000, '1.00000000'],
            [150000000, '1.50000000'],
            [2100000000000000, '21000000.00000000'],
        ];
    }

    /**
     * Provides satoshi and ubtc/bits values.
     *
     * @return array
     */
    public function bitsBtcProvider(): array
    {
        return [
            [10, '0.00001000'],
            [25, '0.00002500'],
            [-10, '-0.00001000'],
            [1000000, '1.00000000'],
            [1500000, '1.50000000'],
        ];
    }

    /**
     * Provides satoshi and mbtc values.
     *
     * @return array
     */
    public function mbtcBtcProvider(): array
    {
        return [
            [0.01, '0.00001000'],
            [0.025, '0.00002500'],
            [-0.01, '-0.00001000'],
            [1000, '1.00000000'],
            [1500, '1.50000000'],
        ];
    }

    /**
     * Provides float values with precision and result.
     *
     * @return array
     */
    public function floatProvider(): array
    {
        return [
            [1.2345678910, 0, '1'],
            [1.2345678910, 2, '1.23'],
            [1.2345678910, 4, '1.2345'],
            [1.2345678910, 8, '1.23456789'],
        ];
    }
}
