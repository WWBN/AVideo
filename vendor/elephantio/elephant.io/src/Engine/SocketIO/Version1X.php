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

namespace ElephantIO\Engine\SocketIO;

use ElephantIO\Engine\SocketIO;
use ElephantIO\Engine\Transport\Polling;
use ElephantIO\Exception\ServerConnectionFailureException;
use ElephantIO\Exception\UnsuccessfulOperationException;
use ElephantIO\Parser\Polling\Decoder as PollingDecoder;
use ElephantIO\Parser\Websocket\Encoder as WebsocketEncoder;
use ElephantIO\SequenceReader;
use ElephantIO\Util;
use ElephantIO\Yeast;
use InvalidArgumentException;
use RuntimeException;

/**
 * Implements the dialog with socket.io server 1.x.
 *
 * Based on the work of Mathieu Lallemand (@lalmat)
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 * @link https://tools.ietf.org/html/rfc6455#section-5.2 Websocket's RFC
 */
class Version1X extends SocketIO
{
    public const PROTO_OPEN = 0;
    public const PROTO_CLOSE = 1;
    public const PROTO_PING = 2;
    public const PROTO_PONG = 3;
    public const PROTO_MESSAGE = 4;
    public const PROTO_UPGRADE = 5;
    public const PROTO_NOOP = 6;

    public const PACKET_CONNECT = 0;
    public const PACKET_DISCONNECT = 1;
    public const PACKET_EVENT = 2;
    public const PACKET_ACK = 3;
    public const PACKET_ERROR = 4;
    public const PACKET_BINARY_EVENT = 5;
    public const PACKET_BINARY_ACK = 6;

    protected function initialize(&$options)
    {
        $this->name = 'SocketIO Version 1.X';
        $this->packetMaps = [
            'proto' => [
                static::PROTO_OPEN => 'open',
                static::PROTO_CLOSE => 'close',
                static::PROTO_PING => 'ping',
                static::PROTO_PONG => 'pong',
                static::PROTO_MESSAGE => 'message',
                static::PROTO_UPGRADE => 'upgrade',
                static::PROTO_NOOP => 'noop',
            ],
            'type' => [
                static::PACKET_CONNECT => 'connect',
                static::PACKET_DISCONNECT => 'disconnect',
                static::PACKET_EVENT => 'event',
                static::PACKET_ACK => 'ack',
                static::PACKET_ERROR => 'error',
                static::PACKET_BINARY_EVENT => 'binary-event',
                static::PACKET_BINARY_ACK => 'binary-ack',
            ]
        ];
        $this->setDefaults(['version' => static::EIO_V2, 'max_payload' => 10e7]);
    }

    /** {@inheritDoc} */
    protected function matchEvent($packet, $event)
    {
        foreach ($packet->peek(static::PROTO_MESSAGE) as $found) {
            if ($this->matchNamespace($found->nsp) && ($found->event === $event || null === $event)) {
                return $found;
            }
        }
    }

    /** {@inheritDoc} */
    protected function createEvent($event, $args, $ack = null)
    {
        $args = $args->getArguments();
        $attachments = [];
        $this->getAttachments($args, $attachments);
        $type = count($attachments) ? static::PACKET_BINARY_EVENT : static::PACKET_EVENT;
        $data = ($ack ? $this->getAckId(true) : '') . json_encode(array_merge([$event], $args));
        $data = Util::concatNamespace($this->namespace, $data);
        if ($type === static::PACKET_BINARY_EVENT) {
            $data = sprintf('%d-%s', count($attachments), $data);
            $this->logger->debug(sprintf('Binary event arguments %s', Util::toStr($args)));
        }

        $raws = null;
        if (count($attachments)) {
            switch ($this->transport) {
                case static::TRANSPORT_POLLING:
                    if ($this->options->version >= static::EIO_V4) {
                        foreach ($attachments as $attachment) {
                            $data .= PollingDecoder::EIO_V4_SEPARATOR;
                            $data .= 'b' . base64_encode($attachment);
                        }
                    } else {
                        $raws = [];
                        foreach ($attachments as $attachment) {
                            $raws[] = 'b' . static::PROTO_MESSAGE . base64_encode($attachment);
                        }
                    }
                    break;
                case static::TRANSPORT_WEBSOCKET:
                    $raws = [];
                    /** @var \ElephantIO\Engine\Transport\Websocket $transport */
                    $transport = $this->_transport();
                    foreach ($attachments as $attachment) {
                        if ($this->options->version <= static::EIO_V3) {
                            $attachment = pack('C', static::PROTO_MESSAGE) . $attachment;
                        }
                        $raws[] = $transport->getPayload($attachment, WebsocketEncoder::OPCODE_BINARY);
                    }
                    break;
            }
        }

        return [static::PROTO_MESSAGE, $type . $data, $raws];
    }

