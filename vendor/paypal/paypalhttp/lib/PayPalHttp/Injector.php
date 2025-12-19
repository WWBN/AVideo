<?php

namespace PayPalHttp;

/**
 * Interface Injector
 * @package PayPalHttp
 *
 * Interface that can be implemented to apply injectors to Http client.
 *
 * @see HttpClient
 */
interface Injector
{
    /**
     * @param HttpRequest $httpRequest
     */
    public function inject($httpRequest);
}
