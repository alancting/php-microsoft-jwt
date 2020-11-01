<?php

namespace Alancting\Microsoft\Tests\AzureAd;

use PHPUnit\Framework\TestCase;
use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;
use Alancting\Microsoft\JWT\AzureAd\AzureAdIdTokenJWT;

use Alancting\Microsoft\JWT\JWT;
use Alancting\Microsoft\JWT\JWK;

class AzureAdIdTokenJWTTest extends TestCase
{
    private $azure_ad_config;
    private $jwks;
    private $private_key;

    protected function setUp(): void
    {
        $this->azure_ad_config = new AzureAdConfiguration(
          [
              'tenant' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
              'tenant_id' => 'iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz',
              'client_id' => 'client-id',
              'config_uri' => __DIR__ . '/../metadata/azure_ad/configuration/configuration.json',
          ]
      );

        $jwkSet = json_decode(
          file_get_contents(__DIR__ . '/../metadata/azure_ad/configuration/jwks_uri.json'),
          true
      );
        $this->jwks = JWK::parseKeySet($jwkSet);

        $this->private_key = file_get_contents(__DIR__ . '/../metadata/azure_ad/configuration/private.pem');
    }

    public function testValidIdToken()
    {
        $payload = [
            'iss' => 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0',
            'aud' => 'client-id',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token);

        $this->assertFalse($id_token_jwt->isExpired());
        $this->assertEquals((object) $payload, $id_token_jwt->getPayload());
        $this->assertEquals($id_token, $id_token_jwt->getJWT());
        $this->assertEquals('tester123', $id_token_jwt->get('unique_name'));
    }

    public function testValidIdTokenOtherAuth()
    {
        $payload = [
            'iss' => 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0',
            'aud' => 'other-client-id',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token, 'other-client-id');

        $this->assertFalse($id_token_jwt->isExpired());
        $this->assertEquals((object) $payload, $id_token_jwt->getPayload());
        $this->assertEquals($id_token, $id_token_jwt->getJWT());
        $this->assertEquals('tester123', $id_token_jwt->get('unique_name'));
    }

    public function testInvalidIdTokenMissingIssuer()
    {
        $this->setExpectedException(
          'UnexpectedValueException',
          'Missing issuer'
      );

        $payload = [
            'aud' => 'client-id',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token);
    }

    public function testInvalidIdTokenInvalidIssuer()
    {
        $this->setExpectedException(
          'UnexpectedValueException',
          'Invalid issuer'
      );

        $payload = [
            'iss' => 'https://login.microsoftonline.com/wrong_id/v2.0',
            'aud' => 'client-id',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token);
    }

    public function testInvalidIdTokenMissingAudience()
    {
        $this->setExpectedException(
          'UnexpectedValueException',
          'Missing audience'
      );

        $payload = [
            'iss' => 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token);
    }

    public function testInvalidIdTokenInvalidAudience()
    {
        $this->setExpectedException(
          'UnexpectedValueException',
          'Invalid audience'
      );

        $payload = [
            'iss' => 'https://login.microsoftonline.com/iv9puejd-qmJ1-AL2i-j3TP-wrb7qjjvxttz/v2.0',
            'aud' => 'wrong-client-id',
            'exp' => time() + 10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AzureAdIdTokenJWT($this->azure_ad_config, $id_token);
    }

    public function setExpectedException($exceptionName, $message = '', $code = null)
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