    /** {@inheritDoc} */
    protected function matchAck($packet)
    {
        foreach ($packet->peek(static::PROTO_MESSAGE) as $found) {
            if (in_array($found->type, [static::PACKET_ACK, static::PACKET_BINARY_ACK]) &&
                $this->matchNamespace($found->nsp) && $found->ack === $this->getAckId()) {
                return $found;
            }
        }
    }

    /** {@inheritDoc} */
    protected function createAck($packet, $data)
    {
        $type = $packet->count ? static::PACKET_BINARY_ACK : static::PACKET_ACK;
        $data = Util::concatNamespace($this->namespace, $packet->ack . json_encode($data->getArguments()));

        return [static::PROTO_MESSAGE, $type . $data];
    }

    /** {@inheritDoc} */
    protected function decodePacket($data)
    {
        // @see https://socket.io/docs/v4/engine-io-protocol/
        $seq = new SequenceReader($data);
        $proto = (int) $seq->read();
        if ($this->isProtocol($proto)) {
            $packet = $this->createPacket($proto);
            $packet->data = null;
            switch ($packet->proto) {
                case static::PROTO_MESSAGE:
                    $packet->type = (int) $seq->read();
                    if ($packet->type === static::PACKET_BINARY_EVENT) {
                        $packet->count = (int) $seq->readUntil('-');
                    }
                    // nsp is delimited by ","
                    $openings = ['[', '{'];
                    $packet->nsp = $seq->readWithin(',', $openings);
                    // check for ack
                    if (in_array($packet->type, [static::PACKET_EVENT, static::PACKET_BINARY_EVENT, static::PACKET_ACK, static::PACKET_BINARY_ACK])) {
                        if (!in_array($seq->readData(), $openings) && '' !== ($ack = (string) $seq->readUntil(implode($openings), $openings))) {
                            $packet->ack = (int) $ack;
                        }
                    }
                    if (null !== ($data = json_decode($seq->getData(), true))) {
                        switch ($packet->type) {
                            case static::PACKET_EVENT:
                            case static::PACKET_BINARY_EVENT:
                                $packet->event = array_shift($data);
                                $packet->setArgs($data);
                                break;
                            case static::PACKET_ACK:
                            case static::PACKET_BINARY_ACK:
                                $packet->setArgs($data);
                                break;
                            default:
                                $packet->data = $data;
                                break;
                        }
                    }
                    break;
                default:
                    if (!$seq->isEof()) {
                        $packet->data = $seq->getData();
                        if ($packet->data && $packet->proto === static::PROTO_OPEN) {
                            $packet->data = json_decode($packet->data, true);
                        }
                    }
                    break;
            }
            $this->logger->info(sprintf('Got packet: %s', Util::truncate((string) $packet)));

            return $packet;
        }
    }

