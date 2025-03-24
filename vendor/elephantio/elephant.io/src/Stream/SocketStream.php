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

namespace ElephantIO\Stream;

use ElephantIO\Util;
use RuntimeException;

/**
 * Basic stream to connect to the socket server which behave as an HTTP client.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class SocketStream extends Stream
{
    /**
     * @var resource
     */
    protected $handle = null;

    /**
     * @var bool
     */
    protected $upgraded = null;

    /**
     * @var bool
     */
    protected $wasUpgraded = null;

    /**
     * @var array
     */
    protected $errors = null;

    /**
     * @var array
     */
    protected $metadata = null;

    /**
     * {@inheritDoc}
     */
    protected function initialize()
    {
        $this->open();
    }

    /**
     * Get connection timeout (in second).
     *
     * @return int
     */
    public function getTimeout()
    {
        return isset($this->options['timeout']) ? $this->options['timeout'] : 5;
    }

    /**
     * Set connection timeout.
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        if ($this->getTimeout() != $timeout) {
            $this->options['timeout'] = $timeout;
            $this->applyTimeout($timeout);
        }
    }

    /**
     * Apply connection timeout to underlying stream.
     *
     * @param int $timeout
     */
    protected function applyTimeout($timeout)
    {
        if (is_resource($this->handle)) {
            stream_set_timeout($this->handle, $timeout);
        }
    }

    /**
     * Read metadata from socket.
     *
     * @return array
     */
    protected function readMetadata()
    {
        if (is_resource($this->handle)) {
            $this->metadata = stream_get_meta_data($this->handle);

            return $this->metadata;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function available()
    {
        return is_resource($this->handle);
    }

    /**
     * {@inheritDoc}
     */
    public function readable()
    {
        if ($metadata = $this->readMetadata()) {
            return $metadata['eof'] ? false : true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function upgraded()
    {
        return $this->upgraded;
    }

    /**
     * {@inheritDoc}
     */
    public function open()
    {
        $errors = [null, null];
        $timeout = $this->getTimeout();
        $address = $this->url->getAddress();

        $this->logger->info(sprintf('Stream connect: %s', $address));
        $flags = STREAM_CLIENT_CONNECT;
        if (!isset($this->options['persistent']) || $this->options['persistent']) {
            $flags |= STREAM_CLIENT_PERSISTENT;
        }

        $context = !isset($this->context['headers']) ? $this->context :
            array_merge($this->context, ['headers' => Util::normalizeHeaders($this->context['headers'])]);

        $this->handle = @stream_socket_client(
            sprintf('%s/%s', $address, uniqid()),
            $errors[0],
            $errors[1],
            $timeout,
            $flags,
            stream_context_create($context)
        );

        if (is_resource($this->handle)) {
            stream_set_blocking($this->handle, false);
            $this->applyTimeout($timeout);
        } else {
            $this->errors = $errors;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade()
    {
        if (null === $this->upgraded) {
            $this->upgraded = true;
            $this->wasUpgraded = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function wasUpgraded()
    {
        if ($this->wasUpgraded) {
            $this->wasUpgraded = false;

            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if (!is_resource($this->handle)) {
            return;
        }
        @stream_socket_shutdown($this->handle, STREAM_SHUT_RDWR);
        fclose($this->handle);
        $this->handle = null;
    }

    /**
     * {@inheritDoc}
     */
    public function read($size = null)
    {
        if (is_resource($this->handle)) {
            $data = null !== $size ? fread($this->handle, (int) $size) : fgets($this->handle);
            if (false !== $data && strlen($data)) {
                $this->logger->debug(sprintf('Stream receive: %s', Util::truncate(rtrim($data))));
            }

            return $data;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
    {
        $bytes = null;
        if (is_resource($this->handle)) {
            $data = (string) $data;
            $len = strlen($data);
            while (true) {
                if (false === ($written = fwrite($this->handle, $data))) {
                    throw new RuntimeException(sprintf('Unable to write %d data to stream!', strlen($data)));
                }
                if ($written > 0) {
                    $lines = explode(static::EOL, substr($data, 0, $written));
                    foreach ($lines as $line) {
                        $this->logger->debug(sprintf('Stream write: %s', Util::truncate($line)));
                    }
                    if (null === $bytes) {
                        $bytes = $written;
                    } else {
                        $bytes += $written;
                    }
                    // all data has been written
                    if ($len === $bytes) {
                        break;
                    }
                    // this is the remaining data
                    $data = substr($data, $written);
                }
            }
        }

        return $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
