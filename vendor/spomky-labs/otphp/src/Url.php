<?php

declare(strict_types=1);

namespace OTPHP;

use function array_key_exists;
use function is_string;
use OTPHP\Exception\InvalidProvisioningUriException;

/**
 * @readonly
 *
 * @internal
 */
final class Url
{
    /**
     * @param non-empty-string $scheme
     * @param non-empty-string $host
     * @param non-empty-string $path
     * @param non-empty-string $secret
     * @param array<non-empty-string, mixed> $query
     */
    public function __construct(
        private readonly string $scheme,
        private readonly string $host,
        private readonly string $path,
        private readonly string $secret,
        private readonly array $query
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return non-empty-string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return non-empty-string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return non-empty-string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param non-empty-string $uri
     */
    public static function fromString(string $uri): self
    {
        $parsed_url = parse_url($uri);
        $parsed_url !== false || throw new InvalidProvisioningUriException('Invalid URI.');
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            array_key_exists($key, $parsed_url) || throw new InvalidProvisioningUriException(
                'Not a valid OTP provisioning URI'
            );
        }
        $scheme = $parsed_url['scheme'] ?? null;
        $host = $parsed_url['host'] ?? null;
        $path = $parsed_url['path'] ?? null;
        $query = $parsed_url['query'] ?? null;
        $scheme === 'otpauth' || throw new InvalidProvisioningUriException('Not a valid OTP provisioning URI');
        (is_string($host) && $host !== '') || throw new InvalidProvisioningUriException('Invalid URI.');
        (is_string($path) && $path !== '') || throw new InvalidProvisioningUriException('Invalid URI.');
        is_string($query) || throw new InvalidProvisioningUriException('Invalid URI.');
        $parsedQuery = [];
        parse_str($query, $parsedQuery);
        array_key_exists('secret', $parsedQuery) || throw new InvalidProvisioningUriException(
            'Not a valid OTP provisioning URI'
        );
        $secret = $parsedQuery['secret'];
        (is_string($secret) && $secret !== '') || throw new InvalidProvisioningUriException('Invalid URI.');
        unset($parsedQuery['secret']);

        return new self($scheme, $host, $path, $secret, $parsedQuery);
    }
}
