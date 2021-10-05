<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Tests\HttpHandler;

use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @group http-handler
 */
class Guzzle6HttpHandlerTest extends BaseTest
{
    protected $client;
    protected $handler;

    public function setUp()
    {
        $this->onlyGuzzle6();

        $this->client = $this->prophesize('GuzzleHttp\ClientInterface');
        $this->handler = new Guzzle6HttpHandler($this->client->reveal());
    }

    public function testSuccessfullySendsRequest()
    {
        $request = new Request('GET', 'https://domain.tld');
        $options = ['key' => 'value'];
        $response = new Response(200);

        $this->client->send($request, $options)->willReturn($response);

        $handler = $this->handler;

        $this->assertSame($response, $handler($request, $options));
    }

    public function testSuccessfullySendsRequestAsync()
    {
        $request = new Request('GET', 'https://domain.tld');
        $options = ['key' => 'value'];
        $response = new Response(200);
        $promise = new FulfilledPromise($response);

        $this->client->sendAsync($request, $options)->willReturn($promise);

        $handler = $this->handler;

        $this->assertSame($response, $handler->async($request, $options)->wait());
    }
}
