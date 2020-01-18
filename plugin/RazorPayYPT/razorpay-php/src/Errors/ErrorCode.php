<?php

namespace Razorpay\Api\Errors;

class ErrorCode
{
    const BAD_REQUEST_ERROR                 = 'BAD_REQUEST_ERROR';
    const SERVER_ERROR                      = 'SERVER_ERROR';
    const GATEWAY_ERROR                     = 'GATEWAY_ERROR';

    public static function exists($code)
    {
        $code = strtoupper($code);

        return defined(get_class() . '::' . $code);
    }
}