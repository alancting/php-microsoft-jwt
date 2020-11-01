<?php

namespace Alancting\Microsoft\Tests\Adfs;

use PHPUnit\Framework\TestCase;
use Alancting\Microsoft\JWT\Adfs\AdfsConfiguration;

class AdfsConfigurationTest extends TestCase
{
    public function testMissingHostNameAndConfigUriOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing hostname'
        );

        $config = new AdfsConfiguration([]);
    }

    public function testMissingConfigUriOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing config_uri'
        );

        $config = new AdfsConfiguration(
            [
                'hostname' => 'some_hostname.com',
            ]
        );
    }

    public function testMissingCliendIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing client_id'
        );

        $config = new AdfsConfiguration(
            [
                'hostname' => 'some_hostname.com',
                'config_uri' => __DIR__ . '/../metadata/adfs/configuration/configuration.json',
            ]
        );
    }

    public function testIfHostnameGivenOptions()
    {
        $config = new AdfsConfiguration(
            [
                'hostname' => 'some_hostname.com',
                'client_id' => 'client-id',
            ]
        );

        $this->assertEquals($config->getConfigUri(), 'https://some_hostname.com/adfs/.well-known/openid-configuration');
    }

    public function testIfConfigUrisGivenOptions()
    {
        $config = new AdfsConfiguration(
            [
                'client_id' => 'client-id',
                'config_uri' => __DIR__ . '/../metadata/adfs/configuration/configuration.json',
            ]
        );

        $this->assertEquals($config->getConfigUri(), __DIR__ . '/../metadata/adfs/configuration/configuration.json');
    }

    public function testInvalodConfigUri()
    {
        $config = new AdfsConfiguration(
            [
                'client_id' => 'client-id',
                'config_uri' => 'http://127.0.0.1/not_exists',
            ]
        );

        $this->assertEquals($config->getLoadStatus(), [
            'status' => false,
            'error' => 'Configuration not found',
        ]);
    }

    public function testConstructor()
    {
        $config = new AdfsConfiguration(
            [
                'client_id' => 'client-id',
                'config_uri' => __DIR__ . '/../metadata/adfs/configuration/configuration.json',
            ]
        );

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

    private function setExpectedException($exceptionName, $message = '', $code = null)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionName);
        } else {
            parent::setExpectedException($exceptionName, $message, $code);
        }
    }
}