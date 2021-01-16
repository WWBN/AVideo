<?php

namespace Razorpay\Api\Errors;

use Exception;

class Error extends Exception
{
    protected $httpStatusCode;

    public function __construct($message, $code, $httpStatusCode)
    {
        $this->code = $code;

        $this->message = $message;

        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}