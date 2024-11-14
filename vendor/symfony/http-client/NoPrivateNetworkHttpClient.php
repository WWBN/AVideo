<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Decorator that blocks requests to private networks by default.
 *
 * @author Hallison Boaventura <hallisonboaventura@gmail.com>
 */
final class NoPrivateNetworkHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    use HttpClientTrait;

    private const PRIVATE_SUBNETS = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '192.168.0.0/16',
        '172.16.0.0/12',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '240.0.0.0/4',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
        '::ffff:0:0/96',
        '::/128',
    ];

    private $client;
    private $subnets;

    /**
     * @param string|array|null $subnets String or array of subnets using CIDR notation that will be used by IpUtils.
     *                                   If null is passed, the standard private subnets will be used.
     */
    public function __construct(HttpClientInterface $client, $subnets = null)
    {
        if (!(\is_array($subnets) || \is_string($subnets) || null === $subnets)) {
            throw new \TypeError(sprintf('Argument 2 passed to "%s()" must be of the type array, string or null. "%s" given.', __METHOD__, get_debug_type($subnets)));
        }

        if (!class_exists(IpUtils::class)) {
            throw new \LogicException(sprintf('You cannot use "%s" if the HttpFoundation component is not installed. Try running "composer require symfony/http-foundation".', __CLASS__));
        }

        $this->client = $client;
        $this->subnets = $subnets;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $onProgress = $options['on_progress'] ?? null;
        if (null !== $onProgress && !\is_callable($onProgress)) {
            throw new InvalidArgumentException(sprintf('Option "on_progress" must be callable, "%s" given.', get_debug_type($onProgress)));
        }

        $subnets = $this->subnets;
        $lastUrl = '';
        $lastPrimaryIp = '';

        $options['on_progress'] = function (int $dlNow, int $dlSize, array $info, ?\Closure $resolve = null) use ($onProgress, $subnets, &$lastUrl, &$lastPrimaryIp): void {
            if ($info['url'] !== $lastUrl) {
                $host = trim(parse_url($info['url'], PHP_URL_HOST) ?: '', '[]');
                $resolve ??= static fn () => null;

                if (($ip = $host)
                    && !filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)
                    && !filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)
                    && !$ip = $resolve($host)
                ) {
                    if ($ip = @(dns_get_record($host, \DNS_A)[0]['ip'] ?? null)) {
                        $resolve($host, $ip);
                    } elseif ($ip = @(dns_get_record($host, \DNS_AAAA)[0]['ipv6'] ?? null)) {
                        $resolve($host, '['.$ip.']');
                    }
                }

                if ($ip && IpUtils::checkIp($ip, $subnets ?? self::PRIVATE_SUBNETS)) {
                    throw new TransportException(sprintf('Host "%s" is blocked for "%s".', $host, $info['url']));
                }

                $lastUrl = $info['url'];
            }

            if ($info['primary_ip'] !== $lastPrimaryIp) {
                if ($info['primary_ip'] && IpUtils::checkIp($info['primary_ip'], $subnets ?? self::PRIVATE_SUBNETS)) {
                    throw new TransportException(sprintf('IP "%s" is blocked for "%s".', $info['primary_ip'], $info['url']));
                }

                $lastPrimaryIp = $info['primary_ip'];
            }

            null !== $onProgress && $onProgress($dlNow, $dlSize, $info);
        };

        return $this->client->request($method, $url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withOptions(array $options): self
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);

        return $clone;
    }

    public function reset()
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }
}
