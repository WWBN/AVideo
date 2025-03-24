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
 * Encode the payload before sending it to a frame.
 *
 * Based on the work of the following:
 *   - Ludovic Barreca (@ludovicbarreca), project founder
 *   - Byeoung Wook (@kbu1564) in #49
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Encoder extends Payload
{
    private $data;

    /** @var string */
    private $payload;

    /** @var string[] */
    private $fragments = [];

    /**
     * @param string  $data   data to encode
     * @param integer $opCode OpCode to use (one of Payload's constant)
     * @param bool    $mask   Should we use a mask ?
     */
    public function __construct($data, $opCode, $mask)
    {
        $this->data = $data;
        $this->opCode = $opCode;
        $this->mask = (bool) $mask;

        if (true === $this->mask) {
            $this->maskKey = \openssl_random_pseudo_bytes(4);
        }
    }

    /**
     * Get payload fragments.
     *
     * @return string[]
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * Encode a data payload.
     *
     * @param string $data
     * @param int $opCode
     * @return string
     */
    protected function doEncode($data, $opCode)
    {
        $pack = '';
        $length = \strlen($data);

        if (0xFFFF < $length) {
            $pack = \pack('NN', ($length >> 0b100000) & 0xFFFFFFFF, $length & 0xFFFFFFFF);
            $length = 0x007F;
        } elseif (0x007D < $length) {
            $pack = \pack('n*', $length);
            $length = 0x007E;
        }

        $payload = ($this->fin << 0b001) | $this->rsv[0];
        $payload = ($payload << 0b001) | $this->rsv[1];
        $payload = ($payload << 0b001) | $this->rsv[2];
        $payload = ($payload << 0b100) | $opCode;
        $payload = ($payload << 0b001) | $this->mask;
        $payload = ($payload << 0b111) | $length;

        $payload = \pack('n', $payload) . $pack;

        if (true === $this->mask) {
            $payload .= $this->maskKey;
            $data = $this->maskData($data);
        }

        return $payload . $data;
    }

    /**
     * Encode data.
     *
     * @return \ElephantIO\Parser\Websocket\Encoder
     */
    public function encode()
    {
        if (null === $this->payload) {
            $data = $this->data;
            $length = strlen($data);
            $size = min($this->maxPayload > 0 ? $this->maxPayload : $length, $length);

            $this->fin = 0b0;
            $opCode = $this->opCode;
            while (strlen($data) > 0) {
                $count = $size;
                // reduce count with framing protocol size
                if ($count === $this->maxPayload) {
                    if ($count > 125) {
                        $count -= (0xFFFF >= $count) ? 2 : 8;
                    }
                    if (true === $this->mask) {
                        $count -= strlen($this->maskKey);
                    }
                    $count -= 2;
                }

                // create payload fragment
                $s = substr($data, 0, $count);
                $data = substr($data, $count);
                if (0 === strlen($data)) {
                    $this->fin = 0b1;
                }

                $this->fragments[] = $this->doEncode($s, $opCode);
                $opCode = static::OPCODE_CONTINUE;
            }

            $this->payload = implode('', $this->fragments);
        }

        return $this;
    }

    public function __toString()
    {
        $this->encode();

        return $this->payload;
    }
}
