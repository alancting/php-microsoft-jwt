<?php

namespace Alancting\Microsoft\JWT\Base;

use Alancting\Microsoft\JWT\JWK;
use \UnexpectedValueException;

abstract class MicrosoftConfiguration
{
    abstract protected function getDefaultSigningAlgValues();

    private $options;
    private $config_uri;

    private $client_id;

    private $authorization_endpoint;
    private $token_endpoint;
    private $userinfo_endpoint;
    private $device_authorization_endpoint;
    private $end_session_endpoint;

    private $jwks_uri;

    private $issuer;
    private $access_token_issuer;

    private $id_token_signing_alg_values_supported;
    private $token_endpoint_auth_signing_alg_values_supported;

    private $jwks;

    private $loaded;
    private $load_error;

    public function __construct($options = [])
    {
        if (!isset($options['config_uri'])) {
            throw new UnexpectedValueException('Missing config_uri');
        }

        if (!isset($options['client_id'])) {
            throw new UnexpectedValueException('Missing client_id');
        }

        $this->config_uri = $options['config_uri'];
        $this->client_id = $options['client_id'];
        $this->options = $options;
        $this->_load();
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getConfigUri()
    {
        return $this->config_uri;
    }

    public function getJWKs()
    {
        return $this->jwks;
    }

    public function getIdTokenSigingAlgValuesSupported()
    {
        return $this->id_token_signing_alg_values_supported;
    }

    public function getTokenEndpointAuthSigingAlgValuesSupported()
    {
        return $this->token_endpoint_auth_signing_alg_values_supported;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function getAccessTokenIssuer()
    {
        return $this->access_token_issuer;
    }

    public function getAuthorizationEndpoint()
    {
        return $this->authorization_endpoint;
    }

    public function getTokenEndpoint()
    {
        return $this->token_endpoint;
    }

    public function getUserInfoEndpoint()
    {
        return $this->userinfo_endpoint;
    }

    public function getDeviceAuthEndpoint()
    {
        return $this->device_authorization_endpoint;
    }

    public function getEndSessionEndpoint()
    {
        return $this->end_session_endpoint;
    }

    public function getLoadStatus()
    {
        $result = [
            'status' => $this->loaded,
        ];
        if (!$this->loaded) {
            $result['error'] = $this->load_error;
        }

        return $result;
    }

    protected function replaceStr($str, $key, $value)
    {
        return str_replace('{' . $key . '}', $value, $str);
    }

    private function _load()
    {
        try {
            $this->loaded = false;

            $json = $this->getFromUrlOrFile($this->config_uri);
            $data = json_decode($json, true);

            $this->authorization_endpoint = $data['authorization_endpoint'];
            $this->token_endpoint = $data['token_endpoint'];
            $this->userinfo_endpoint = $data['userinfo_endpoint'];
            $this->device_authorization_endpoint = $data['device_authorization_endpoint'];
            $this->end_session_endpoint = $data['end_session_endpoint'];
            $this->jwks_uri = $data['jwks_uri'];
            $this->issuer = $data['issuer'];
            $this->access_token_issuer = (isset($data['access_token_issuer'])) ? $data['access_token_issuer'] : $this->issuer;
            $this->id_token_signing_alg_values_supported = isset($data['id_token_signing_alg_values_supported']) ? $data['id_token_signing_alg_values_supported'] : $this->getDefaultSigningAlgValues();
            $this->token_endpoint_auth_signing_alg_values_supported = isset($data['token_endpoint_auth_signing_alg_values_supported']) ? $data['token_endpoint_auth_signing_alg_values_supported'] : $this->getDefaultSigningAlgValues();

            $jwks_json = $this->getFromUrlOrFile($this->jwks_uri);
            $jwks_data = json_decode($jwks_json, true);

            $this->jwks = JWK::parseKeySet($jwks_data);

            $this->loaded = true;
        } catch (\Exception $e) {
            $this->load_error = $e->getMessage();
        }
    }

    private function getFromUrlOrFile($value)
    {
        $targetUri = $value;
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $targetUri = realpath($value) === false ? __DIR__ . $value : $value;
        }

        $result = @file_get_contents($targetUri);
        if ($result === false) {
            throw new \Exception('Configuration not found');
        }

        return $result;
    }
}