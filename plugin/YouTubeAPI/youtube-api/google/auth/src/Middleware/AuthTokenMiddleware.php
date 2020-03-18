<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Middleware;

use Google\Auth\CacheTrait;
use Google\Auth\FetchAuthTokenInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;

/**
 * AuthTokenMiddleware is a Guzzle Middleware that adds an Authorization header
 * provided by an object implementing FetchAuthTokenInterface.
 *
 * The FetchAuthTokenInterface#fetchAuthToken is used to obtain a hash; one of
 * the values value in that hash is added as the authorization header.
 *
 * Requests will be accessed with the authorization header:
 *
 * 'Authorization' 'Bearer <value of auth_token>'
 */
class AuthTokenMiddleware
{
    use CacheTrait;

    const DEFAULT_CACHE_LIFETIME = 1500;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var callback
     */
    private $httpHandler;

    /**
     * @var FetchAuthTokenInterface
     */
    private $fetcher;

    /**
     * @var array configuration
     */
    private $cacheConfig;

    /**
     * @var callable
     */
    private $tokenCallback;

    /**
     * Creates a new AuthTokenMiddleware.
     *
     * @param FetchAuthTokenInterface $fetcher is used to fetch the auth token
     * @param array $cacheConfig configures the cache
     * @param CacheItemPoolInterface $cache (optional) caches the token.
     * @param callable $httpHandler (optional) callback which delivers psr7 request
     * @param callable $tokenCallback (optional) function to be called when a new token is fetched.
     */
    public function __construct(
        FetchAuthTokenInterface $fetcher,
        array $cacheConfig = null,
        CacheItemPoolInterface $cache = null,
        callable $httpHandler = null,
        callable $tokenCallback = null
    ) {
        $this->fetcher = $fetcher;
        $this->httpHandler = $httpHandler;
        $this->tokenCallback = $tokenCallback;
        if (!is_null($cache)) {
            $this->cache = $cache;
            $this->cacheConfig = array_merge([
                'lifetime' => self::DEFAULT_CACHE_LIFETIME,
                'prefix' => '',
            ], $cacheConfig);
        }
    }

    /**
     * Updates the request with an Authorization header when auth is 'google_auth'.
     *
     *   use Google\Auth\Middleware\AuthTokenMiddleware;
     *   use Google\Auth\OAuth2;
     *   use GuzzleHttp\Client;
     *   use GuzzleHttp\HandlerStack;
     *
     *   $config = [..<oauth config param>.];
     *   $oauth2 = new OAuth2($config)
     *   $middleware = new AuthTokenMiddleware(
     *       $oauth2,
     *       ['prefix' => 'OAuth2::'],
     *       $cache = new Memcache()
     *   );
     *   $stack = HandlerStack::create();
     *   $stack->push($middleware);
     *
     *   $client = new Client([
     *       'handler' => $stack,
     *       'base_uri' => 'https://www.googleapis.com/taskqueue/v1beta2/projects/',
     *       'auth' => 'google_auth' // authorize all requests
     *   ]);
     *
     *   $res = $client->get('myproject/taskqueues/myqueue');
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // Requests using "auth"="google_auth" will be authorized.
            if (!isset($options['auth']) || $options['auth'] !== 'google_auth') {
                return $handler($request, $options);
            }

            $request = $request->withHeader('Authorization', 'Bearer ' . $this->fetchToken());

            return $handler($request, $options);
        };
    }

    /**
     * Determine if token is available in the cache, if not call fetcher to
     * fetch it.
     *
     * @return string
     */
    private function fetchToken()
    {
        // TODO: correct caching; update the call to setCachedValue to set the expiry
        // to the value returned with the auth token.
        //
        // TODO: correct caching; enable the cache to be cleared.
        $cached = $this->getCachedValue();
        if (!empty($cached)) {
            return $cached;
        }

        $auth_tokens = $this->fetcher->fetchAuthToken($this->httpHandler);

        if (array_key_exists('access_token', $auth_tokens)) {
            $this->setCachedValue($auth_tokens['access_token']);

            // notify the callback if applicable
            if ($this->tokenCallback) {
                call_user_func($this->tokenCallback, $this->getFullCacheKey(), $auth_tokens['access_token']);
            }

            return $auth_tokens['access_token'];
        }
    }
}
