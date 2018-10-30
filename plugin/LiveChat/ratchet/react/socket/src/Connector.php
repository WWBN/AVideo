<?php

namespace React\Socket;

use React\EventLoop\LoopInterface;
use React\Dns\Resolver\Resolver;
use React\Dns\Resolver\Factory;
use React\Promise;
use RuntimeException;

/**
 * The `Connector` class is the main class in this package that implements the
 * `ConnectorInterface` and allows you to create streaming connections.
 *
 * You can use this connector to create any kind of streaming connections, such
 * as plaintext TCP/IP, secure TLS or local Unix connection streams.
 *
 * Under the hood, the `Connector` is implemented as a *higher-level facade*
 * or the lower-level connectors implemented in this package. This means it
 * also shares all of their features and implementation details.
 * If you want to typehint in your higher-level protocol implementation, you SHOULD
 * use the generic [`ConnectorInterface`](#connectorinterface) instead.
 *
 * @see ConnectorInterface for the base interface
 */
final class Connector implements ConnectorInterface
{
    private $connectors = array();

    public function __construct(LoopInterface $loop, array $options = array())
    {
        // apply default options if not explicitly given
        $options += array(
            'tcp' => true,
            'tls' => true,
            'unix' => true,

            'dns' => true,
            'timeout' => true,
        );

        if ($options['timeout'] === true) {
            $options['timeout'] = (float)ini_get("default_socket_timeout");
        }

        if ($options['tcp'] instanceof ConnectorInterface) {
            $tcp = $options['tcp'];
        } else {
            $tcp = new TcpConnector(
                $loop,
                is_array($options['tcp']) ? $options['tcp'] : array()
            );
        }

        if ($options['dns'] !== false) {
            if ($options['dns'] instanceof Resolver) {
                $resolver = $options['dns'];
            } else {
                $factory = new Factory();
                $resolver = $factory->create(
                    $options['dns'] === true ? '8.8.8.8' : $options['dns'],
                    $loop
                );
            }

            $tcp = new DnsConnector($tcp, $resolver);
        }

        if ($options['tcp'] !== false) {
            $options['tcp'] = $tcp;

            if ($options['timeout'] !== false) {
                $options['tcp'] = new TimeoutConnector(
                    $options['tcp'],
                    $options['timeout'],
                    $loop
                );
            }

            $this->connectors['tcp'] = $options['tcp'];
        }

        if ($options['tls'] !== false) {
            if (!$options['tls'] instanceof ConnectorInterface) {
                $options['tls'] = new SecureConnector(
                    $tcp,
                    $loop,
                    is_array($options['tls']) ? $options['tls'] : array()
                );
            }

            if ($options['timeout'] !== false) {
                $options['tls'] = new TimeoutConnector(
                    $options['tls'],
                    $options['timeout'],
                    $loop
                );
            }

            $this->connectors['tls'] = $options['tls'];
        }

        if ($options['unix'] !== false) {
            if (!$options['unix'] instanceof ConnectorInterface) {
                $options['unix'] = new UnixConnector($loop);
            }
            $this->connectors['unix'] = $options['unix'];
        }
    }

    public function connect($uri)
    {
        $scheme = 'tcp';
        if (strpos($uri, '://') !== false) {
            $scheme = (string)substr($uri, 0, strpos($uri, '://'));
        }

        if (!isset($this->connectors[$scheme])) {
            return Promise\reject(new RuntimeException(
                'No connector available for URI scheme "' . $scheme . '"'
            ));
        }

        return $this->connectors[$scheme]->connect($uri);
    }
}

