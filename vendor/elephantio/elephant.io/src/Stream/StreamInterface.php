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

/**
 * An underlying socket stream to handle raw io.
 *
 * @author Toha <tohenk@yahoo.com>
 */
interface StreamInterface
{
    public const EOL = "\r\n";

    /**
     * Is stream available?
     *
     * @return bool
     */
    public function available();

    /**
     * Check if stream is currently readable.
     *
     * @return bool
     */
    public function readable();

    /**
     * Check if stream is already upgraded.
     *
     * @return bool
     */
    public function upgraded();

    /**
     * Open stream URL.
     */
    public function open();

    /**
     * Close the stream.
     */
    public function close();

    /**
     * Upgrade the stream.
     */
    public function upgrade();

    /**
     * Is the stream was upgraded?
     *
     * @return bool
     */
    public function wasUpgraded();

    /**
     * Read data from underlying stream.
     *
     * @param int $size
     * @return string
     */
    public function read($size = null);

    /**
     * Write data to underlying stream.
     *
     * @param string $data
     * @return int
     */
    public function write($data);

    /**
     * Get url.
     *
     * @return \ElephantIO\SocketUrl
     */
    public function getUrl();

    /**
     * Get errors from the last open attempts.
     *
     * @return array
     */
    public function getErrors();

    /**
     * Get stream metadata.
     *
     * @return array
     */
    public function getMetadata();

    /**
     * Get connection timeout (in second).
     *
     * @return int
     */
    public function getTimeout();

    /**
     * Set connection timeout.
     *
     * @param int $timeout Timeout in second
     */
    public function setTimeout($timeout);
}
