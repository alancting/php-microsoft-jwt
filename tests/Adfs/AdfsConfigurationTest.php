<?php

namespace Alancting\Microsoft\Tests\Adfs;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use \Mockery;

use Symfony\Component\Cache\CacheItem;

use Alancting\Microsoft\JWT\Base\MicrosoftConfiguration;
use Alancting\Microsoft\JWT\Adfs\AdfsConfiguration;

class AdfsConfigurationTest extends MockeryTestCase
{
    private $default_configs;

    protected function setUp(): void
    {
        $this->default_configs = [
            'hostname' => 'some_hostname.com',
            'client_id' => 'client-id',
            'config_uri' => __DIR__ . '/../metadata/adfs/configuration/configuration.json',
        ];
    }
    
    public function tearDown(): void
    {
        Mockery::close();
    }
    
    public function testMissingHostNameAndConfigUriOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing hostname'
        );

        new AdfsConfiguration([]);
    }

    public function testMissingCliendIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing client_id'
        );

        unset(($this->default_configs)['client_id']);
        new AdfsConfiguration($this->default_configs);
    }

    public function testIfHostnameGivenOptions()
    {
        unset(($this->default_configs)['config_uri']);
        $config = new AdfsConfiguration($this->default_configs);

        $this->assertEquals($config->getConfigUri(), 'https://some_hostname.com/adfs/.well-known/openid-configuration');
    }

    public function testIfConfigUrisGivenOptions()
    {
        $config = new AdfsConfiguration($this->default_configs);

        $this->assertEquals($config->getConfigUri(), __DIR__ . '/../metadata/adfs/configuration/configuration.json');
    }

    public function testInvalidConfigUri()
    {
        ($this->default_configs)['config_uri'] = 'http://127.0.0.1/not_exists';
        $config = new AdfsConfiguration($this->default_configs);

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
        new AdfsConfiguration($this->default_configs);
    }

    public function testMissingCacheOptionsKey() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid cache configuration'
        );
        
        ($this->default_configs)['cache'] = [];
        new AdfsConfiguration($this->default_configs);
    }
    
    public function testInvalidCacheType() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid cache type'
        );

        ($this->default_configs)['cache']['type'] = 'any_random_type';
        new AdfsConfiguration($this->default_configs);
    }
    
    public function testMissingCacheTypeFilePath() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing file path'
        );

        ($this->default_configs)['cache']['type'] = 'file';
        new AdfsConfiguration($this->default_configs);
    }
    
    public function testMissingCacheTypeRedisClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing Redis client'
        );

        ($this->default_configs)['cache']['type'] = 'redis';
        new AdfsConfiguration($this->default_configs);
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
        new AdfsConfiguration($this->default_configs);
    }

    public function testMissingCacheTypeMemcacheClient() 
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing Memcached client'
        );
        
        ($this->default_configs)['cache']['type'] = 'memcache';
        new AdfsConfiguration($this->default_configs);
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
        new AdfsConfiguration($this->default_configs);
    }
    
    public function testConstructor()
    {
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
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
        $config = new AdfsConfiguration($this->default_configs);
        $this->commonConstructorAssert($config);
    }

    private function setExpectedException($exceptionName, $message = '', $code = null)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionName);
        } else {
            parent::setExpectedException($exceptionName, $message, $code);
        }
    }

    private function commonConstructorAssert($config) 
    {
        $this->assertEquals($config->getLoadStatus(), [
            'status' => true,
        ]);

        $this->assertEquals($config->getClientId(), 'client-id');

        $this->assertArrayHasKey('2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc', $config->getJWKs());

        $this->assertEquals($config->getIdTokenSigingAlgValuesSupported(), ['RS256']);
        $this->assertEquals($config->getTokenEndpointAuthSigingAlgValuesSupported(), ['RS256']);

        $this->assertEquals($config->getIssuer(), 'https://your_domain/adfs');
        $this->assertEquals($config->getAccessTokenIssuer(), 'http://your_domain/adfs/services/trust');

        $this->assertEquals($config->getAuthorizationEndpoint(), 'https://your_domain/adfs/oauth2/authorize/');
        $this->assertEquals($config->getTokenEndpoint(), 'https://your_domain/adfs/oauth2/token/');
        $this->assertEquals($config->getUserInfoEndpoint(), 'https://your_domain/adfs/userinfo');
        $this->assertEquals($config->getDeviceAuthEndpoint(), 'https://your_domain/adfs/oauth2/devicecode');
        $this->assertEquals($config->getEndSessionEndpoint(), 'https://your_domain/adfs/oauth2/logout');
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