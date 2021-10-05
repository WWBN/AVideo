<?php

namespace Test\Unit;

use PayPalHttp\HttpRequest;
use PayPalHttp\Serializable;
use PayPalHttp\Serializer\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testSerialize_returnsStringIfBodyString()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body = "some string";

        $serializer = new Json();
        $result = $serializer->encode($httpRequest);
        $this->assertEquals("some string", $result);
    }

    public function testSerialize_returnsStringIfBodyJSONString()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body = "{ \"key\": \"value\" }";

        $serializer = new Json();
        $result = $serializer->encode($httpRequest);
        $this->assertEquals("{ \"key\": \"value\" }", $result);
    }

    public function testSerialize_returnsJsonArrayIfArray()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body[] = "some string";
        $httpRequest->body[] = "another string";

        $serializer = new Json();
        $result = $serializer->encode($httpRequest);
        $this->assertEquals("[\"some string\",\"another string\"]", $result);
    }

    public function testSerialize_returnsJsonObjectStringIfArray()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body['key'] = [
            'another_key' => 'another value',
            'something' => 'else'
        ];

        $serializer = new Json();
        $result = $serializer->encode($httpRequest);
        $this->assertEquals("{\"key\":{\"another_key\":\"another value\",\"something\":\"else\"}}", $result);
    }
}

