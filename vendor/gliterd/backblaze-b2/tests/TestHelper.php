<?php

namespace BackblazeB2\Tests;

use BackblazeB2\Http\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

trait TestHelper
{
    protected function buildGuzzleFromResponses(array $responses, $history = null)
    {
        $mock = new MockHandler($responses);
        $handler = new HandlerStack($mock);

        if ($history) {
            $handler->push($history);
        }

        return new HttpClient(['handler' => $handler]);
    }

    protected function buildResponseFromStub($statusCode, array $headers, $responseFile)
    {
        $response = file_get_contents(dirname(__FILE__).'/responses/'.$responseFile);

        return new Response($statusCode, $headers, $response);
    }
}
