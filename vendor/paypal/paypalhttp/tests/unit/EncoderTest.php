<?php

namespace Test\Unit;

use PayPalHttp\Encoder;
use PayPalHttp\HttpRequest;
use PayPalHttp\Serializer;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HttpRequest does not have Content-Type header set
     */
    public function testEncode_throwsExceptionIfContentTypeNotPresent()
    {
        $encoder = new Encoder();
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->body = "some string";

        $encoder->serializeRequest($httpRequest);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to serialize request with Content-Type: non-existent/type. Supported encodings are
     */
    public function testEncode_throwsExceptionIfNoSerializerForGivenContentType()
    {
        $encoder = new Encoder();
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->headers['content-type'] = "non-existent/type";
        $httpRequest->body = "some string";

        $encoder->serializeRequest($httpRequest);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Body must be either string or array
     */
    public function testEncode_throwsExceptionForNonStringOrArrayBody()
    {
        $encoder = new Encoder();
        $httpRequest = new HttpRequest("/path", "post");

        $httpRequest->headers['content-type'] = "application/json";
        $httpRequest->body = new \stdClass();

        $encoder->serializeRequest($httpRequest);
    }

    public function testEncode_serializesWithCorrectSerializer()
    {
        $encoder = new Encoder();
        $httpRequest = new HttpRequest("/path", "post");
        $httpRequest->headers['content-type'] = "application/json";
        $httpRequest->body = [
            "key_one" => "value_one",
            "key_two" => [
                "one",
                "two"
            ]
        ];

        $result = $encoder->serializeRequest($httpRequest);

        $this->assertEquals('{"key_one":"value_one","key_two":["one","two"]}', $result);
    }

    public function testEncode_gzipsDataWhenHeaderPresent()
    {
        $encoder = new Encoder();
        $httpRequest = new HttpRequest("/path", "post");

        $httpRequest->headers["content-type"] = "application/json";
        $httpRequest->headers["content-encoding"] = "gzip";
        $httpRequest->body = [
            "key" => "val"
        ];

        $encoded = $encoder->serializeRequest($httpRequest);

        $this->assertEquals(gzencode(json_encode($httpRequest->body)), $encoded);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP response does not have Content-Type header set
     */
    public function testDecode_throwsWhenContentTypeNotPresent()
    {
        $encoder = new Encoder();
        $headers = [];

        $encoder->deserializeResponse('data', $headers);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to deserialize response with Content-Type: application/unstructured. Supported encodings are:
     */
    public function testDecode_throwsWhenNoSerializerAvailableForContentType()
    {
        $encoder = new Encoder();
        $headers = [
            "content-type" => "application/unstructured"
        ];

        $encoder->deserializeResponse('data', $headers);
    }

    public function testDecode_deserializesResponseWithCorrectSerializer()
    {
        $encoder = new Encoder();
        $responseBody = '{"key_one":"value_one","key_two":["one","two"]}';
        $headers = [
            "content-type" => "application/json"
        ];

        $result = $encoder->deserializeResponse($responseBody, $headers);

        $this->assertEquals("value_one", $result->key_one);
        $this->assertEquals(["one", "two"], $result->key_two);
    }

    public function testDecode_deserializesResponseWithContentEncoding()
    {
        $encoder = new Encoder();
        $responseBody = '{"key_one":"value_one","key_two":["one","two"]}';
        $headers = [
            "content-type" => "application/json; charset=utf-8"
        ];

        $result = $encoder->deserializeResponse($responseBody, $headers);

        $this->assertEquals("value_one", $result->key_one);
        $this->assertEquals(["one", "two"], $result->key_two);
    }

    public function testDecode_deserializesResponseWithContentEncodingCaseInsensitive()
    {
        $encoder = new Encoder();
        $responseBody = '{"key_one":"value_one","key_two":["one","two"]}';
        $headers = [
            "content-type" => "application/JSON; charset=utf-8"
        ];

        $result = $encoder->deserializeResponse($responseBody, $headers);

        $this->assertEquals("value_one", $result->key_one);
        $this->assertEquals(["one", "two"], $result->key_two);
    }

    public function testDecode_ungzipsDataWhenHeaderPresent()
    {
        $encoder = new Encoder();
        $responseBody = '{"key_one":"value_one"}';
        $headers = [
            "content-type" => "application/json; charset=utf-8",
            "content-encoding" => "gzip"
        ];

        $decoded = $encoder->deserializeResponse(gzencode($responseBody), $headers);
        $expected = new \stdClass();

        $expected->key_one = "value_one";

        $this->assertEquals($expected, $decoded);
    }
}
