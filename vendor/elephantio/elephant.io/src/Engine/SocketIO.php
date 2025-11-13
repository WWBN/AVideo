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

use ArrayObject;
use ElephantIO\Engine\Argument;
use ElephantIO\Engine\Packet;
use ElephantIO\Engine\Transport\Polling;
use ElephantIO\Engine\Transport\Websocket;
use ElephantIO\Exception\SocketException;
use ElephantIO\Exception\UnsupportedActionException;
use ElephantIO\Stream\Stream;
use ElephantIO\Util;
use InvalidArgumentException;
use RuntimeException;
use Psr\Log\LoggerAwareTrait;

/**
 * Elephant.io socket engine base class.
 *
 * @author Toha <tohenk@yahoo.com>
 */
abstract class SocketIO implements EngineInterface, SocketInterface
{
    use LoggerAwareTrait;

    public const EIO_V1 = 1;    // socket.io 0x
    public const EIO_V2 = 2;    // socket.io 1x
    public const EIO_V3 = 3;    // socket.io 2x
    public const EIO_V4 = 4;    // socket.io 3x and newer

    public const TRANSPORT_POLLING = 'polling';
    public const TRANSPORT_WEBSOCKET = 'websocket';

    /** @var string Engine name */
    protected $name = 'SocketIO';

    /** @var string Socket url */
    protected $url;

    /** @var ?string Normalized namespace without path prefix */
    protected $namespace = '';

    /** @var ?\ElephantIO\Engine\Session Session information */
    protected $session;

    /** @var string[] Cookies received during handshake */
    protected $cookies = [];

    /** @var \ElephantIO\Engine\Option Array of options for the engine */
    protected $options;

    /** @var ?\ElephantIO\Stream\StreamInterface Resource to the connected stream */
    protected $stream;

    /** @var ?string Current socket transport */
    protected $transport = null;

    /** @var mixed[] Array of php stream context options */
    protected $context = [];

    /** @var mixed[] Array of default options for the engine */
    protected $defaults;

    /** @var array<string, array<int, string>> Array of packet maps */
    protected $packetMaps = [];

    /** @var int[] Array of min and max of protocol */
    protected $proto = [0, 0];

    /** @var string Protocol delimiter */
    protected $protoDelimiter = '';

    /** @var ?\ElephantIO\Engine\Transport */
    private $_transport = null;

    /** @var ?int Acknowledgement id */
    private static $ack = null;

    /**
     * Constructor.
     *
     * @param string $url Socket URL
     * @param array<string, mixed> $options Engine options
     */
    public function __construct($url, array $options = [])
    {
        $this->url = $url;

        if (isset($options['headers'])) {
            Util::handleDeprecatedHeaderOptions($options['headers']);
        }

        if (isset($options['context']['headers'])) {
            Util::handleDeprecatedHeaderOptions($options['context']['headers']);
        }

        if (isset($options['context'])) {
            $this->context = $options['context'];
            unset($options['context']);
        }

        $this->defaults = [
            'ua' => true,
            'cors' => true,
            'wait' => 10,
            'timeout' => ini_get('default_socket_timeout'),
            'reuse_connection' => true,
            'transport' => static::TRANSPORT_POLLING,
            'transports' => null,
            'binary_as_resource' => true,
        ];

        $this->initialize($options);
        $this->options = Option::create(array_replace($this->defaults, $options));
        if (isset($this->packetMaps['proto']) && count($protos = array_keys($this->packetMaps['proto']))) {
            $this->proto = [min($protos), max($protos)];
        }
    }

    /**
     * Do initilization.
     *
     * @param array<string, mixed> $options Engine options
     * @return void
     */
    protected function initialize(&$options)
    {
    }

