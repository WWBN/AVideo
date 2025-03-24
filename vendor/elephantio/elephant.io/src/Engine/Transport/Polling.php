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

namespace ElephantIO\Engine\Transport;

use ElephantIO\Engine\SocketIO;
use ElephantIO\Engine\Transport;
use ElephantIO\Parser\Polling\Decoder;
use ElephantIO\Parser\Polling\Encoder;
use ElephantIO\Stream\StreamInterface;
use ElephantIO\Util;

/**
 * HTTP polling transport.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Polling extends Transport
{
    public const MIMETYPE_OCTET_STREAM = 'application/octet-stream';
    public const MIMETYPE_JSON = 'application/json';
    public const MIMETYPE_PLAIN_TEXT = 'text/plain; charset=UTF-8';

    /**
     * @var array
     */
    protected $result = null;

    /**
     * @var int
     */
    protected $bytesWritten = null;

    /**
     * Get connection default headers.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        $context = $this->sio->getContext();
        $headers = ['Accept' => '*/*'];
        if ($this->sio->getOptions()->cors) {
            $headers['Origin'] = $context['headers']['Origin'] ?? $this->sio->getUrl();
            $headers['Referer'] = $context['headers']['Referer'] ?? $this->sio->getUrl();
        }
        if ($ua = $this->sio->getOptions()->ua) {
            if (is_string($ua)) {
                $headers['User-Agent'] = $ua;
            } else {
                $headers['User-Agent'] = sprintf('Elephant.io/%s', ($version = Util::getVersion()) ? $version : '*');
            }
        }
        if (count($cookies = $this->sio->getCookies())) {
            $headers['Cookie'] = implode('; ', $cookies);
        }
        $headers['Connection'] = $this->sio->getOptions()->reuse_connection ? 'keep-alive' : 'close';

        return $headers;
    }

    /**
     * Get websocket upgrade headers.
     *
     * @return array
     */
    protected function getUpgradeHeaders()
    {
        $hash = sha1(uniqid(mt_rand(), true), true);
        if ($this->sio->getOptions()->version >= SocketIO::EIO_V3) {
            $hash = substr($hash, 0, 16);
        }

        return array_merge($this->getDefaultHeaders(), [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Key' => base64_encode($hash),
            'Sec-WebSocket-Version' => '13',
        ]);
    }

    /**
     * Perform HTTP request to server.
     *
     * @param string $uri
     * @param array $headers Key-value pairs
     * @param array $options Request options
     * @return bool
     */
    protected function request($uri, $headers = [], $options = [])
    {
        $stream = $this->sio->getStream(true);
        if (!$stream->available()) {
            return;
        }

        $method = isset($options['method']) ? $options['method'] : 'GET';
        $timeout = isset($options['timeout']) ? $options['timeout'] : 0;
        $skip_body = isset($options['skip_body']) ? $options['skip_body'] : false;
        $payload = isset($options['payload']) ? $options['payload'] : null;

        if ($payload) {
            $contentType = $headers['Content-Type'] ?? null;
            if (null === $contentType) {
                if (false !== strpos($payload, "\x00")) {
                    $contentType = static::MIMETYPE_OCTET_STREAM;
                } else {
                    $contentType = static::MIMETYPE_PLAIN_TEXT;
                    $payload = mb_convert_encoding($payload, 'UTF-8', 'ISO-8859-1');
                }
            }
            if ($contentType) {
                $headers = array_merge([
                    'Content-Type' => $contentType,
                    'Content-Length' => strlen($payload),
                ], $headers);
            }
        }

        $headers = array_merge(['Host' => $stream->getUrl()->getHost()], $headers);
        if (isset($this->sio->getOptions()->headers)) {
            $headers = array_merge($headers, $this->sio->getOptions()->headers);
        }
        $request = array_merge([
            sprintf('%s %s HTTP/1.1', strtoupper($method), $uri),
        ], Util::normalizeHeaders($headers));

        $request = implode(StreamInterface::EOL, $request) . StreamInterface::EOL . StreamInterface::EOL . $payload;

        $this->bytesWritten = $stream->write($request);

        $this->result = ['status' => null, 'headers' => [], 'body' => null];

        // wait for response
        $header = true;
        $len = null;
        $closed = null;
        $contentType = null;
        $chunked = null;
        $start = microtime(true);
        while (true) {
            if ($timeout > 0 && microtime(true) - $start >= $timeout) {
                $this->timedout = true;
                break;
            }
            if (!$stream->readable()) {
                break;
            }
            if ($content = $stream->read($header ? null : $len)) {
                if ($content === StreamInterface::EOL && $header && count($this->result['headers'])) {
                    if ($skip_body) {
                        break;
                    }
                    $header = false;
                } else {
                    if ($header) {
                        if ($content = trim($content)) {
                            if (null === $this->result['status']) {
                                $matches = null;
                                if (preg_match('/^(?P<HTTP>(HTTP|http)\/(\d+(\.\d+)?))\s(?P<CODE>(\d+))\s(?P<STATUS>(.*))/', $content, $matches)) {
                                    $this->result['status'] = [$matches['HTTP'], (int) $matches['CODE'], $matches['STATUS']];
                                }
                            } else {
                                list($key, $value) = explode(':', $content, 2);
                                $value = trim($value);
                                if (null === $len &&
                                    strtolower($key) === 'content-length') {
                                    $len = (int) $value;
                                }
                                if (null === $chunked &&
                                    strtolower($key) === 'transfer-encoding' &&
                                    strtolower($value) === 'chunked') {
                                    $chunked = true;
                                }
                                if (null === $contentType &&
                                    strtolower($key) === 'content-type') {
                                    $contentType = $value;
                                }
                                if (null === $closed &&
                                    strtolower($key) === 'connection' &&
                                    strtolower($value) === 'close') {
                                    $closed = true;
                                }
                                // allow multiple values
                                if (isset($this->result['headers'][$key])) {
                                    if (!is_array($this->result['headers'][$key])) {
                                        $this->result['headers'][$key] = [$this->result['headers'][$key]];
                                    }
                                    $this->result['headers'][$key][] = $value;
                                } else {
                                    $this->result['headers'][$key] = $value;
                                }
                            }
                        }
                    } else {
                        $this->result['body'] .= $content;
                        if ($chunked && null === $len && $content === '0' . StreamInterface::EOL) {
                            $this->result['body'] = $this->decodeChunked($this->result['body']);
                            break;
                        }
                        if ($len === strlen($this->result['body'])) {
                            break;
                        }
                    }
                }
            }
            usleep($this->sio->getOptions()->wait);
        }
        // decode JSON if necessary
        if ($this->result['body'] && $contentType === static::MIMETYPE_JSON) {
            $this->result['body'] = json_decode($this->result['body'], true);
        }
        if ($closed) {
            $this->logger->debug('Connection closed by server');
            $stream->close();
        }
        if ($this->result['status']) {
            $this->setHeartbeat();
        }

        return count($this->result['headers']) ? true : false;
    }

    /**
     * Get response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return is_array($this->result) ? $this->result['headers'] : null;
    }

    /**
     * Get response body.
     *
     * @return string
     */
    public function getBody()
    {
        return is_array($this->result) ? $this->result['body'] : null;
    }

    /**
     * Get response status.
     *
     * @return array Index 0 is HTTP version, index 1 is status code, and index 2 is status message
     */
    public function getStatus()
    {
        return is_array($this->result) ? $this->result['status'] : null;
    }

    /**
     * Get response status code.
     *
     * @return int Status code
     */
    public function getStatusCode()
    {
        return is_array($status = $this->getStatus()) ? $status[1] : null;
    }

    /**
     * Get response header.
     *
     * @param string $name Header name
     * @return string|array
     */
    public function getHeader($name)
    {
        if (is_array($headers = $this->getHeaders())) {
            foreach ($headers as $k => $v) {
                if (strtolower($name) === strtolower($k)) {
                    return $v;
                }
            }
        }
    }

    /**
     * Get cookies from response headers.
     *
     * @return array
     */
    public function getCookies()
    {
        $cookies = [];
        if ($cookie = $this->getHeader('Set-Cookie')) {
            foreach ((array) $cookie as $value) {
                $value = explode(';', $value);
                $cookies[] = $value[0];
            }
        }

        return $cookies;
    }

    /**
     * Decode chunked response.
     *
     * Copied from https://stackoverflow.com/questions/10793017/how-to-easily-decode-http-chunked-encoded-string-when-making-raw-http-request
     *
     * @param string $str Chunked string
     * @return string
     */
    protected function decodeChunked($str)
    {
        for ($res = ''; !empty($str); $str = trim($str)) {
            $pos = strpos($str, StreamInterface::EOL);
            $len = hexdec(substr($str, 0, $pos));
            $res .= substr($str, $pos + 2, $len);
            $str = substr($str, $pos + 2 + $len);
        }

        return $res;
    }

    /** {@inheritDoc} */
    public function send($data, $parameters = [])
    {
        if (!$data instanceof Encoder) {
            $data = new Encoder($data, $this->sio->getOptions()->version);
        }
        $options = ['method' => 'POST', 'payload' => $data, 'timeout' => $this->sio->getOptions()->timeout];
        $headers = $this->getDefaultHeaders();
        $code = 200;
        $transport = isset($parameters['transport']) ? $parameters['transport'] : $this->sio->getOptions()->transport;
        $uri = $this->sio->buildQuery($this->sio->buildQueryParameters($transport));
        $this->request($uri, $headers, $options);

        if ($this->getStatusCode() === $code) {
            return $this->bytesWritten;
        }
    }

    /** {@inheritDoc} */
    public function recv($timeout = 0, $parameters = [])
    {
        $this->timedout = null;
        $options = ['timeout' => $timeout];
        if (isset($parameters['upgrade']) && $parameters['upgrade']) {
            $headers = $this->getUpgradeHeaders();
            $options['skip_body'] = true;
            $code = 101;
        } else {
            $headers = $this->getDefaultHeaders();
            $code = 200;
        }
        $transport = isset($parameters['transport']) ? $parameters['transport'] :
            $this->sio->getOptions()->transport;
        $uri = $this->sio->buildQuery($this->sio->buildQueryParameters($transport));
        $this->request($uri, $headers, $options);

        if ($this->getStatusCode() === $code) {
            return new Decoder((string) $this->getBody(), $this->sio->getOptions()->version,
                $this->getHeader('Content-Type') === static::MIMETYPE_OCTET_STREAM);
        }
    }
}
