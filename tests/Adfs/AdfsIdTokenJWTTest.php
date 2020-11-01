<?php

namespace Alancting\Microsoft\Tests\Adfs;

use PHPUnit\Framework\TestCase;
use Alancting\Microsoft\JWT\Adfs\AdfsConfiguration;
use Alancting\Microsoft\JWT\Adfs\AdfsIdTokenJWT;

use Alancting\Microsoft\JWT\JWT;
use Alancting\Microsoft\JWT\JWK;

class AdfsIdTokenJWTTest extends TestCase
{
    private $adfs_config;
    private $jwks;
    private $private_key;

    protected function setUp(): void
    {
        $this->adfs_config = new AdfsConfiguration(
            [
                'client_id' => 'client-id',
                'config_uri' =>  __DIR__ . '/../metadata/adfs/configuration/configuration.json',
            ]
        );

        $jwkSet = json_decode(
            file_get_contents(__DIR__ . '/../metadata/adfs/configuration/jwks_uri.json'),
            true
        );
        $this->jwks = JWK::parseKeySet($jwkSet);

        $this->private_key = file_get_contents(__DIR__ . '/../metadata/adfs/configuration/private.pem');
    }

    public function testValidIdToken()
    {
        $payload = [
            'iss' => 'https://your_domain/adfs',
            'aud' => 'client-id',
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token);

        $this->assertFalse($id_token_jwt->isExpired());
        $this->assertEquals((object) $payload, $id_token_jwt->getPayload());
        $this->assertEquals($id_token, $id_token_jwt->getJWT());
        $this->assertEquals('tester123', $id_token_jwt->get('unique_name'));
    }

    public function testValidIdTokenOtherAuth()
    {
        $payload = [
            'iss' => 'https://your_domain/adfs',
            'aud' => 'other-client-id',
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token, 'other-client-id');

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
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token);
    }

    public function testInvalidIdTokenInvalidIssuer()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid issuer'
        );

        $payload = [
            'iss' => 'https://wrong_domain/adfs',
            'aud' => 'client-id',
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token);
    }

    public function testInvalidIdTokenMissingAudience()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Missing audience'
        );

        $payload = [
            'iss' => 'https://your_domain/adfs',
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token);
    }

    public function testInvalidIdTokenInvalidAudience()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'Invalid audience'
        );

        $payload = [
            'iss' => 'https://your_domain/adfs',
            'aud' => 'wrong-client-id',
            'exp' => time()+10000,
            'unique_name' => 'tester123',
        ];
        $id_token = JWT::encode($payload, $this->private_key, 'RS256', '2lEZNsDIjsBPH94_b7-1z1IvnybfzOIz0hsBamzxCWc');
        $id_token_jwt = new AdfsIdTokenJWT($this->adfs_config, $id_token);
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