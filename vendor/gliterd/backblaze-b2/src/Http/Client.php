<?php

namespace BackblazeB2\Http;

use BackblazeB2\ErrorHandler;
use BackblazeB2\Exceptions\B2Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Client wrapper around Guzzle.
 */
class Client extends GuzzleClient
{
    /**
     * Sends a response to the B2 API, automatically handling decoding JSON and errors.
     *
     * @param string $method
     * @param null   $uri
     * @param array  $options
     * @param bool   $asJson
     *
     * @throws GuzzleException If the request fails.
     * @throws B2Exception     If the B2 server replies with an error.
     *
     * @return mixed|ResponseInterface|string
     */
    public function guzzleRequest($method, $uri = null, array $options = [], $asJson = true)
    {
        $response = parent::request($method, $uri, $options);

        if ($response->getStatusCode() !== 200) {
            ErrorHandler::handleErrorResponse($response);
        }

        if ($asJson) {
            return json_decode($response->getBody(), true);
        }

        return $response->getBody()->getContents();
    }
}
