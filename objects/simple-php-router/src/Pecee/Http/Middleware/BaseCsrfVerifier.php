<?php

namespace Pecee\Http\Middleware;

use Pecee\Http\Middleware\Exceptions\TokenMismatchException;
use Pecee\Http\Request;
use Pecee\Http\Security\CookieTokenProvider;
use Pecee\Http\Security\ITokenProvider;

class BaseCsrfVerifier implements IMiddleware
{
    const POST_KEY = 'csrf-token';
    const HEADER_KEY = 'X-CSRF-TOKEN';

    protected $except;
    protected $tokenProvider;

    /**
     * BaseCsrfVerifier constructor.
     * @throws \Pecee\Http\Security\Exceptions\SecurityException
     */
    public function __construct()
    {
        $this->tokenProvider = new CookieTokenProvider();
    }

    /**
     * Check if the url matches the urls in the except property
     * @param Request $request
     * @return bool
     */
    protected function skip(Request $request)
    {
        if ($this->except === null || count($this->except) === 0) {
            return false;
        }

        $max = count($this->except) - 1;

        for ($i = $max; $i >= 0; $i--) {
            $url = $this->except[$i];

            $url = rtrim($url, '/');
            if ($url[strlen($url) - 1] === '*') {
                $url = rtrim($url, '*');
                $skip = (stripos($request->getUrl()->getOriginalUrl(), $url) === 0);
            } else {
                $skip = ($url === $request->getUrl()->getOriginalUrl());
            }

            if ($skip === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle request
     *
     * @param Request $request
     * @throws TokenMismatchException
     */
    public function handle(Request $request)
    {

        if ($this->skip($request) === false && in_array($request->getMethod(), ['post', 'put', 'delete'], false) === true) {

            $token = $request->getInput()->get(static::POST_KEY, null, 'post');

            // If the token is not posted, check headers for valid x-csrf-token
            if ($token === null) {
                $token = $request->getHeader(static::HEADER_KEY);
            }

            if ($this->tokenProvider->validate($token) === false) {
                throw new TokenMismatchException('Invalid CSRF-token.');
            }

        }

        // Refresh existing token
        $this->tokenProvider->refresh();

    }

    public function getTokenProvider()
    {
        return $this->tokenProvider;
    }

    /**
     * Set token provider
     * @param ITokenProvider $provider
     */
    public function setTokenProvider(ITokenProvider $provider)
    {
        $this->tokenProvider = $provider;
    }

}