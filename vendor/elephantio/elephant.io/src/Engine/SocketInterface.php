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

namespace ElephantIO\Engine;

/**
 * Socket host interface.
 *
 * @author Toha <tohenk@yahoo.com>
 */
interface SocketInterface
{
    /**
     * Get options.
     *
     * @return \ElephantIO\Engine\Option
     */
    public function getOptions();

    /**
     * Get socket URL.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get socket stream.
     *
     * @param bool $create True to create the stream
     * @return \ElephantIO\Stream\StreamInterface|null
     */
    public function getStream($create = false);

    /**
     * Get stream context.
     *
     * @return array<string, mixed>
     */
    public function getContext();

    /**
     * Get cookies.
     *
     * @return string[]
     */
    public function getCookies();

    /**
     * Get session.
     *
     * @return \ElephantIO\Engine\Session|null
     */
    public function getSession();

    /**
     * Send ping to server.
     *
     * @return bool
     */
    public function ping();

    /**
     * Build query string parameters.
     *
     * @param ?string $transport
     * @return array<string, mixed>
     */
    public function buildQueryParameters($transport);

    /**
     * Build query from parameters.
     *
     * @param array<string, mixed> $query
     * @return string
     */
    public function buildQuery($query);
}
