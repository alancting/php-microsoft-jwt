[![Packagist](https://img.shields.io/packagist/v/alancting/php-microsoft-jwt?style=for-the-badge)](https://packagist.org/packages/alancting/php-microsoft-jwt)
[![GitHub](https://img.shields.io/github/v/release/alancting/php-microsoft-jwt?label=GitHub&style=for-the-badge)](https://github.com/alancting/php-microsoft-jwt)
[![Test](https://img.shields.io/github/workflow/status/alancting/php-microsoft-jwt/PHP%20Test?label=TEST&style=for-the-badge)](https://github.com/alancting/php-microsoft-jwt)
[![Coverage Status](https://img.shields.io/coveralls/github/alancting/php-microsoft-jwt/master?style=for-the-badge)](https://coveralls.io/github/alancting/php-microsoft-jwt?branch=master)
[![GitHub license](https://img.shields.io/github/license/alancting/php-microsoft-jwt?color=green&style=for-the-badge)](https://github.com/alancting/php-microsoft-jwt/blob/master/LICENCE)  
[![firebase/php-jwt Version](https://img.shields.io/static/v1?label=firebase%2Fphp-jwt&message=5.2.0&color=blue&style=for-the-badge)](https://github.com/firebase/php-jwt/tree/v5.2.0)

# php-microsoft-jwt

A simple library to validate and decode Microsoft Azure Active Directory ([Azure AD](https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-app-types)), Microsoft Active Directory Federation Services (ADFS) JSON Web Tokens (JWT) in PHP, conforming to [RFC 7519](https://tools.ietf.org/html/rfc7519).

**Forked From [firebase/php-jwt](https://github.com/firebase/php-jwt)**

## Installation

Use composer to manage your dependencies and download php-microsoft-jwt:

```bash
composer require alancting/php-microsoft-jwt
```

## Example

### ADFS

```php
<?php

use Alancting\Microsoft\JWT\Adfs\AdfsConfiguration;
use Alancting\Microsoft\JWT\Adfs\AdfsAccessTokenJWT;
use Alancting\Microsoft\JWT\Adfs\AdfsIdTokenJWT;

...

/**
 * AdfsConfiguration class will go to https://{your_asfs_hostname}/adfs/.well-known/openid-configuration to parse the configuration for your application
 *
 */
$config_options = [
  'client_id' => '{client_id}',
  'hostname' => '{your_asfs_hostname}',
];

/**
 * You can also specific the local configuration by
 */
// $config_options = [
//   'client_id' => '{client_id}',
//   'config_uri' => 'local_path_to_configuration_json',
// ];

$config = new AdfsConfiguration($config_options);

$id_token = 'adfs.id.token.jwt';
$access_token = 'adfs.access.token.jwt';

/**
 * If id token is invalid, exception will be thrown.
 */
$id_token_jwt = new AdfsIdTokenJWT($config, $id_token);
echo "\n";
// Getting payload from id token
print_r($id_token_jwt->getPayload());
echo "\n";
// Getting value from payload by attribute of id token
print_r($id_token_jwt->get('attribute_name'));
echo "\n";

/**
 * If id token is invalid, exception will be thrown.
 * To validate and decode access token jwt, you need to pass $audience (scope name of your app)
 */
$access_token_jwt = new AdfsAccessTokenJWT($config, $access_token, $audience);
echo "\n";
// Getting payload from access token
print_r($access_token_jwt->getPayload());
echo "\n";
// Getting value from payload by attribute of access token
print_r($access_token_jwt->get('attribute_name'));
echo "\n";

/**
 * You might want to 'cache' the tokens for expire validation
 * To check whether the access token and id token are expired, simply call
 */
echo ($id_token_jwt->isExpired()) ? 'Id token is expired' : 'Id token is valid';
echo ($id_token->isExpired()) ? 'Access token is expired' : 'Access token is valid';
```

### Azure Ad

```php
<?php

use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;
use Alancting\Microsoft\JWT\AzureAd\AzureAdAccessTokenJWT;
use Alancting\Microsoft\JWT\AzureAd\AzureAdIdTokenJWT;

...

/**
 * AzureAdConfiguration class will go to https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration to parse the configuration for your application
 */
$config_options = [
  'tenant' => '{tenant_id} | common | organizations | consumers',
  'tenant_id' => '{tenant_id}',
  'client_id' => '{client_id}'
];

/**
 * You can also specific the local configuration by
 */
// $config_options = [
//   'tenant' => '{tenant_id} | common | organizations | consumers',
//   'tenant_id' => '{tenant_id}',
//   'client_id' => '{client_id}'
//   'config_uri' => 'local_path_to_configuration_json',
// ];

$config = new AzureAdConfiguration($config_options);

$id_token = 'azure_ad.id.token.jwt';
$access_token = 'azure_ad.access.token.jwt';

/**
 * If id token is invalid, exception will be thrown.
 */
$id_token_jwt = new AzureAdIdTokenJWT($config, $id_token);
echo "\n";
/**
 * You could also pass $audience if needed
 */
// $id_token_jwt = new AzureAdIdTokenJWT($config, $id_token, $audience);
// echo "\n";

// Getting payload from id token
print_r($id_token_jwt->getPayload());
echo "\n";
// Getting value from payload by attribute of id token
print_r($id_token_jwt->get('attribute_name'));
echo "\n";

/**
 * If id token is invalid, exception will be thrown.
 * To validate and decode access token jwt, you need to pass $audience (scope name of your app)
 */
$access_token_jwt = new AzureAdAccessTokenJWT($config, $access_token, $audience);
echo "\n";
// Getting payload from access token
print_r($access_token_jwt->getPayload());
echo "\n";
// Getting value from payload by attribute of access token
print_r($access_token_jwt->get('attribute_name'));
echo "\n";

/**
 * You might want to 'cache' the tokens for expire validation
 * To check whether the access token and id token are expired, simply call
 */
echo ($id_token_jwt->isExpired()) ? 'Id token is expired' : 'Id token is valid';
echo ($id_token->isExpired()) ? 'Access token is expired' : 'Access token is valid';
```

### Cache support

We provide a option to cache the open id configuration in order to reduce the network traffic. You can use one of these cache options:

- File
- Redis
- Memcached

### ADFS

#### File

```php
$config_options = [
  'client_id' => '{client_id}',
  'hostname' => '{your_asfs_hostname}',
  'cache' => [
    'type' => 'file',
    'path' => '{cache_file_path}'
  ]
];
$config = new AdfsConfiguration($config_options);
```

#### Redis

Client expects a [Redis](https://github.com/phpredis/phpredis) or [Predis](https://packagist.org/packages/predis/predis) instance

```php
$redis_client = new \Redis();
$redis_client->pconnect('redis', 6379);

$predis_client = new \Predis\Client([
  'scheme' => 'tcp',
  'host'   => 'redis',
  'port'   => 6379,
]);

$config_options = [
  'client_id' => '{client_id}',
  'hostname' => '{your_asfs_hostname}',
  'cache' => [
    'type' => 'redis',
    'client' => $redis_client // or $predis_client
  ]
];
$config = new AdfsConfiguration($config_options);
```

#### Memcached

Client expects a [Memcached](https://www.php.net/manual/en/class.memcached.php) instance

```php
$memcached_client = new \Memcached();
$memcached_client->addServer('memcached', 11211);

$config_options = [
  'client_id' => '{client_id}',
  'hostname' => '{your_asfs_hostname}',
  'cache' => [
    'type' => 'memcache',
    'client' => $memcached_client
  ]
];
$config = new AdfsConfiguration($config_options);
```

### Azure Ad

#### File

```php
$config_options = [
  'tenant' => '{tenant_id} | common | organizations | consumers',
  'tenant_id' => '{tenant_id}',
  'client_id' => '{client_id}',
  'cache' => [
    'type' => 'file',
    'path' => '{cache_file_path}'
  ]
];

$config = new AzureAdConfiguration($config_options);
```

#### Redis

Client expects a [Redis](https://github.com/phpredis/phpredis) or [Predis](https://packagist.org/packages/predis/predis) instance

```php
$redis_client = new \Redis();
$redis_client->pconnect('redis', 6379);

$predis_client = new \Predis\Client([
  'scheme' => 'tcp',
  'host'   => 'redis',
  'port'   => 6379,
]);

$config_options = [
  'tenant' => '{tenant_id} | common | organizations | consumers',
  'tenant_id' => '{tenant_id}',
  'client_id' => '{client_id}',
  'cache' => [
    'type' => 'redis',
    'client' => $redis_client // or $predis_client
  ]
];
$config = new AzureAdConfiguration($config_options);
```

#### Memcached

Client expects a [Memcached](https://www.php.net/manual/en/class.memcached.php) instance

```php
$memcached_client = new \Memcached();
$memcached_client->addServer('memcached', 11211);

$config_options = [
  'tenant' => '{tenant_id} | common | organizations | consumers',
  'tenant_id' => '{tenant_id}',
  'client_id' => '{client_id}',
  'cache' => [
    'type' => 'memcache',
    'client' => $memcached_client
  ]
];
$config = new AzureAdConfiguration($config_options);
```

## Tests

Run the tests using phpunit:

```bash
$ composer install
$ composer run test
```

## License

[3-Clause BSD](http://opensource.org/licenses/BSD-3-Clause).
