<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\Injector;

class FPTIInstrumentationInjector implements Injector
{
    public function inject($request)
    {
        $request->headers["sdk_name"] = "Checkout SDK";
        $request->headers["sdk_version"] = "1.0.2";
        $request->headers["sdk_tech_stack"] = "PHP " . PHP_VERSION;
        $request->headers["api_integration_type"] = "PAYPALSDK";
    }
}
