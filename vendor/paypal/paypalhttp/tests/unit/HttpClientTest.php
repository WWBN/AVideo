<?php

namespace Test\Unit;

use PayPalHttp\Curl;
use PayPalHttp\Environment;
use PayPalHttp\HttpClient;
use PayPalHttp\HttpException;
use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;
use PHPUnit\Framework\TestCase;
use WireMock\Client\WireMock;

class HttpClientTest extends TestCase
{
    private $wireMock;
    private $environment;

    public function setUp()
    {
        $this->wireMock = WireMock::create();
        $this->environment = new DevelopmentEnvironment("http://localhost:8080");

        $this->assertTrue($this->wireMock->isAlive());
    }

    public static function setUpBeforeClass()
    {
        exec('java -jar ./tests/wiremock-standalone.jar --port 8080 --https-port 8443 > /dev/null 2>&1 &');
    }

    public static function tearDownAfterClass()
    {
        exec('ps aux | grep wiremock | grep -v grep | awk \'{print $2}\' | xargs kill -9');
    }

    public function testAddInjector_addsInjectorToInjectorList()
    {
        $client = new HttpClient($this->environment);

        $inj = new BasicInjector();
        $client->addInjector($inj);

        $this->assertContains($inj, $client->injectors);
    }

    public function testAddsMultipleInjectors_addsMultipleInjectorsToInjectorList()
    {
        $client = new HttpClient($this->environment);

        $inj1 = new BasicInjector();
        $client->addInjector($inj1);

        $inj2 = new BasicInjector();
        $client->addInjector($inj2);

        $this->assertArraySubset([$inj1, $inj2], $client->injectors);
    }

    public function testExecute_usesInjectorsToModifyRequest()
    {
        $this->wireMock->stubFor(WireMock::get(WireMock::urlEqualTo("/some-other-path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);
        $injector = new BasicInjector();
        $client->addInjector($injector);

        $req = new HttpRequest("/path", "GET");

        $client->execute($req);
        $this->wireMock->verify(WireMock::getRequestedFor(WireMock::urlEqualTo("/some-other-path")));
    }

    public function testExecute_formsRequestProperly()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $req->headers["Content-Type"] = "text/plain";
        $req->body = "some data";

        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("Content-Type", WireMock::equalTo("text/plain"))
            ->withRequestBody(WireMock::equalTo("some data")));
    }

    public function testExecute_formsRequestProperlyCaseInsensitive()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $req->headers["Content-Type"] = "TEXT/plain";
        $req->body = "some data";

        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("Content-Type", WireMock::equalTo("text/plain"))
            ->withRequestBody(WireMock::equalTo("some data")));
    }

    public function testExecute_setsUserAgentIfNotSet()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("User-Agent", WireMock::equalTo($client->userAgent())));
    }

    public function testExecute_doesNotSetUserAgentIfAlreadySet()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $req->headers["User-Agent"] = "Example user-agent";
        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("User-Agent", WireMock::equalTo("Example user-agent")));
    }

    public function testExecute_setsHeadersInRequest()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $req->headers["Custom-Header"] = "Custom value";
        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("Custom-Header", WireMock::equalTo("Custom value")));
    }

    public function testExecute_parsesHeadersFromResponse()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withHeader("Some-key", "Some value")
            ->withHeader("Content-Type", "text/plain")
            ->withBody("some plain text")
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $response = $client->execute($req);

        $this->assertEquals("Some value", $response->headers["Some-key"]);
        $this->assertEquals("text/plain", $response->headers["Content-Type"]);
        $this->assertEquals("some plain text", $response->result);
    }

    public function testExecute_throwsForNon200LevelResponse()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withHeader("Debug-Id", "some debug id")
            ->withHeader("Content-Type", "text/plain")
            ->withBody("Response body")
            ->withStatus(400)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        try {
            $client->execute($req);
            $this->fail("expected execute to throw");
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->statusCode);
            $this->assertArraySubset(["Debug-Id" => "some debug id"], $e->headers);
            $this->assertArraySubset(["Content-Type" => "text/plain"], $e->headers);
            $this->assertEquals("Response body", $e->getMessage());
        }
    }

    public function testParseResponse_parsesResponseWith100ContinueCorrectly()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(100)));

        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withHeader("Content-Type", "text/plain")
            ->withBody("Successfully dumped some data.\nAnother line of data\nLast one.")
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        $client->execute($req);
        $res = $client->execute($req);

        $this->assertEquals("Successfully dumped some data.\nAnother line of data\nLast one.", $res->result);
    }

    public function testExecute_doesNotModifyOriginalRequest()
    {
        $this->wireMock->stubFor(WireMock::get(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);
        $req = new HttpRequest("/path", "GET");

        $client->execute($req);

        // HttpClient adds UserAgent header pre-flight
        $this->assertEquals(0, sizeof($req->headers));
    }

    public function testExecute_usesUpdatedHTTPHeadersFromSerializer()
    {
        $this->wireMock->stubFor(WireMock::post(WireMock::urlEqualTo("/path"))
            ->willReturn(WireMock::aResponse()
            ->withStatus(200)));

        $client = new HttpClient($this->environment);

        $req = new HttpRequest("/path", "POST");
        // The "; boundary=--..." will be added by the Multipart serializer when serializing
        // the body
        $req->headers["Content-Type"] = "multipart/form-data";
        $file = fopen(dirname(__DIR__) . '/unit/Serializer/sample.txt', 'rb');
        $body = [];
        $body["file1"] = $file;
        $body["key"] = "value";
        $req->body = $body;

        $client->execute($req);

        $this->wireMock->verify(WireMock::postRequestedFor(WireMock::urlEqualTo("/path"))
            ->withHeader("Content-Type", WireMock::containing("multipart/form-data; boundary=--"))
            ->withRequestBody(WireMock::containing("Hello World!")));
    }
}

class BasicInjector implements Injector
{
    public function inject($httpRequest)
    {
        $httpRequest->path = "/some-other-path";
    }
}

class DevelopmentEnvironment implements Environment
{
    /**
     * @var string
     */
    private $baseUrl;

    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function baseUrl()
    {
        return $this->baseUrl;
    }
}
