<?php

namespace Denpa\Bitcoin\Tests;

use Denpa\Bitcoin;
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
    public function testToBtc($satoshi, $bitcoin)
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
    public function testToSatoshi($satoshi, $bitcoin)
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
    public function testToBits($ubtc, $bitcoin)
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
    public function testToMbtc($mbtc, $bitcoin)
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
    public function testToFixed($float, $precision, $expected)
    {
        $this->assertSame($expected, Bitcoin\to_fixed($float, $precision));
    }

    /**
     * Test exception handler helper.
     *
     * @return void
     */
    public function testExceptionHandlerHelper()
    {
        $this->assertInstanceOf(ExceptionHandler::class, Bitcoin\exception());
    }

    /**
     * Provides satoshi and bitcoin values.
     *
     * @return array
     */
    public function satoshiBtcProvider()
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
    public function bitsBtcProvider()
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
    public function mbtcBtcProvider()
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
    public function floatProvider()
    {
        return [
            [1.2345678910, 0, '1'],
            [1.2345678910, 2, '1.23'],
            [1.2345678910, 4, '1.2345'],
            [1.2345678910, 8, '1.23456789'],
        ];
    }
}
