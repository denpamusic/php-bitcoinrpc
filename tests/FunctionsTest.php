<?php

use Denpa\Bitcoin;

class FunctionsTest extends TestCase
{
    /**
     * Test satoshi to btc converter.
     *
     * @return void
     *
     * @dataProvider valueProvider
     */
    public function testToBtc($satoshi, $bitcoin)
    {
        $this->assertEquals($bitcoin, Bitcoin\to_bitcoin($satoshi));
    }

    /**
     * Test btc to satoshi converter.
     *
     * @return void
     *
     * @dataProvider valueProvider
     */
    public function testToSatoshi($satoshi, $bitcoin)
    {
        $this->assertEquals($satoshi, Bitcoin\to_satoshi($bitcoin));
    }

    /**
     * Test float to fixed converter.
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
     * Provides satoshi and bitcoin values.
     *
     * @return array
     */
    public function valueProvider()
    {
        return [
            [1000, '0.00001'],
            [2500, '0.00002500'],
            [-1000, '-0.0000100'],
            [100000000, '1.00000000'],
            [150000000, '1.50000000'],
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
