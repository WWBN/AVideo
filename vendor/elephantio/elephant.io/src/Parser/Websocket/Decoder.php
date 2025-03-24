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

use Countable;

/**
 * Decode the payload from a received frame.
 *
 * Based on the work of Byeoung Wook (@kbu1564) in #49
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Decoder extends Payload implements Countable
{
    private $payload;
    private $data;
    private $length;

    /** @param string $payload Payload to decode */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function decode()
    {
        if (null !== $this->data) {
            return;
        }

        $length = \count($this);

        // if ($payload !== null) and ($payload packet error)?
        // invalid websocket packet data or not (text, binary opCode)
        if (!$length) {
            return;
        }

        $payload = \array_map('ord', \str_split($this->payload));

        $this->fin = ($payload[0] >> 0b111);

        $this->rsv = [($payload[0] >> 0b110) & 0b1,  // rsv1
            ($payload[0] >> 0b101) & 0b1,  // rsv2
            ($payload[0] >> 0b100) & 0b1]; // rsv3

        $this->opCode = $payload[0] & 0xF;
        $this->mask = (bool) ($payload[1] >> 0b111);

        $payloadOffset = 2;

        if ($length > 125) {
            $payloadOffset += (0xFFFF >= $length) ? 2 : 8;
        }

        $payload = \implode('', \array_map('chr', $payload));

        if (true === $this->mask) {
            $this->maskKey = \substr($payload, $payloadOffset, 4);
            $payloadOffset += 4;
        }

        $data = \substr($payload, $payloadOffset, $length);

        if (true === $this->mask) {
            $data = $this->maskData($data);
        }

        $this->data = $data;
    }

    public function count(): int
    {
        if (null === $this->payload) {
            return 0;
        }

        if (null !== $this->length) {
            return $this->length;
        }

        $length = \ord($this->payload[1]) & 0x7F;

        if ($length === 126 || $length === 127) {
            $length = \unpack('H*', \substr($this->payload, 2, ($length === 126 ? 2 : 8)));
            $length = \hexdec($length[1]);
        }

        return $this->length = $length;
    }

    public function __toString()
    {
        $this->decode();

        return $this->data ?: '';
    }
}