    /**
     * Set default options.
     *
     * @param array<string, mixed> $defaults Default options
     * @return void
     */
    protected function setDefaults($defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Get options.
     *
     * @return \ElephantIO\Engine\Option
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get socket URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get underlying socket stream.
     *
     * @param bool $create True to create the stream
     * @return \ElephantIO\Stream\StreamInterface|null
     */
    public function getStream($create = false)
    {
        if ($create) {
            $this->createStream();
        }

        return $this->stream;
    }

    /**
     * Get session.
     *
     * @return \ElephantIO\Engine\Session|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get cookies.
     *
     * @return string[]
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Get stream context.
     *
     * @return mixed[]
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get current socket transport.
     *
     * @return string|null
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set current socket transport.
     *
     * @param string $transport Socket transport name
     * @return \ElephantIO\Engine\SocketIO
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->getTransports())) {
            throw new InvalidArgumentException(sprintf('Unsupported transport "%s"!', $transport));
        }
        $this->transport = $transport;

        return $this;
    }

    /**
     * Get current transport.
     *
     * @return \ElephantIO\Engine\Transport|null
     */
    protected function _transport()
    {
        if (null === $this->_transport || ($this->stream && $this->stream->wasUpgraded())) {
            $this->createStream();

            if ($this->stream) {
                switch ($this->stream->upgraded()) {
                    case true:
                        $this->_transport = new Websocket($this);
                        break;
                    default:
                        $this->_transport = new Polling($this);
                        break;
                }
                if ($this->logger) {
                    $this->_transport->setLogger($this->logger);
                }
            }
        }

        return $this->_transport;
    }

    public function connect()
    {
        if (!$this->connected()) {
            $this->setTransport($this->options->transport);
            $this->doHandshake();
            $this->doAfterHandshake();
            if ($this->isUpgradable()) {
                $this->doUpgrade();
            } else {
                $this->doSkipUpgrade();
            }
            $this->doConnected();
        }

        return $this;
    }

    public function connected()
    {
        return $this->stream ? $this->stream->readable() : false;
    }

    public function disconnect()
    {
        if ($this->connected()) {
            if ($this->session) {
                $this->doClose();
            }
            $this->reset();
        }

        return $this;
    }

    public function of($namespace)
    {
        $normalized = Util::normalizeNamespace($namespace);
        if ($this->namespace !== $normalized) {
            $this->namespace = $normalized;

            return $this->doChangeNamespace();
        }

        return null;
    }

    public function emit($event, $args, $ack = null)
    {
        if (!$args instanceof Argument) {
            $args = Argument::from($args);
        }
        list($proto, $data, $raws) = $this->createEvent($event, $args, $ack);

        $len = $this->send($proto, $data);
        if (is_array($raws)) {
            if ($transport = $this->_transport()) {
                foreach ($raws as $raw) {
                    $len += $transport->send($raw);
                }
            } else {
                throw new RuntimeException('Unable to create transport!');
            }
        }

        // wait for an ack
        if ($ack) {
            return $this->waitForPacket(function($packet) {
                return $this->matchAck($packet);
            });
        }

        return $len;
    }

    public function ack($packet, $args)
    {
        if (!$args instanceof Argument) {
            $args = Argument::from($args);
        }
        list($proto, $data) = $this->createAck($packet, $args);

        return $this->send($proto, $data);
    }

    public function wait($event, $timeout = 0)
    {
        return $this->waitForPacket(function($packet) use ($event) {
            return $this->matchEvent($packet, $event);
        }, $timeout);
    }

    public function drain($timeout = 0)
    {
        if (($transport = $this->_transport()) && null !== ($data = $transport->recv($timeout))) {
            if ($this->logger) {
                $this->logger->debug(sprintf('Got data: %s', Util::truncate((string) $data)));
            }
            if ($data instanceof ArrayObject) {
                $data = (array) $data;
            } else {
                $data = (string) $data;
            }

            return $this->processData($data);
        }

        return null;
    }

    /**
     * Send protocol and its data to server.
     *
     * @param int $proto Protocol type
     * @param string  $data Optional data to be sent
     * @return int|null Number of bytes written
     */
    public function send($proto, $data = null)
    {
        if ($this->isProtocol($proto) && $transport = $this->_transport()) {
            $formatted = $this->formatProtocol($proto, $data);
            if ($this->logger) {
                $this->logger->debug(sprintf('Send data: %s', Util::truncate($formatted)));
            }

            return $transport->send($formatted);
        }

        return null;
    }

    /**
     * Send ping to server.
     *
     * @return bool
     */
    public function ping()
    {
        if ($this->session && $this->session->needsHeartbeat()) {
            if ($this->logger) {
                $this->logger->debug('Sending ping to server');
            }
            $this->doPing();
            if ($this->session) {
                $this->session->resetHeartbeat();
            }

            return true;
        }

        return false;
    }

    /**
     * Wait for a matched packet to arrive.
     *
     * @param callable $matcher
     * @param float $timeout
     * @return \ElephantIO\Engine\Packet|null
     */
    protected function waitForPacket($matcher, $timeout = 0)
    {
        while (true) {
            if ($packet = $this->drain($timeout)) {
                if ($match = $matcher($packet)) {
                    return $match;
                }
                if ($this->logger) {
                    foreach ($packet->flatten() as $p) {
                        $this->logger->info(sprintf('Ignoring packet: %s', Util::truncate((string) $p)));
                    }
                }
            }
            if (($transport = $this->_transport()) && $transport->timedout()) {
                break;
            }
        }

        return null;
    }

    /**
     * Process one or more received data. If data contains more than one,
     * the next packet will be passed as first packet next attribute.
     *
     * @param string|array<int|string, mixed> $data Data to process
     * @return \ElephantIO\Engine\Packet|null
     */
    protected function processData($data)
    {
        $result = null;
        $packets = (array) $data;
        while (count($packets)) {
            if ($packet = $this->decodePacket(array_shift($packets))) {
                $this->postPacket($packet, $packets);
                if (!$this->consumePacket($packet)) {
                    if (null === $result) {
                        $result = $packet;
                    } else {
                        $result->add($packet);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Decode a packet.
     *
     * @param string $data
     * @return \ElephantIO\Engine\Packet|null
     */
    abstract protected function decodePacket($data);

    /**
     * Post processing a packet.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @param string[] $more Remaining packet data to be processed
     * @return void
     */
    protected function postPacket($packet, &$more)
    {
    }

    /**
     * Consume a packet.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @return bool
     */
    protected function consumePacket($packet)
    {
        return false;
    }

    /**
     * Is namespace match?
     *
     * @param ?string $namespace
     * @return bool
     */
    protected function matchNamespace($namespace)
    {
        if ((string) $namespace === $this->namespace || Util::normalizeNamespace($namespace) === $this->namespace) {
            return true;
        }

        return false;
    }

    /**
     * Create an event to sent to server.
     *
     * @param string $event
     * @param \ElephantIO\Engine\Argument $args
     * @param bool $ack
     * @return array<int, mixed> An indexed array which first element would be protocol id and second element is the data
     */
    protected function createEvent($event, $args, $ack = null)
    {
        throw new UnsupportedActionException($this, 'createEvent');
    }

    /**
     * Find matched event from packet.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @param ?string $event
     * @return \ElephantIO\Engine\Packet|null
     */
    protected function matchEvent($packet, $event)
    {
        throw new UnsupportedActionException($this, 'matchEvent');
    }

    /**
     * Create an acknowledgement.
     *
     * @param \ElephantIO\Engine\Packet $packet Packet to acknowledge
     * @param \ElephantIO\Engine\Argument $data Acknowledgement data
     * @return array<int, mixed> An indexed array which first element would be protocol id and second element is the data
     */
    protected function createAck($packet, $data)
    {
        throw new UnsupportedActionException($this, 'createAck');
    }

    /**
     * Find matched ack from packet.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @return \ElephantIO\Engine\Packet|null
     */
    protected function matchAck($packet)
    {
        throw new UnsupportedActionException($this, 'matchAck');
    }

    /**
     * Get or generate acknowledgement id.
     *
     * @param bool $generate
     * @return int|null
     */
    protected function getAckId($generate = null)
    {
        if ($generate) {
            if (null === self::$ack) {
                self::$ack = 0;
            } else {
                self::$ack++;
            }
        }

        return self::$ack;
    }

    /**
     * Create a packet.
     *
     * @param int $proto
     * @return \ElephantIO\Engine\Packet
     */
    protected function createPacket($proto)
    {
        $packet = new Packet();
        $packet->proto = $proto;
        if (count($this->packetMaps)) {
            $packet->setMaps($this->packetMaps);
        }

        return $packet;
    }

    /**
     * Store successful connection handshake as session.
     *
     * @param array<string, mixed> $handshake
     * @param string[] $cookies
     * @return void
     */
    protected function storeSession($handshake, $cookies = [])
    {
        $this->session = Session::from($handshake);
        $this->cookies = $cookies;
    }

    /**
     * Create socket stream.
     *
     * @throws \ElephantIO\Exception\SocketException
     * @return void
     */
    protected function createStream()
    {
        if ($this->stream && !$this->stream->available()) {
            $this->stream = null;
        }
        if (!$this->stream) {
            $this->stream = Stream::create(
                $this->url,
                $this->context,
                array_merge($this->options->toArray(), ['logger' => $this->logger])
            );
            if ($errors = $this->stream->getErrors()) {
                throw new SocketException($errors[0], $errors[1]);
            }
        }
    }

    /**
     * Update or set connection timeout.
     *
     * @param float $timeout
     * @return \ElephantIO\Engine\SocketIO
     */
    protected function setTimeout($timeout)
    {
        $this->options->timeout = $timeout;
        // stream already established?
        if ($this->stream) {
            $this->stream->setTimeout($timeout);
        }

        return $this;
    }

    /**
     * Check if socket transport is enabled.
     *
     * @param string $transport
     * @return bool
     */
    protected function isTransportEnabled($transport)
    {
        $transports = $this->options->transports;

        return
            null === $transports ||
            $transport === $transports ||
            (is_array($transports) && in_array($transport, $transports)) ? true : false;
    }

    /**
     * Get supported socket transports.
     *
     * @return string[]
     */
    protected function getTransports()
    {
        return [static::TRANSPORT_POLLING, static::TRANSPORT_WEBSOCKET];
    }

    /**
     * Build query string parameters.
     *
     * @param ?string $transport
     * @return array<string, mixed>
     */
    public function buildQueryParameters($transport)
    {
        return [];
    }

    /**
     * Build query from parameters.
     *
     * @param array<string, mixed> $query
     * @return string|null
     */
    public function buildQuery($query)
    {
        if ($this->stream) {
            $path = null;
            if (isset($query['path'])) {
                $path = $query['path'];
                unset($query['path']);
            }

            return $this->stream->getUrl()->getUri($path, $query);
        }

        return null;
    }

    /**
     * Do reset.
     *
     * @return void
     */
    protected function reset()
    {
        if ($this->stream) {
            $this->stream->close();
            $this->stream = null;
            $this->session = null;
            $this->cookies = [];
            $this->_transport = null;
        }
    }

    /**
     * Check if protocol is valid.
     *
     * @param int $proto
     * @return bool True if it is a valid protocol
     */
    protected function isProtocol($proto)
    {
        return $proto >= $this->proto[0] && $proto <= $this->proto[1] ? true : false;
    }

    /**
     * Format data for protocol.
     *
     * @param int $proto
     * @param string $data
     * @return string
     */
    protected function formatProtocol($proto, $data = null)
    {
        return implode($this->protoDelimiter, $this->buildProtocol($proto, $data));
    }

    /**
     * Build protocol data.
     *
     * @param int $proto
     * @param string $data
     * @return array<int, int|string|null>
     */
    protected function buildProtocol($proto, $data = null)
    {
        return [$proto, $data];
    }

    /**
     * Is transport can be upgraded to websocket?
     *
     * @return bool
     */
    protected function isUpgradable()
    {
        return $this->session &&
            in_array(static::TRANSPORT_WEBSOCKET, $this->session->upgrades) &&
            $this->isTransportEnabled(static::TRANSPORT_WEBSOCKET) ? true : false;
    }

    /**
     * Do on handshake.
     *
     * @return void
     */
    protected function doHandshake()
    {
    }

    /**
     * Do on after handshake.
     *
     * @return void
     */
    protected function doAfterHandshake()
    {
    }

    /**
     * Do on transport upgrade.
     *
     * @return void
     */
    protected function doUpgrade()
    {
    }

    /**
     * Do on skip upgrade.
     *
     * @return void
     */
    protected function doSkipUpgrade()
    {
    }

    /**
     * Do on change namespace.
     *
     * @return \ElephantIO\Engine\Packet|null
     */
    protected function doChangeNamespace()
    {
        return null;
    }

    /**
     * Do on connected.
     *
     * @return void
     */
    protected function doConnected()
    {
    }

    /**
     * Do on ping.
     *
     * @return void
     */
    protected function doPing()
    {
    }

    /**
     * Do on close.
     *
     * @return void
     */
    protected function doClose()
    {
    }
}
