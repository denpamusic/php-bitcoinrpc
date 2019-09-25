<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use Denpa\Bitcoin\Exceptions\BadConfigurationException;
use Denpa\Bitcoin\Exceptions\Handler as ExceptionHandler;
use Denpa\Bitcoin\Requests\Batch as RequestBatch;
use Denpa\Bitcoin\Requests\Request;
use Denpa\Bitcoin\Responses\Batch as ResponseBatch;

/**
 * Converts from satoshi to bitcoin.
 *
 * @param int $satoshi
 *
 * @return string
 */
function to_bitcoin(int $satoshi) : string
{
    return bcdiv((string) $satoshi, (string) 1e8, 8);
}

/**
 * Converts from bitcoin to satoshi.
 *
 * @param string|float $bitcoin
 *
 * @return string
 */
function to_satoshi($bitcoin) : string
{
    return bcmul(to_fixed((float) $bitcoin, 8), (string) 1e8);
}

/**
 * Converts from bitcoin to ubtc/bits.
 *
 * @param string|float $bitcoin
 *
 * @return string
 */
function to_ubtc($bitcoin) : string
{
    return bcmul(to_fixed((float) $bitcoin, 8), (string) 1e6, 4);
}

/**
 * Converts from bitcoin to mbtc.
 *
 * @param string|float $bitcoin
 *
 * @return string
 */
function to_mbtc($bitcoin) : string
{
    return bcmul(to_fixed((float) $bitcoin, 8), (string) 1e3, 4);
}

/**
 * Brings number to fixed precision without rounding.
 *
 * @param float $number
 * @param int   $precision
 *
 * @return string
 */
function to_fixed(float $number, int $precision = 8) : string
{
    $number = $number * pow(10, $precision);

    return bcdiv((string) $number, (string) pow(10, $precision), $precision);
}

/**
 * Splits url into parts.
 *
 * @param string $url
 *
 * @return array
 */
function split_url(string $url) : array
{
    $allowed = ['scheme', 'host', 'port', 'user', 'pass'];

    $parts = (array) parse_url($url);
    $parts = array_intersect_key($parts, array_flip($allowed));

    if (!$parts || empty($parts)) {
        throw new BadConfigurationException(
            ['url' => $url],
            'Invalid url'
        );
    }

    return $parts;
}

/**
 * Creates request for value.
 *
 * @param mixed            $value
 * @param RequestInterface $handler
 *
 * @return \Denpa\Bitcoin\Requests\Request
 */
function request_for($value, RequestInterface $handler) : Request
{
    if ($value instanceof RequestInterface) {
        return $value;
    }

    $key = $value;
    $params = [];

    if (is_array($value)) {
        if (isset($value[0])) {
            return new RequestBatch($value, $handler);
        }

        $key = key($value);
        $params = (array) end($value);
    }

    return new $handler($value, ...$params);
}

/**
 * Gets exception handler instance.
 *
 * @param mixed             $value
 * @param ResponseInterface $handler
 *
 * @return \Denpa\Bitcoin\Exceptions\Handler
 */
function response_for($value, ResponseInterface $handler)
{
    if ($value->getHeaderLine('X-Meta-Batch') == '1') {
        return new ResponseBatch($value, $handler);
    }

    return new $handler($value);
}

/**
 * Gets data with dot notation.
 *
 * @param mixed $items
 * @param mixed $key
 * @param mixed $default
 * @param bool  $exits
 *
 * @return mixed
 */
function dot_get($items, $key, $default = null, bool &$exists = true)
{
    $key = is_array($key) ? $key : explode('.', trim($key, '.'));

    while ($part = array_shift($key)) {
        if ($part == '*') {
            $result = [];

            foreach ((array) $items as $item) {
                $value = dot_get($item, $key, $default, $exists);
                if ($exists) {
                    $result[] = $value;
                }
            }

            return empty($result) ? $default : arr_collapse($result);
        }

        if (!$exists = array_key_exists($part, (array) $items)) {
            return $default;
        }

        $items = $items[$part];
    }

    return $items;
}

/**
 * Sets data with dot notation.
 *
 * @param mixed $items
 * @param mixed $key
 * @param mixed $value
 *
 * @return void
 */
function dot_set(&$items, $key, $value) : void
{
    $key = is_array($key) ? $key : explode('.', trim($key, '.'));

    while ($part = array_shift($key)) {
        if ($part == '*') {
            foreach ($items as &$item) {
                dot_set($item, $key, $value);
            }

            return;
        }

        if (!array_key_exists($part, (array) $items)) {
            if (is_array($items)) {
                $items[$part] = $value;
            }

            return;
        }

        $items = &$items[$part];
    }

    $items = $value;
}

/**
 * Delete data with dot notation.
 *
 * @param mixed $items
 * @param mixed $key
 *
 * @return void
 */
function dot_delete(&$items, $key) : void
{
    $key = is_array($key) ? $key : explode('.', trim($key, '.'));

    while ($part = array_shift($key)) {
        if ($part == '*' && is_array($items)) {
            foreach ($items as &$item) {
                dot_delete($item, $key);
            }

            return;
        }

        if (count($key) == 0) {
            unset($items[$part]);

            return;
        }

        $items = &$items[$part];
    }
}

/**
 * @param array $array
 *
 * @return mixed
 */
function arr_collapse(array $array)
{
    $result = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveArrayIterator($array)
    );

    foreach ($iterator as $item) {
        $result[] = $item;
    }

    return count($result) > 1 ? $result : $result[0];
}

/**
 * Gets exception handler instance.
 *
 * @return \Denpa\Bitcoin\Exceptions\Handler
 */
function exception() : ExceptionHandler
{
    return ExceptionHandler::getInstance();
}

set_exception_handler([ExceptionHandler::getInstance(), 'handle']);
