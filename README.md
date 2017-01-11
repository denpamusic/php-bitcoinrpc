# Simple Bitcoin JSON-RPC client based on GuzzleHttp

## Installation
Add following lines to your project composer.json
```javascript
"require": {
	"denpa/php-bitapi": "dev-master"
},
"repositories": [
	{
		"type": "vcs",
		"url": "https://github.com/denpamusic/php-bitapi"
	}
]
```
and run ```composer update```.

## Usage
Create new object
```php
$bitcoind = new Denpa\Bitcoin\Client('https://rpcuser@rpcpassword:localhost:8332/');
```
or use array to define your bitcoind settings
```php
$bitcoind = new Denpa\Bitcoin\Client([
	'scheme'   => 'https',
    'host'     => 'localhost',
    'port'     => 8332,
    'username' => 'rpcuser',
    'password' => 'rpcpassword',
    'verify'   => '/etc/ssl/ca-cert.pem'
]);
```
Then call methods defined in [Bitcoin Core API Documentation](https://bitcoin.org/en/developer-reference#bitcoin-core-apis).
```php
$bitcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');
```

## License

This product is distributed under MIT license.