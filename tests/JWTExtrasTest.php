<?php

namespace Alancting\Microsoft\Tests;

use Alancting\Microsoft\JWT\JWT;

class JWTExtrasTest extends JWTTest
{
    public function testDecodeInvalidHeaderNoAlg()
    {
        $jwt = 'eyJ0eXAiOiJKV1QifQ.eyJtZXNzYWdlIjoiYWJjIiwibmJmIjoxNjAxODgwODAyfQ.spSGMmD4PuujRAfqTMNuD2WL6MxNnBdhbL7A-uTbgpc';
        $this->setExpectedException(
            'UnexpectedValueException',
            'Empty algorithm'
        );
        JWT::decode($jwt, 'my_key', ['HS256']);
    }

    public function testDecodeInvalidHeaderUnsupportedAlg()
    {
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzk5OSJ9.eyJtZXNzYWdlIjoiYWJjIiwibmJmIjoxNjAxODgxMjg0fQ.V43ebYjSX8b7PyTWv5x7Q12g550ZP3shut19XrCCsaQ';
        $this->setExpectedException(
            'UnexpectedValueException',
            'Algorithm not supported'
        );
        JWT::decode($jwt, 'my_key', ['HS256']);
    }

    public function testDecodeInvalidHeaderNoKidAndX5tJWK()
    {
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtZXNzYWdlIjoiYWJjIiwibmJmIjoxNjAxODgxNjc4fQ.5m_UELHsCus6gJyOEXOXkDcuG0qjlUO3dxpR9GT1QNQRR';
        $this->setExpectedException(
            'UnexpectedValueException',
            '"kid" && "x5t" empty, unable to lookup correct key'
        );
        JWT::decode($jwt, ['my_key'], ['HS256']);
    }

    public function testDecodeInvalidHeaderNoKidJWK()
    {
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6ImtpZGtleSJ9.eyJtZXNzYWdlIjoiYWJjIiwibmJmIjoxNjAxODgxNjQ5fQ.pr3XRYQGfRBMO0YJb5365XOHsUBKpTueaJNH1I2L8EURR';
        $this->setExpectedException(
            'UnexpectedValueException',
            '"kid" invalid, unable to lookup correct key'
        );
        JWT::decode($jwt, ['my_key'], ['HS256']);
    }

    public function testDecodeInvalidHeaderNoX5tJWK()
    {
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsIng1dCI6Ing1dGtleSJ9.eyJtZXNzYWdlIjoiYWJjIiwibmJmIjoxNjAxODgxNjI4fQ.OuhrKy2YVRhDzscPnBMOPuZDxytACA5wH_YdO32FnGERR';
        $this->setExpectedException(
            'UnexpectedValueException',
            '"x5t" invalid, unable to lookup correct key'
        );
        JWT::decode($jwt, ['my_key'], ['HS256']);
    }

    public function testSignInvalidAlg()
    {
        $msg = 'testmsg';
        $this->setExpectedException(
            'DomainException',
            'Algorithm not supported'
        );
        JWT::sign($msg, 'my_key', 'HS999');
    }

    public function testVerifyInvalidAlg()
    {
        $msg = 'testmsg';
        $sign = 'sign';

        $this->setExpectedException(
            'DomainException',
            'Algorithm not supported'
        );
        $keys = $this->invokeMethod(
            new JWT,
            'verify',
            [
                'msg' => $msg,
                'signature' => $sign,
                'key' => 'my_key',
                'alg' => 'HS999',
            ]
        );
    }

    public function testIsExpiredEmptyTimestamp()
    {
        $future_payload = new \stdClass();
        $future_payload->exp = time() + 1000000;

        $expired_payload = new \stdClass();
        $expired_payload->exp = time() - 1000000;

        $this->assertFalse(JWT::isExpired($future_payload));
        $this->assertTrue(JWT::isExpired($expired_payload));
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

    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}