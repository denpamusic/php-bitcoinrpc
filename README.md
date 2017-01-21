# Simple Bitcoin JSON-RPC client based on GuzzleHttp

[![Latest Stable Version](https://poser.pugx.org/denpa/php-bitcoinrpc/v/stable)](https://packagist.org/packages/denpa/php-bitcoinrpc)[![License](https://poser.pugx.org/denpa/php-bitcoinrpc/license)](https://packagist.org/packages/denpa/php-bitcoinrpc) [![Build Status](https://travis-ci.org/denpamusic/php-bitcoinrpc.svg?branch=master)](https://travis-ci.org/denpamusic/php-bitcoinrpc) [![Test Coverage](https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/coverage.svg)](https://codeclimate.com/github/denpamusic/php-bitcoinrpc/coverage) [![Code Climate](https://codeclimate.com/github/denpamusic/php-bitcoinrpc/badges/gpa.svg)](https://codeclimate.com/github/denpamusic/php-bitcoinrpc) [![Dependency Status](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf/badge.svg?style=rounded)](https://www.versioneye.com/user/projects/58833bfce25f5900365362cf)

## Installation
Run ```php composer.phar require denpa/php-bitcoinrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
	"denpa/php-bitcoinrpc": "^1.0"
}
```
and run ```php composer.phar update```.

## Requirements
PHP 5.6 or higher

## Usage
Create new object
```php
$bitcoind = new Denpa\Bitcoin\Client('https://rpcuser:rpcpassword@localhost:8332/');
```
or use array to define your bitcoind settings
```php
$bitcoind = new Denpa\Bitcoin\Client([
	'scheme'   => 'https',
    'host'     => 'localhost',
    'port'     => 8332,
    'user'     => 'rpcuser',
    'pass'     => 'rpcpassword',
    'ca'       => '/etc/ssl/ca-cert.pem'
]);
```
Then call methods defined in [Bitcoin Core API Documentation](https://bitcoin.org/en/developer-reference#bitcoin-core-apis) with magic
```php
$bitcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');
```
or as an argument
```php
$bitcoind->request('getBlock', '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');
```

## License

This product is distributed under MIT license.
