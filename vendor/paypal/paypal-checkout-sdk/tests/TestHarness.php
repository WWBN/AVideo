<?php

namespace Test;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class TestHarness
{
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }
    public static function environment()
    {
        $clientId = getenv("CLIENT_ID") ?: "<<PAYPAL-CLIENT-ID>>";
        $clientSecret = getenv("CLIENT_SECRET") ?: "<<PAYPAL-CLIENT-SECRET>>";
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}
