# Simple Bitcoin JSON-RPC client based on GuzzleHttp

[![Latest Stable Version](https://poser.pugx.org/denpa/php-bitapi/v/stable)](https://packagist.org/packages/denpa/php-bitapi) [![License](https://poser.pugx.org/denpa/php-bitapi/license)](https://packagist.org/packages/denpa/php-bitapi) [![Build Status](https://travis-ci.org/denpamusic/php-bitapi.svg?branch=master)](https://travis-ci.org/denpamusic/php-bitapi) [![Coverage Status](https://coveralls.io/repos/github/denpamusic/php-bitapi/badge.svg?branch=master)](https://coveralls.io/github/denpamusic/php-bitapi?branch=master) [![Dependency Status](https://www.versioneye.com/user/projects/58827bdae25f59002c91be10/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/58827bdae25f59002c91be10)

## Installation
Run ```php composer.phar require denpa/php-bitapi``` in your project directory or add following lines to composer.json
```javascript
"require": {
	"denpa/php-bitapi": "^1.0"
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
    'verify'   => '/etc/ssl/ca-cert.pem'
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