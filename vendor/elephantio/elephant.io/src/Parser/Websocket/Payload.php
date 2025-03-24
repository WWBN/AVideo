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

namespace ElephantIO\Parser\Websocket;

/**
 * Payload for sending data through the websocket.
 *
 * Loosely based on the work of the following:
 *   - Ludovic Barreca (@ludovicbarreca)
 *   - Byeoung Wook (@kbu1564)
 *
 * @link https://tools.ietf.org/html/rfc6455#section-5.2
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
abstract class Payload
{
    public const OPCODE_CONTINUE = 0x0;
    public const OPCODE_TEXT = 0x1;
    public const OPCODE_BINARY = 0x2;
    public const OPCODE_CLOSE = 0x8;
    public const OPCODE_PING = 0x9;
    public const OPCODE_PONG = 0xA;

    public const OPCODE_NON_CONTROL_RESERVED_1 = 0x3;
    public const OPCODE_NON_CONTROL_RESERVED_2 = 0x4;
    public const OPCODE_NON_CONTROL_RESERVED_3 = 0x5;
    public const OPCODE_NON_CONTROL_RESERVED_4 = 0x6;
    public const OPCODE_NON_CONTROL_RESERVED_5 = 0x7;

    public const OPCODE_CONTROL_RESERVED_1 = 0xB;
    public const OPCODE_CONTROL_RESERVED_2 = 0xC;
    public const OPCODE_CONTROL_RESERVED_3 = 0xD;
    public const OPCODE_CONTROL_RESERVED_4 = 0xE;
    public const OPCODE_CONTROL_RESERVED_5 = 0xF;

    protected $fin = 0b1; // only one frame is necessary
    protected $rsv = [0b0, 0b0, 0b0]; // rsv1, rsv2, rsv3

    protected $mask = false;
    protected $maskKey = "\x00\x00\x00\x00";

    protected $opCode;

    protected $maxPayload = 0;

    /**
     * Get maximum payload length.
     *
     * @return int
     */
    public function getMaxPayload()
    {
        return $this->maxPayload;
    }

    /**
     * Set maximum payload length.
     *
     * @param int $length
     * @return \ElephantIO\Parser\Websocket\Payload
     */
    public function setMaxPayload($length)
    {
        $this->maxPayload = $length;

        return $this;
    }

    /**
     * Mask a data according to the current mask key.
     *
     * @param string $data Data to mask
     * @return string Masked data
     */
    protected function maskData($data)
    {
        $masked = '';
        $data = \str_split($data);
        $key = \str_split($this->maskKey);

        foreach ($data as $i => $letter) {
            $masked .= $letter ^ $key[$i % 4];
        }

        return $masked;
    }
}
