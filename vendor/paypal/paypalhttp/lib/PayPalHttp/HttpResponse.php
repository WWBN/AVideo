<?php

namespace PayPalHttp;

/**
 * Class HttpResponse
 * @package PayPalHttp
 *
 * Object that holds your response details
 */
class HttpResponse
{
    /**
     * @var int
     */
    public $statusCode;

    /**
     * @var array | string | object
     */
    public $result;

    /**
     * @var array
     */
    public $headers;

    public function __construct($statusCode, $body, $headers)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->result = $body;
    }
}
