# Simple Bitcoin JSON-RPC client based on GuzzleHttp

[![Latest Stable Version](https://poser.pugx.org/denpa/php-bitcoinrpc/v/stable)](https://packagist.org/packages/denpa/php-bitcoinrpc) [![License](https://poser.pugx.org/denpa/php-bitcoinrpc/license)](https://packagist.org/packages/denpa/php-bitcoinrpc) [![Build Status](https://travis-ci.org/denpamusic/php-bitcoinrpc.svg)](https://travis-ci.org/denpamusic/php-bitcoinrpc) [![Code Climate](https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/gpa.svg)](https://codeclimate.com/github/denpamusic/php-bitcoinrpc) <a href="https://codeclimate.com/github/denpamusic/php-bitcoinrpc/coverage"><img src="https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/coverage.svg" /></a> [![Dependency Status](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf/badge.svg?style=rounded)](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf)

## Installation
Run ```php composer.phar require denpa/php-bitcoinrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
    "denpa/php-bitcoinrpc": "^1.0"
}
```
and run ```php composer.phar update```.

## Requirements
PHP 7.0 or higher (should also work on 5.6, but this is unsupported)

## Usage
Create new object with url as parameter
```php
use Denpa\Bitcoin\Client as BitcoinClient;

$bitcoind = new BitcoinClient('http://rpcuser:rpcpassword@localhost:8332/');
```
or use array to define your bitcoind settings
```php
use Denpa\Bitcoin\Client as BitcoinClient;

$bitcoind = new BitcoinClient([
    'scheme' => 'http',                 // optional, default http
    'host'   => 'localhost',            // optional, default localhost
    'port'   => 8332,                   // optional, default 8332
    'user'   => 'rpcuser',              // required
    'pass'   => 'rpcpassword',          // required
    'ca'     => '/etc/ssl/ca-cert.pem'  // optional, for use with https scheme
]);
```
Then call methods defined in [Bitcoin Core API Documentation](https://bitcoin.org/en/developer-reference#bitcoin-core-apis) with magic:
```php
$bitcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');
```
To send asynchronous request, add Async to method name:
```php
$bitcoind->getBlockAsync(
	'000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($success) {
    	//
    },
    function ($exception) {
    	//
    }
);
```

You can also send requests using request method:
```php
$bitcoind->request('getBlock', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');
```
or requestAsync method for asynchronous calls:
```php
$bitcoind->requestAsync(
	'getBlock',
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function ($success) {
    	//
    },
    function ($exception) {
    	//
    }
);
```

## License

This product is distributed under MIT license.
