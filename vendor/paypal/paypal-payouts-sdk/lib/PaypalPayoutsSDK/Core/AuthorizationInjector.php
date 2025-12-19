<?php

namespace PaypalPayoutsSDK\Core;

use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;
use PayPalHttp\HttpClient;

class AuthorizationInjector implements Injector
{
    private $client;
    private $environment;
    private $refreshToken;
    public $accessToken;

    public function __construct(HttpClient $client, PayPalEnvironment $environment, $refreshToken)
    {
        $this->client = $client;
        $this->environment = $environment;
        $this->refreshToken = $refreshToken;
    }

    public function inject($request)
    {
        if (!$this->hasAuthHeader($request) && !$this->isAuthRequest($request))
        {
            if (is_null($this->accessToken) || $this->accessToken->isExpired())
            {
                $this->accessToken = $this->fetchAccessToken();
            }
            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    private function fetchAccessToken()
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment, $this->refreshToken));
        $accessToken = $accessTokenResponse->result;
        return new AccessToken($accessToken->access_token, $accessToken->token_type, $accessToken->expires_in);
    }

    private function isAuthRequest($request)
    {
        return $request instanceof AccessTokenRequest || $request instanceof RefreshTokenRequest;
    }

    private function hasAuthHeader(HttpRequest $request)
    {
        return array_key_exists("Authorization", $request->headers);
    }
}
