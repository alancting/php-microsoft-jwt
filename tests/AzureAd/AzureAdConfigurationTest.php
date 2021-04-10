<?php

namespace Alancting\Microsoft\Tests\AzureAd;

// use PHPUnit\Framework\TestCase;

use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

use Alancting\Microsoft\JWT\Base\MicrosoftConfiguration;
use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;
use Mockery\Adapter\Phpunit\MockeryTestCase;

use \Mockery;

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

    public function testConstructorWithCacheFile()
    {
        \DG\BypassFinals::enable();
        
        ($this->default_configs)['cache'] = [
            'type' => 'file',
            'path' => 'any_file_path'
        ];
        
        $mock_cache_item_config = Mockery::mock(CacheItem::class);
        $mock_cache_item_config
            ->shouldReceive('isHit')
            ->andReturn(false);
        
        $mock_cache_item_config
            ->shouldReceive('set')
            ->andReturn($mock_cache_item_config);

        $mock_cache_item_config
            ->shouldReceive('get')
            ->andReturn(file_get_contents(($this->default_configs)['config_uri']));
        
        $mock_cache_config = Mockery::mock('overload:Symfony\Component\Cache\Adapter\FilesystemAdapter');
        $mock_cache_config
            ->shouldReceive('getItem')
            ->with(MicrosoftConfiguration::CACHE_KEY_CONFIGS)
            ->andReturn($mock_cache_item_config);
        
        $mock_cache_config
            ->shouldReceive('save')
            ->andReturn($mock_cache_item_config);

        $mock_cache_item_jwk = Mockery::mock(CacheItem::class);
        $mock_cache_item_jwk
            ->shouldReceive('isHit')
            ->andReturn(false);
            
        $mock_cache_item_jwk
            ->shouldReceive('set')
            ->andReturn($mock_cache_item_jwk);
    
        $mock_cache_item_jwk
            ->shouldReceive('get')
            ->andReturn(file_get_contents(__DIR__.'/../../tests/metadata/azure_ad/configuration/jwks_uri.json'));
            
        $mock_cache_config
            ->shouldReceive('getItem')
            ->with(MicrosoftConfiguration::CACHE_KEY_JWKS)
            ->andReturn($mock_cache_item_jwk);
            
        $mock_cache_config
            ->shouldReceive('save')
            ->andReturn($mock_cache_item_jwk);
                
        $config = new AzureAdConfiguration($this->default_configs);

        print_r($config->getLoadStatus());
        $this->commonConstructorAssert($config);

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

    private function mockCacheConfig()
    {
        
    }
}