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

namespace ElephantIO\Parser\Polling;

use ElephantIO\Engine\SocketIO;

/**
 * Encode the payload to send as HTTP payload.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Encoder
{
    protected $binary = true;
    protected $encoded = null;

    /**
     * Constructor.
     *
     * @param string $data Payload data
     * @param int $eio Engine IO version
     */
    public function __construct($data, $eio)
    {
        switch ($eio) {
            case SocketIO::EIO_V4:
                break;
            case SocketIO::EIO_V3:
            case SocketIO::EIO_V2:
                $data = $this->binary ? $this->encodeBinaryPayload($data) :
                    $this->encodePayload($data);
                break;
            case SocketIO::EIO_V1:
                break;
        }
        $this->encoded = $data;
    }

    /**
     * Encode payload as string.
     *
     * @param string $data Payload
     * @return string Enocded payload
     */
    protected function encodePayload($data)
    {
        return sprintf('%d:%s', strlen($data), $data);
    }

    /**
     * Encode payload as binary string.
     *
     * @param string $data Payload
     * @return string Enocded payload
     */
    protected function encodeBinaryPayload($data)
    {
        $len = array_map(function($n) {
            return pack('C', $n);
        }, str_split((string) strlen($data)));

        return "\x00" . implode($len) . "\xff" . $data;
    }

    public function __toString()
    {
        return (string) $this->encoded;
    }
}
