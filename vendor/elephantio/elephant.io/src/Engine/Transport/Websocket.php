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

use DomainException;
use ElephantIO\Engine\Transport;
use ElephantIO\Parser\Websocket\Decoder;
use ElephantIO\Parser\Websocket\Encoder;
use RuntimeException;

/**
 * Websocket transport.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Websocket extends Transport
{
    /**
     * Network safe fread wrapper.
     *
     * @param integer $bytes
     * @param int $timeout
     * @return bool|string
     */
    protected function readBytes($bytes, $timeout = 0)
    {
        $stream = $this->sio->getStream();
        $data = '';
        $chunk = null;
        $start = microtime(true);
        while ($bytes > 0) {
            if ($timeout > 0 && microtime(true) - $start >= $timeout) {
                $this->timedout = true;
                break;
            }
            if (!$stream->readable()) {
                throw new RuntimeException('Stream disconnected');
            }
            if (false === ($chunk = $stream->read($bytes))) {
                break;
            }
            $bytes -= \strlen($chunk);
            $data .= $chunk;

            $this->sio->ping();
        }
        if (false === $chunk) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * Be careful, this method may hang your script, as we're not in a non
     * blocking mode.
     */
    protected function doRead($timeout = 0)
    {
        $stream = $this->sio->getStream();
        if (!$stream || !$stream->readable()) {
            return;
        }

        /*
         * The first byte contains the FIN bit, the reserved bits, and the
         * opcode... We're not interested in them. Yet.
         * the second byte contains the mask bit and the payload's length
         */
        $data = $this->readBytes(2, $timeout);
        $bytes = \unpack('C*', $data);

        if (empty($bytes[2])) {
            return;
        }

        $mask = ($bytes[2] & 0b10000000) >> 7;
        $length = $bytes[2] & 0b01111111;

        /*
         * Here is where it is getting tricky :
         *
         * - If the length <= 125, then we do not need to do anything ;
         * - if the length is 126, it means that it is coded over the next 2 bytes ;
         * - if the length is 127, it means that it is coded over the next 8 bytes.
         *
         * But, here's the trick : we cannot interpret a length over 127 if the
         * system does not support 64bits integers (such as Windows, or 32bits
         * processors architectures).
         */
        switch ($length) {
            case 0x7D: // 125
                break;
            case 0x7E: // 126
                $data .= $bytes = $this->readBytes(2);
                $bytes = \unpack('n', $bytes);

                if (empty($bytes[1])) {
                    throw new RuntimeException('Invalid extended packet len');
                }

                $length = $bytes[1];
                break;
            case 0x7F: // 127
                // are (at least) 64 bits not supported by the architecture ?
                if (8 > PHP_INT_SIZE) {
                    throw new DomainException('64 bits unsigned integer are not supported on this architecture');
                }

                /*
                 * As (un)pack does not support unpacking 64bits unsigned
                 * integer, we need to split the data
                 *
                 * {@link http://stackoverflow.com/questions/14405751/pack-and-unpack-64-bit-integer}
                 */
                $data .= $bytes = $this->readBytes(8);
                list($left, $right) = \array_values(\unpack('N2', $bytes));
                $length = $left << 32 | $right;
                break;
        }

        // incorporate the mask key if the mask bit is 1
        if (true === $mask) {
            $data .= $this->readBytes(4);
        }

        $data .= $this->readBytes($length);
        $this->setHeartbeat();

        // decode the payload
        return new Decoder($data);
    }

    /**
     * Write to the stream.
     *
     * @param string $data
     * @return int
     */
    public function doWrite($data)
    {
        $stream = $this->sio->getStream();
        if (!$stream) {
            throw new RuntimeException('Stream not available!');
        }

        $bytes = $stream->write($data);
        $this->setHeartbeat();

        // wait a little bit of time after this message was sent
        usleep((int) $this->sio->getOptions()->wait);

        return $bytes;
    }

    /**
     * Create payload.
     *
     * @param string $data
     * @param int $encoding
     * @return \ElephantIO\Parser\Websocket\Encoder
     */
    public function getPayload($data, $encoding = Encoder::OPCODE_TEXT)
    {
        $encoder = new Encoder($data, $encoding, true);
        $encoder->setMaxPayload($this->sio->getSession() ? $this->sio->getSession()->max_payload :
            $this->sio->getOptions()->max_payload);

        return $encoder;
    }

    /** {@inheritDoc} */
    public function send($data, $parameters = [])
    {
        if (!$data instanceof Encoder) {
            $data = $this->getPayload($data, isset($parameters['encoding']) ? $parameters['encoding'] : Encoder::OPCODE_TEXT);
        }
        if (count($fragments = $data->encode()->getFragments()) > 1) {
            throw new RuntimeException(sprintf(
                'Payload is exceed the maximum allowed length of %d!',
                $this->sio->getOptions()->max_payload
            ));
        }

        return $this->doWrite($fragments[0]);
    }

    /** {@inheritDoc} */
    public function recv($timeout = 0, $parameters = [])
    {
        $this->timedout = null;

        return $this->doRead($timeout);
    }
}
