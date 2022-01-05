<?php

namespace PaypalPayoutsSDK\Core;

use PayPalHttp\Injector;

class FPTIInstrumentationInjector implements Injector
{
    public function inject($request)
    {
        $request->headers["sdk_name"] = "Payouts SDK";
        $request->headers["sdk_version"] = "1.0.0";
        $request->headers["sdk_tech_stack"] = "PHP " . PHP_VERSION;
        $request->headers["api_integration_type"] = "PAYPALSDK";
    }
}
