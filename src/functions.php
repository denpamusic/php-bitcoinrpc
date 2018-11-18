<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

if (!function_exists('to_bitcoin')) {
    /**
     * Converts from satoshi to bitcoin.
     *
     * @param int $satoshi
     *
     * @return string
     */
    function to_bitcoin($satoshi) : string
    {
        return bcdiv((string) (int) $satoshi, (string) 1e8, 8);
    }
}

if (!function_exists('to_satoshi')) {
    /**
     * Converts from bitcoin to satoshi.
     *
     * @param string|float $bitcoin
     *
     * @return string
     */
    function to_satoshi($bitcoin) : string
    {
        return bcmul(to_fixed($bitcoin, 8), (string) 1e8);
    }
}

if (!function_exists('to_ubtc')) {
    /**
     * Converts from bitcoin to ubtc/bits.
     *
     * @param string|float $bitcoin
     *
     * @return string
     */
    function to_ubtc($bitcoin) : string
    {
        return bcmul(to_fixed($bitcoin, 8), (string) 1e6, 4);
    }
}

if (!function_exists('to_mbtc')) {
    /**
     * Converts from bitcoin to mbtc.
     *
     * @param string|float $bitcoin
     *
     * @return string
     */
    function to_mbtc($bitcoin) : string
    {
        return bcmul(to_fixed($bitcoin, 8), (string) 1e3, 4);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param string|float $number
     * @param int          $precision
     *
     * @return string
     */
    function to_fixed($number, int $precision = 8) : string
    {
        $number = $number * pow(10, $precision);

        return bcdiv((string) $number, (string) pow(10, $precision), $precision);
    }
}

if (!function_exists('exception')) {
    /**
     * Gets exception handler instance.
     *
     * @return \Denpa\Bitcoin\Exceptions\Handler
     */
    function exception() : Exceptions\Handler
    {
        return Exceptions\Handler::getInstance();
    }
}

set_exception_handler([Exceptions\Handler::getInstance(), 'handle']);
