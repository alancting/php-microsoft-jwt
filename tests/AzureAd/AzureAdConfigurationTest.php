<?php

namespace Alancting\Microsoft\Tests\AzureAd;

use PHPUnit\Framework\TestCase;
use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;

class AzureAdConfigurationTest extends TestCase
{
    public function testMissingTenantOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing tenant'
        );

        $config = new AzureAdConfiguration([]);
    }

    public function testMissingTenantIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing tenant_id'
        );

        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
            ]
        );
    }

    public function testMissingCliendIdOptions()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing client_id'
        );

        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
            ]
        );
    }

    public function testIfConfigUrisGivenOptions()
    {
        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'client_id' => 'client-id',
                'config_uri' => __DIR__ . '/../metadata/azure_ad/configuration/configuration.json',
            ]
        );

        $this->assertEquals($config->getConfigUri(), __DIR__ . '/../metadata/azure_ad/configuration/configuration.json');
    }

    public function testIfConfigUrisNotGivenOptions()
    {
        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'client_id' => 'client-id',
            ]
        );

        $this->assertEquals($config->getConfigUri(), 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0/.well-known/openid-configuration');
    }

    public function testInvalodConfigUri()
    {
        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
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
        $config = new AzureAdConfiguration(
            [
                'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
                'client_id' => 'client-id',
                'config_uri' => __DIR__ . '/../metadata/azure_ad/configuration/configuration.json',
            ]
        );

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
}