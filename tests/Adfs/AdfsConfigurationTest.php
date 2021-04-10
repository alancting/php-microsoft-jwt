<?php

namespace Alancting\Microsoft\Tests\Adfs;

use PHPUnit\Framework\TestCase;
use Alancting\Microsoft\JWT\Adfs\AdfsConfiguration;

class AdfsConfigurationTest extends TestCase
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
    
    public function testMissingHostNameAndConfigUriOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing hostname'
        );

        new AdfsConfiguration([]);
    }

    public function testMissingConfigUriOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing config_uri'
        );

        unset(($this->default_configs)['client_id'], ($this->default_configs)['config_uri']);
        new AdfsConfiguration($this->default_configs);
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
}