    /** {@inheritDoc} */
    protected function postPacket($packet, &$more)
    {
        if ($packet->proto === static::PROTO_MESSAGE &&
            $packet->type === static::PACKET_BINARY_EVENT) {
            $packet->type = static::PACKET_EVENT;
            for ($i = 0; $i < $packet->count; $i++) {
                $bindata = null;
                switch ($this->transport) {
                    case static::TRANSPORT_POLLING:
                        if ($this->options->version >= static::EIO_V4) {
                            if ($bindata = array_shift($more)) {
                                $prefix = substr($bindata, 0, 1);
                                if ($prefix !== 'b') {
                                    throw new RuntimeException(sprintf('Unable to decode binary data with prefix "%s"!', $prefix));
                                }
                                $bindata = base64_decode(substr($bindata, 1));
                            }
                        } else {
                            if ($bindata = array_shift($more)) {
                                if (ord($bindata[0]) !== static::PROTO_MESSAGE) {
                                    throw new RuntimeException(sprintf('Invalid binary data at position %d!', $i));
                                }
                                $bindata = substr($bindata, 1);
                            }
                        }
                        break;
                    case static::TRANSPORT_WEBSOCKET:
                        $bindata = (string) $this->_transport()->recv();
                        break;
                }
                if (null === $bindata) {
                    throw new RuntimeException(sprintf('Binary data unavailable for index %d!', $i));
                }
                $packet->data = $this->replaceAttachment($packet->data, $i, $bindata);
            }
        }
    }

