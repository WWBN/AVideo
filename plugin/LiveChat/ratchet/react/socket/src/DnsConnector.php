<?php

namespace React\Socket;

use React\Dns\Resolver\Resolver;
use React\Promise;
use React\Promise\CancellablePromiseInterface;

final class DnsConnector implements ConnectorInterface
{
    private $connector;
    private $resolver;

    public function __construct(ConnectorInterface $connector, Resolver $resolver)
    {
        $this->connector = $connector;
        $this->resolver = $resolver;
    }

    public function connect($uri)
    {
        if (strpos($uri, '://') === false) {
            $parts = parse_url('tcp://' . $uri);
            unset($parts['scheme']);
        } else {
            $parts = parse_url($uri);
        }

        if (!$parts || !isset($parts['host'])) {
            return Promise\reject(new \InvalidArgumentException('Given URI "' . $uri . '" is invalid'));
        }

        $host = trim($parts['host'], '[]');
        $connector = $this->connector;

        return $this
            ->resolveHostname($host)
            ->then(function ($ip) use ($connector, $host, $parts) {
                $uri = '';

                // prepend original scheme if known
                if (isset($parts['scheme'])) {
                    $uri .= $parts['scheme'] . '://';
                }

                if (strpos($ip, ':') !== false) {
                    // enclose IPv6 addresses in square brackets before appending port
                    $uri .= '[' . $ip . ']';
                } else {
                    $uri .= $ip;
                }

                // append original port if known
                if (isset($parts['port'])) {
                    $uri .= ':' . $parts['port'];
                }

                // append orignal path if known
                if (isset($parts['path'])) {
                    $uri .= $parts['path'];
                }

                // append original query if known
                if (isset($parts['query'])) {
                    $uri .= '?' . $parts['query'];
                }

                // append original hostname as query if resolved via DNS and if
                // destination URI does not contain "hostname" query param already
                $args = array();
                parse_str(isset($parts['query']) ? $parts['query'] : '', $args);
                if ($host !== $ip && !isset($args['hostname'])) {
                    $uri .= (isset($parts['query']) ? '&' : '?') . 'hostname=' . rawurlencode($host);
                }

                // append original fragment if known
                if (isset($parts['fragment'])) {
                    $uri .= '#' . $parts['fragment'];
                }

                return $connector->connect($uri);
            });
    }

    private function resolveHostname($host)
    {
        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return Promise\resolve($host);
        }

        $promise = $this->resolver->resolve($host);

        return new Promise\Promise(
            function ($resolve, $reject) use ($promise) {
                // resolve/reject with result of DNS lookup
                $promise->then($resolve, $reject);
            },
            function ($_, $reject) use ($promise) {
                // cancellation should reject connection attempt
                $reject(new \RuntimeException('Connection attempt cancelled during DNS lookup'));

                // (try to) cancel pending DNS lookup
                if ($promise instanceof CancellablePromiseInterface) {
                    $promise->cancel();
                }
            }
        );
    }
}
