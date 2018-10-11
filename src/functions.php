<?php

namespace Denpa\Bitcoin;

if (!function_exists('to_bitcoin')) {
    /**
     * Converts from satoshi to bitcoin.
     *
     * @param int $satoshi
     *
     * @return string
     */
    function to_bitcoin($satoshi)
    {
        return bcdiv((int) $satoshi, 1e8, 8);
    }
}

if (!function_exists('to_satoshi')) {
    /**
     * Converts from bitcoin to satoshi.
     *
     * @param float $bitcoin
     *
     * @return string
     */
    function to_satoshi($bitcoin)
    {
        return bcmul(to_fixed($bitcoin, 8), 1e8);
    }
}

if (!function_exists('to_ubtc')) {
    /**
     * Converts from bitcoin to ubtc/bits.
     *
     * @param float $bitcoin
     *
     * @return string
     */
    function to_ubtc($bitcoin)
    {
        return bcmul(to_fixed($bitcoin, 8), 1e6, 4);
    }
}

if (!function_exists('to_mbtc')) {
    /**
     * Converts from bitcoin to mbtc.
     *
     * @param float $bitcoin
     *
     * @return string
     */
    function to_mbtc($bitcoin)
    {
        return bcmul(to_fixed($bitcoin, 8), 1e3, 4);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param float $number
     * @param int   $precision
     *
     * @return string
     */
    function to_fixed($number, $precision = 8)
    {
        $number = $number * pow(10, $precision);

        return bcdiv($number, pow(10, $precision), $precision);
    }
}

if (!function_exists('exception')) {
    /**
     * Gets exception handler instance.
     *
     * @return \Denpa\Bitcoin\Exceptions\Handler
     */
    function exception()
    {
        return Exceptions\Handler::getInstance();
    }
}

set_exception_handler([Exceptions\Handler::getInstance(), 'handle']);
