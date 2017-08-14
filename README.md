# Simple Bitcoin JSON-RPC client based on GuzzleHttp

[![Latest Stable Version](https://poser.pugx.org/denpa/php-bitcoinrpc/v/stable)](https://packagist.org/packages/denpa/php-bitcoinrpc) [![License](https://poser.pugx.org/denpa/php-bitcoinrpc/license)](https://packagist.org/packages/denpa/php-bitcoinrpc) [![Build Status](https://travis-ci.org/denpamusic/php-bitcoinrpc.svg)](https://travis-ci.org/denpamusic/php-bitcoinrpc) [![Code Climate](https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/gpa.svg)](https://codeclimate.com/github/denpamusic/php-bitcoinrpc) <a href="https://codeclimate.com/github/denpamusic/php-bitcoinrpc/coverage"><img src="https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/coverage.svg" /></a> [![Dependency Status](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf/badge.svg?style=rounded)](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf)

## Installation
Run ```php composer.phar require denpa/php-bitcoinrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
    "denpa/php-bitcoinrpc": "^2.0"
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
$block = $bitcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash');            // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // array of values
$block->keys();            // array of keys
$block->random(1, 'tx');   // random block txid
```
To send asynchronous request, add Async to method name:
```php
use Denpa\Bitcoin\BitcoindResponse;

$promise = $bitcoind->getBlockAsync(
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function (BitcoindResponse $success) {
        //
    },
    function (\Exception $exception) {
        //
    }
);

$promise->wait();
```

You can also send requests using request method:
```php
$block = $bitcoind->request('getBlock', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

$block('hash');            // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // get response values
$block->keys();            // get response keys
$block->random(1, 'tx');   // get random txid

```
or requestAsync method for asynchronous calls:
```php
use Denpa\Bitcoin\BitcoindResponse;

$promise = $bitcoind->requestAsync(
    'getBlock',
    '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
    function (BitcoindResponse $success) {
        //
    },
    function (\Exception $exception) {
        //
    }
);

$promise->wait();
```

## License

This product is distributed under MIT license.
