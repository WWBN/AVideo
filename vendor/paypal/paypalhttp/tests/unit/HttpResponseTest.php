<?php

namespace Test\Unit;

use PayPalHttp\HttpResponse;
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    public function testHttpResponse_construct()
    {
        $response = new HttpResponse(200, '{"myJSON"=> "isTheBestJSON"}', ["Content-Type" => "application/json"]);

        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('{"myJSON"=> "isTheBestJSON"}', $response->result);
        $this->assertEquals(["Content-Type" => "application/json"], $response->headers);
    }
}
