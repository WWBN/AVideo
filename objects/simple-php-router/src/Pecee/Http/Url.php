<?php

namespace Pecee\Http;

use Pecee\Http\Exceptions\MalformedUrlException;

class Url
{
    private $originalUrl;
    private $data = [
        'scheme'   => null,
        'host'     => null,
        'port'     => null,
        'user'     => null,
        'pass'     => null,
        'path'     => null,
        'query'    => null,
        'fragment' => null,
    ];

    /**
     * Url constructor.
     * @param string $url
     * @throws MalformedUrlException
     */
    public function __construct($url)
    {
        $this->originalUrl = $url;
        $this->data = $this->parseUrl($url) + $this->data;

        if (isset($this->data['path']) === true && $this->data['path'] !== '/') {
            $this->data['path'] = rtrim($this->data['path'], '/') . '/';
        }

    }

    /**
     * Check if url is using a secure protocol like https
     * @return bool
     */
    public function isSecure()
    {
        return (strtolower($this->getScheme()) === 'https');
    }

    /**
     * Checks if url is relative
     * @return bool
     */
    public function isRelative()
    {
        return ($this->getHost() === null);
    }

    /**
     * Get url scheme
     * @return string|null
     */
    public function getScheme()
    {
        return $this->data['scheme'];
    }

    /**
     * Get url host
     * @return string|null
     */
    public function getHost()
    {
        return $this->data['host'];
    }

    /**
     * Get url port
     * @return int|null
     */
    public function getPort()
    {
        return ($this->data['port'] !== null) ? (int)$this->data['port'] : null;
    }

    /**
     * Parse username from url
     * @return string|null
     */
    public function getUserName()
    {
        return $this->data['user'];
    }

    /**
     * Parse password from url
     * @return string|null
     */
    public function getPassword()
    {
        return $this->data['pass'];
    }

    /**
     * Get path from url
     * @return string
     */
    public function getPath()
    {
        return $this->data['path'];
    }

    /**
     * Get querystring from url
     * @return string|null
     */
    public function getQueryString()
    {
        return $this->data['query'];
    }

    /**
     * Get fragment from url (everything after #)
     * @return string|null
     */
    public function getFragment()
    {
        return $this->data['fragment'];
    }

    /**
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * UTF-8 aware parse_url() replacement.
     * @param string $url
     * @param int $component
     * @throws MalformedUrlException
     * @return array
     */
    public function parseUrl($url, $component = -1)
    {
        $encodedUrl = preg_replace_callback(
            '/[^:\/@?&=#]+/u',
            function ($matches) {
                return urlencode($matches[0]);
            },
            $url
        );

        $parts = parse_url($encodedUrl, $component);

        if ($parts === false) {
            throw new MalformedUrlException('Malformed URL: ' . $url);
        }

        return array_map('urldecode', $parts);
    }

    /**
     * Returns data array with information about the url
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->getOriginalUrl();
    }

}