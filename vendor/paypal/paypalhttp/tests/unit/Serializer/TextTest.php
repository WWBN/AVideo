<?php

namespace Test\Unit;

use PayPalHttp\HttpRequest;
use PayPalHttp\Serializer\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testSerialize_returnsStringIfBodyString()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body = "some string";

        $textSerializer = new Text();
        $result = $textSerializer->encode($httpRequest);
        $this->assertEquals("some string", $result);
    }

    public function testSerialize_returnsStringIfBodyJSONString()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body = "{ \"key\": \"value\" }";

        $textSerializer = new Text();
        $result = $textSerializer->encode($httpRequest);
        $this->assertEquals("{ \"key\": \"value\" }", $result);
    }

    public function testSerialize_returnsJsonArrayStringIfArray()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body[] = "some string";
        $httpRequest->body[] = "another string";

        $textSerializer = new Text();
        $result = $textSerializer->encode($httpRequest);
        $this->assertEquals("[\"some string\",\"another string\"]", $result);
    }

    public function testSerialize_returnsJsonObjectStringIfArray()
    {
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body['key'] = [
            'another_key' => 'another value',
            'something' => 'else'
        ];

        $textSerializer = new Text();
        $result = $textSerializer->encode($httpRequest);
        $this->assertEquals("{\"key\":{\"another_key\":\"another value\",\"something\":\"else\"}}", $result);
    }

    public function testDeserialize_returnsStringIfClassString()
    {
        $data = "something \t really \n fishy.";
        $textSerializer = new Text();
        $this->assertEquals($data, $textSerializer->decode($data));
    }
}