    /** {@inheritDoc} */
    protected function consumePacket($packet)
    {
        switch ($packet->proto) {
            case static::PROTO_CLOSE:
                $this->logger->debug('Connection closed by server');
                $this->reset();
                break;
            case static::PROTO_PING:
                $this->logger->debug('Got PING, sending PONG');
                $this->send(static::PROTO_PONG);
                break;
            case static::PROTO_PONG:
                $this->logger->debug('Got PONG');
                break;
            case static::PROTO_NOOP:
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Get attachment from packet data. A packet data considered as attachment
     * if it's a resource and it has content.
     *
     * @param array $array
     * @param array $result
     */
    protected function getAttachments(&$array, &$result)
    {
        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_resource($value)) {
                    fseek($value, 0);
                    if ($content = stream_get_contents($value)) {
                        $idx = count($result);
                        $result[] = $content;
                        $value = ['_placeholder' => true, 'num' => $idx];
                    } else {
                        $value = null;
                    }
                }
                if (is_array($value)) {
                    $this->getAttachments($value, $result);
                }
            }
        }
    }

    /**
     * Replace binary attachment.
     *
     * @param array $array
     * @param int $index
     * @param string $data
     * @return array
     */
    protected function replaceAttachment($array, $index, $data)
    {
        if (is_array($array)) {
            foreach ($array as $key => &$value) {
                if (is_array($value)) {
                    if (isset($value['_placeholder']) && $value['_placeholder'] && $value['num'] === $index) {
                        if ($this->options->binary_as_resource) {
                            $value = Util::toResource($data);
                        } else {
                            $value = $data;
                        }
                        $this->logger->debug(sprintf('Replacing binary attachment for %d (%s)', $index, $key));
                    } else {
                        $value = $this->replaceAttachment($value, $index, $data);
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Get authentication payload handshake.
     *
     * @return string
     */
    protected function getAuthPayload()
    {
        $auth = '';
        if ($this->options->version >= static::EIO_V4 && is_array($this->options->auth) && count($this->options->auth)) {
            if (false === ($auth = json_encode($this->options->auth))) {
                throw new InvalidArgumentException(sprintf('Can\'t parse auth option JSON: %s!', json_last_error_msg()));
            }
        }

        return $auth;
    }

    /**
     * Get confirmed namespace result. Namespace is confirmed if the returned
     * value is true, otherwise failed. If the return value is a string, it's
     * indicated an error message.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @return bool|string
     */
    protected function getConfirmedNamespace($packet)
    {
        if ($packet) {
            foreach ($packet->peek(static::PROTO_MESSAGE) as $found) {
                if ($found->type === static::PACKET_CONNECT) {
                    return true;
                }
                if ($found->type === static::PACKET_ERROR) {
                    return isset($found->data['message']) ? $found->data['message'] : false;
                }
            }
        }
    }

    public function buildQueryParameters($transport)
    {
        $parameters = [
            'EIO' => $this->options->version,
            'transport' => $transport ?? $this->transport,
            't' => Yeast::yeast(),
        ];
        if ($this->session) {
            $parameters['sid'] = $this->session->id;
        }

        return $parameters;
    }

    protected function doHandshake()
    {
        if (null !== $this->session) {
            return;
        }

        $this->logger->info('Starting handshake');

        // set timeout to default
        $this->setTimeout($this->defaults['timeout']);

        /** @var \ElephantIO\Engine\Transport\Polling $transport */
        $transport = $this->_transport();
        if (null === ($data = $transport->recv($this->options->timeout, ['upgrade' => $this->transport === static::TRANSPORT_WEBSOCKET]))) {
            throw new ServerConnectionFailureException('unable to perform handshake');
        }

        if ($this->transport === static::TRANSPORT_WEBSOCKET) {
            $this->stream->upgrade();
            $packet = $this->drain($this->options->timeout);
        } else {
            $packet = $this->processData($data);
        }

        $handshake = null;
        if ($packet && ($packet = $packet->peekOne(static::PROTO_OPEN))) {
            $handshake = $packet->data;
        }
        if (null === $handshake) {
            throw new RuntimeException('Handshake is successful but without data!');
        }
        array_walk($handshake, function(&$value, $key) {
            if (in_array($key, ['pingInterval', 'pingTimeout'])) {
                $value /= 1000;
            }
        });
        $this->storeSession($handshake, $transport->getCookies());

        $this->logger->info(sprintf('Handshake finished with %s', (string) $this->session));
    }

    protected function doAfterHandshake()
    {
        // connect to namespace for protocol version 4 and later
        if ($this->options->version >= static::EIO_V4) {
            $this->logger->info('Starting namespace connect');

            // set timeout based on handshake response
            $this->setTimeout($this->session->getTimeout());
            $this->doChangeNamespace();

            $this->logger->info('Namespace connect completed');
        }
    }

    protected function doUpgrade()
    {
        $this->logger->info('Starting websocket upgrade');

        // set timeout based on handshake response
        $this->setTimeout($this->session->getTimeout());

        if (null !== $this->_transport()->recv($this->options->timeout, ['transport' => static::TRANSPORT_WEBSOCKET, 'upgrade' => true])) {
            $this->setTransport(static::TRANSPORT_WEBSOCKET);
            $this->stream->upgrade();

            $this->send(static::PROTO_UPGRADE);

            // ensure got packet connect on socket.io 1.x
            if ($this->options->version === static::EIO_V2 && $packet = $this->drain($this->options->timeout)) {
                $confirm = null;
                foreach ($packet->peek(static::PROTO_MESSAGE) as $found) {
                    if ($found->type === static::PACKET_CONNECT) {
                        $confirm = $found;
                        break;
                    }
                }
                if ($confirm) {
                    $this->logger->debug('Upgrade successfully confirmed');
                } else {
                    $this->logger->debug('Upgrade not confirmed');
                }
            }

            $this->logger->info('Websocket upgrade completed');
        } else {
            $this->logger->info('Upgrade failed, skipping websocket');
        }
    }

    protected function doChangeNamespace()
    {
        if (!$this->session) {
            throw new RuntimeException('To switch namespace, a session must has been established!');
        }

        $this->send(static::PROTO_MESSAGE, static::PACKET_CONNECT . Util::concatNamespace($this->namespace, $this->getAuthPayload()));

        $packet = $this->drain($this->options->timeout);
        if (true === ($result = $this->getConfirmedNamespace($packet))) {
            return $packet;
        }
        if (null === $packet) {
            /** @var \ElephantIO\Engine\Transport\Polling $transport */
            $transport = $this->_transport();
            if ($transport instanceof Polling) {
                if (is_array($body = $transport->getBody()) && isset($body['message'])) {
                    $result = $body['message'];
                }
            }
        }
        if (is_string($result)) {
            throw new UnsuccessfulOperationException(sprintf('Unable to switch namespace: %s!', $result));
        } else {
            throw new UnsuccessfulOperationException('Unable to switch namespace!');
        }
    }

    protected function doPing()
    {
        if ($this->options->version <= static::EIO_V3) {
            $this->send(static::PROTO_PING);
        }
    }

    protected function doClose()
    {
        $this->send(static::PROTO_CLOSE);
    }
}
