<?php

namespace Alancting\Microsoft\Tests;

use Alancting\Microsoft\JWT\JWK;

class JWKExtrasTest extends JWKTest
{
    public function testParseKeySetMissingKeys()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            '"keys" member must exist in the JWK Set'
        );

        $keys = JWK::parseKeySet([]);
    }

    public function testParseKeySetEmptyKeys()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'JWK Set did not contain any keys'
        );

        $keys = JWK::parseKeySet(['keys' => []]);
    }

    public function testParseKeyMissingKeys()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'JWK must not be empty'
        );

        $keys = $this->invokeMethod(new JWK, 'parseKey', ['jwk' => []]);
    }

    public function testParseKeyPrivateKey()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'RSA private keys are not supported'
        );

        $jwk = json_decode(
            file_get_contents(__DIR__ . '/metadata/jwk/rsa-public-private.json'),
            true
        );

        $keys = $this->invokeMethod(new JWK, 'parseKey', ['jwk' => $jwk]);
    }

    public function testParseKeyKeyMissingNorE()
    {
        $this->setExpectedException(
            'UnexpectedValueException',
            'RSA keys must contain values for both "n" and "e"'
        );

        $jwk = json_decode(
            file_get_contents(__DIR__ . '/metadata/jwk/rsa-public-no-n.json'),
            true
        );

        $keys = $this->invokeMethod(new JWK, 'parseKey', ['jwk' => $jwk]);

        $this->setExpectedException(
            'UnexpectedValueException',
            'RSA keys must contain values for both "n" and "e"'
        );

        $jwk = json_decode(
            file_get_contents(__DIR__ . '/metadata/jwk/rsa-public-no-e.json'),
            true
        );

        $keys = $this->invokeMethod(new JWK, 'parseKey', ['jwk' => $jwk]);
    }

    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}