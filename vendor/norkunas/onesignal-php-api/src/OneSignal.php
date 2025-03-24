<?php

declare(strict_types=1);

namespace OneSignal;

use OneSignal\Exception\BadMethodCallException;
use OneSignal\Exception\InvalidArgumentException;
use OneSignal\Exception\JsonException;
use OneSignal\Exception\UnsuccessfulResponse;
use OneSignal\Resolver\ResolverFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

use const JSON_THROW_ON_ERROR;

use function gettype;
use function is_array;

/**
 * @method Apps          apps()
 * @method Devices       devices()
 * @method Notifications notifications()
 */
class OneSignal
{
    public const API_URL = 'https://onesignal.com/api/v1';

    private $config;
    private $httpClient;
    private $requestFactory;
    private $streamFactory;
    private $resolverFactory;

    public function __construct(Config $config, ClientInterface $httpClient, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->resolverFactory = new ResolverFactory($this->config);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * @return array<mixed>
     *
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): array
    {
        $response = $this->httpClient->sendRequest($request);

        $contentType = $response->getHeader('Content-Type')[0] ?? 'application/json';

        if (!preg_match('/\bjson\b/i', $contentType)) {
            throw new JsonException("Response content-type is '$contentType' while a JSON-compatible one was expected.");
        }

        $content = $response->getBody()->__toString();

        try {
            $content = json_decode($content, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, %s returned.', gettype($content)));
        }

        if (!isset($content['_status_code'])) {
            $content['_status_code'] = $response->getStatusCode();
        }

        return $content;
    }

    /**
     * @return array<mixed>
     *
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws UnsuccessfulResponse
     */
    public function makeRequest(RequestInterface $request): array
    {
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400) {
            throw new UnsuccessfulResponse($request, $response);
        }

        $content = $response->getBody()->__toString();

        try {
            $content = json_decode($content, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, %s returned.', gettype($content)));
        }

        return $content;
    }

    /**
     * @return object
     *
     * @throws InvalidArgumentException
     */
    public function api(string $name)
    {
        switch ($name) {
            case 'apps':
                $api = new Apps($this, $this->resolverFactory);

                break;
            case 'devices':
                $api = new Devices($this, $this->resolverFactory);

                break;
            case 'notifications':
                $api = new Notifications($this, $this->resolverFactory);

                break;
            case 'segments':
                $api = new Segments($this);

                break;
            default:
                throw new InvalidArgumentException("Undefined api instance called: '$name'.");
        }

        return $api;
    }

    /**
     * @param array<mixed> $args
     */
    public function __call(string $name, array $args): object
    {
        try {
            return $this->api($name);
        } catch (InvalidArgumentException $e) {
            throw new BadMethodCallException("Undefined method called: '$name'.");
        }
    }
}
