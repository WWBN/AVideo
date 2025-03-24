<?php

/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

namespace ElephantIO;

use ElephantIO\Exception\MalformedUrlException;

/**
 * Represents a socket URL.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class SocketUrl
{
    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var array
     */
    protected $parsed = null;

    /**
     * @var string
     */
    protected $sio = 'socket.io';

    /**
     * Constructor.
     *
     * @param string $url The URL
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->parsed = $this->parse($url);
    }

    /**
     * Parse an url into parts we may expect.
     *
     * @param string $url
     * @return string[] information on the given URL
     */
    protected function parse($url)
    {
        if (false === $parsed = parse_url($url)) {
            throw new MalformedUrlException($url);
        }

        $result = array_replace([
            'scheme' => 'http',
            'host' => 'localhost',
            'query' => []
        ], $parsed);
        if (!isset($result['port'])) {
            $result['port'] = 'https' === $result['scheme'] ? 443 : 80;
        }
        if (!is_array($result['query'])) {
            $query = null;
            parse_str($result['query'], $query);
            $result['query'] = $query;
        }
        $result['secure'] = 'https' === $result['scheme'];

        return $result;
    }

    /**
     * Get socket.io path. If not set, default to socket.io.
     *
     * @return string
     */
    public function getSioPath()
    {
        return $this->sio;
    }

    /**
     * Set socket.io path.
     *
     * @param string $sio socket.io path
     * @return \ElephantIO\SocketUrl
     */
    public function setSioPath($sio)
    {
        $this->sio = $sio;

        return $this;
    }

    /**
     * Get raw URL.
     *
     *  @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get host and port from parsed URL.
     *
     *  @return string
     */
    public function getHost()
    {
        return sprintf('%s:%d', $this->parsed['host'], $this->parsed['port']);
    }

    /**
     * Get address from parsed URL.
     *
     *  @return string
     */
    public function getAddress()
    {
        return sprintf('%s://%s', $this->parsed['secure'] ? 'ssl' : 'tcp', $this->getHost());
    }

    /**
     * Get socket URI.
     *
     * @param string $path Path
     * @param array $query Key-value query string
     * @return string
     */
    public function getUri($path = null, $query = [])
    {
        $paths = [];
        if (isset($this->parsed['path']) && $root = trim((string) $this->parsed['path'], '/')) {
            $paths[] = $root;
        }
        $paths[] = $this->sio;
        if ($path = trim((string) $path, '/')) {
            $paths[] = $path;
        }
        $uri = sprintf('/%s/', implode('/', $paths));
        $qs = array_merge($this->parsed['query'], $query);
        if (count($qs)) {
            $uri .= '?' . http_build_query($qs);
        }

        return $uri;
    }
}
