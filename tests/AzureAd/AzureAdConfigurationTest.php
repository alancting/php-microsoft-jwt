<?php

namespace Alancting\Microsoft\Tests\AzureAd;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use \Mockery;

use Symfony\Component\Cache\CacheItem;

use Alancting\Microsoft\JWT\Base\MicrosoftConfiguration;
use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;

class AzureAdConfigurationTest extends MockeryTestCase
{
    private $default_configs;

    protected function setUp(): void
    {
        $this->default_configs = [
            'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
            'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
            'client_id' => 'client-id',
            'config_uri' => __DIR__ . '/../metadata/azure_ad/configuration/configuration.json'
        ];
    }
    
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testMissingTenantOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing tenant'
        );
        
        new AzureAdConfiguration([]);
    }

    public function testMissingTenantIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing tenant_id'
        );
        
        unset(($this->default_configs)['tenant_id'], ($this->default_configs)['client_id'], ($this->default_configs)['config_uri']);
        new AzureAdConfiguration($this->default_configs);
    }

    public function testMissingCliendIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing client_id'
        );
        unset(($this->default_configs)['client_id'], ($this->default_configs)['config_uri']);
        new AzureAdConfiguration($this->default_configs);
    }

    public function testIfConfigUrisGivenOptions()
    {
        $config = new AzureAdConfiguration($this->default_configs);

        $this->assertEquals($config->getConfigUri(), __DIR__ . '/../metadata/azure_ad/configuration/configuration.json');
    }

    public function testIfConfigUrisNotGivenOptions()
    {
        unset(($this->default_configs)['config_uri']);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->assertEquals($config->getConfigUri(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0/.well-known/openid-configuration');
    }

    public function testInvalidConfigUri()
    {
        ($this->default_configs)['config_uri'] = 'http://127.0.0.1/not_exists';
        $config = new AzureAdConfiguration($this->default_configs);
        
        $this->assertEquals($config->getLoadStatus(), [
            'status' => false,
            'error' => 'Configuration not found',
        ]);
    }

    public function testInvalidCacheOptions() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid cache configuration'
        );

        ($this->default_configs)['cache'] = '';
        new AzureAdConfiguration($this->default_configs);
    }

    public function testMissingCacheOptionsKey() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid cache configuration'
        );

        ($this->default_configs)['cache'] = [];
        new AzureAdConfiguration($this->default_configs);
    }

    public function testInvalidCacheType() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid cache type'
        );

        ($this->default_configs)['cache']['type'] = 'any_random_type';
        new AzureAdConfiguration($this->default_configs);
    }

    public function testMissingCacheTypeFilePath() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing file path'
        );

        ($this->default_configs)['cache']['type'] = 'file';
        new AzureAdConfiguration($this->default_configs);
    }

    public function testMissingCacheTypeRedisClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing Redis client'
        );

        ($this->default_configs)['cache']['type'] = 'redis';
        new AzureAdConfiguration($this->default_configs);
    }

    public function testInvalidCacheTypeRedisClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid Redis client, must be Redis or Predis'
        );

        ($this->default_configs)['cache'] = [
            'type' => 'redis',
            'client' => new \stdClass 
        ];
        new AzureAdConfiguration($this->default_configs);
    }

    public function testMissingCacheTypeMemcacheClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing Memcached client'
        );

        ($this->default_configs)['cache']['type'] = 'memcache';
        new AzureAdConfiguration($this->default_configs);
    }

    public function testInvalidCacheTypeMemcacheClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid Memcached client'
        );

        ($this->default_configs)['cache'] = [
            'type' => 'memcache',
            'client' => new \stdClass 
        ];
        new AzureAdConfiguration($this->default_configs);
    }
    
    public function testConstructor()
    {
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithFileCacheNotExists()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'file',
            'path' => 'any_file_path'
        ];

        $this->mockCacheConfig('FilesystemAdapter', false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithFileCacheExists()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'file',
            'path' => 'any_file_path'
        ];

        $this->mockCacheConfig('FilesystemAdapter', true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithFileCacheExistsWithConfigError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'file',
            'path' => 'any_file_path'
        ];

        $this->mockCacheConfig('FilesystemAdapter', true, true, false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithFileCacheExistsWithJwkError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'file',
            'path' => 'any_file_path'
        ];

        $this->mockCacheConfig('FilesystemAdapter', true, false, true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithRedisCacheNotExists()
    {
        \DG\BypassFinals::enable();
       
        ($this->default_configs)['cache'] = [
            'type' => 'redis',
            'client' => $this->createStub(\Redis::class)
        ];

        $this->mockCacheConfig('RedisAdapter', false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithRedisCacheExists()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'redis',
            'client' => $this->createStub(\Redis::class)
        ];

        $this->mockCacheConfig('RedisAdapter', true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithRedisCacheExistsWithConfigsError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'redis',
            'client' => $this->createStub(\Redis::class)
        ];

        $this->mockCacheConfig('RedisAdapter', true, true, false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithRedisCacheExistsWithJwkError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'redis',
            'client' => $this->createStub(\Redis::class)
        ];

        $this->mockCacheConfig('RedisAdapter', true, false, true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithMemcachedCacheNotExists()
    {
        \DG\BypassFinals::enable();
       
        ($this->default_configs)['cache'] = [
            'type' => 'memcache',
            'client' => $this->createStub(\Memcached::class)
        ];

        $this->mockCacheConfig('MemcachedAdapter', false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithMemcachedCacheExists()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'memcache',
            'client' => $this->createStub(\Memcached::class)
        ];

        $this->mockCacheConfig('MemcachedAdapter', true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithMemcachedCacheExistsWithConfigError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'memcache',
            'client' => $this->createStub(\Memcached::class)
        ];

        $this->mockCacheConfig('MemcachedAdapter', true, true, false);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    public function testConstructorWithMemcachedCacheExistsWithJwkError()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'memcache',
            'client' => $this->createStub(\Memcached::class)
        ];

        $this->mockCacheConfig('MemcachedAdapter', true, false, true);
        $config = new AzureAdConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    private function setExpectedException($exceptionName, $message = '', $code = null)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionName);
            if (!empty($message)) {
                $this->expectExceptionMessage($message);
            }
        } else {
            parent::setExpectedException($exceptionName, $message, $code);
        }
    }

    private function commonConstructorAssert($config) 
    {
        $this->assertEquals($config->getLoadStatus(), [
            'status' => true,
        ]);

        $this->assertEquals($config->getTenant(), 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz');
        $this->assertEquals($config->getTenantId(), 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz');

        $this->assertEquals($config->getClientId(), 'client-id');

        $this->assertArrayHasKey('2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc', $config->getJWKs());

        $this->assertEquals($config->getIdTokenSigingAlgValuesSupported(), ['RS256']);
        $this->assertEquals($config->getTokenEndpointAuthSigingAlgValuesSupported(), ['RS256']);

        $this->assertEquals($config->getIssuer(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0');
        $this->assertEquals($config->getAccessTokenIssuer(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0');

        $this->assertEquals($config->getAuthorizationEndpoint(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/oauth2/v2.0/authorize');
        $this->assertEquals($config->getTokenEndpoint(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/oauth2/v2.0/token');
        $this->assertEquals($config->getUserInfoEndpoint(), 'https://graph.microsoft.com/oidc/userinfo');
        $this->assertEquals($config->getDeviceAuthEndpoint(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/oauth2/v2.0/devicecode');
        $this->assertEquals($config->getEndSessionEndpoint(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/oauth2/v2.0/logout');
    }

    private function mockCacheConfig($cache_class, $is_hit, $config_error = false, $jwk_error = false)
    {
        if (!$config_error) {
            $mock_cach_item_configs = $this->getMockCachItem(
                $is_hit, 
                file_get_contents(($this->default_configs)['config_uri']));
        } else {
            $mock_cach_item_configs = $this->getMockCachItem(
                $is_hit, 
                file_get_contents(($this->default_configs)['config_uri']),
                json_encode([]));
        }
        
        if (!$jwk_error) {
            $mock_cach_item_jwks = $this->getMockCachItem(
                $is_hit, 
                file_get_contents(__DIR__.'/../../tests/metadata/azure_ad/configuration/jwks_uri.json'));
        } else {
            $mock_cach_item_jwks = $this->getMockCachItem(
                $is_hit, 
                file_get_contents(__DIR__.'/../../tests/metadata/azure_ad/configuration/jwks_uri.json'),
                json_encode([]));
        }
        
        $mock_cache = Mockery::mock(sprintf('overload:Symfony\Component\Cache\Adapter\%s', $cache_class));
        
        $mock_cache
            ->shouldReceive('getItem')
            ->with(MicrosoftConfiguration::CACHE_KEY_CONFIGS)
            ->andReturn($mock_cach_item_configs);

        if ($is_hit) {
            if (!$config_error) {
                $mock_cache
                    ->shouldNotReceive('save')
                    ->with($mock_cach_item_configs);
            } else {
                $mock_cache
                ->shouldReceive('save')
                ->with($mock_cach_item_configs)
                ->andReturn($mock_cach_item_configs);
            }
        } else {
            $mock_cache
                ->shouldReceive('save')
                ->with($mock_cach_item_configs)
                ->andReturn($mock_cach_item_configs);
        }

        $mock_cache
            ->shouldReceive('getItem')
            ->with(MicrosoftConfiguration::CACHE_KEY_JWKS)
            ->andReturn($mock_cach_item_jwks);
        
        if ($is_hit) {
            if (!$jwk_error) {
                $mock_cache
                    ->shouldNotReceive('save')
                    ->with($mock_cach_item_jwks);
            } else {
                $mock_cache
                    ->shouldReceive('save')
                    ->with($mock_cach_item_jwks)
                    ->andReturn($mock_cach_item_jwks);
            }
        } else {
            $mock_cache
                ->shouldReceive('save')
                ->with($mock_cach_item_jwks)
                ->andReturn($mock_cach_item_jwks);
        }
            
        return $mock_cache;
    }

    private function getMockCachItem($is_hit, $cached_result, $cached_error_result = false)
    {
        $mock_cach_item = Mockery::mock(CacheItem::class);
        $mock_cach_item
            ->shouldReceive('isHit')
            ->andReturn($is_hit);
        
        if ($is_hit && !$cached_error_result) {
            $mock_cach_item
                ->shouldNotReceive('set')
                ->andReturn($mock_cach_item);
        } else {
            $mock_cach_item
                ->shouldReceive('set')
                ->andReturn($mock_cach_item);
        }
        
        if (!$cached_error_result)
        {
            $mock_cach_item
                ->shouldReceive('get')
                ->andReturn($cached_result);
        } else {
            $mock_cach_item
                ->shouldReceive('get')
                ->andReturn($cached_error_result, $cached_result);
        }
        
        return $mock_cach_item;
    }
}