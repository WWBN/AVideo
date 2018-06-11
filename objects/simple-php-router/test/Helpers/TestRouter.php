<?php

class TestRouter extends \Pecee\SimpleRouter\SimpleRouter
{

    public static function debugNoReset($testUrl, $testMethod = 'get')
    {
        static::request()->setUrl($testUrl);
        static::request()->setMethod($testMethod);

        static::start();
    }

    public static function debug($testUrl, $testMethod = 'get')
    {
        try {
            static::debugNoReset($testUrl, $testMethod);
        } catch(\Exception $e) {
            static::router()->reset();
            throw $e;
        }

        static::router()->reset();

    }

    public static function debugOutput($testUrl, $testMethod = 'get')
    {
        $response = null;

        // Route request
        ob_start();
        static::debug($testUrl, $testMethod);
        $response = ob_get_contents();
        ob_end_clean();

        // Return response
        return $response;
    }

}