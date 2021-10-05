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

use Google\Auth\HttpHandler\HttpClientCache;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Tests\BaseTest;

class HttpHandlerFactoryTest extends BaseTest
{
    public function testBuildsGuzzle5Handler()
    {
        $this->onlyGuzzle5();

        HttpClientCache::setHttpClient(null);
        $handler = HttpHandlerFactory::build();
        $this->assertInstanceOf('Google\Auth\HttpHandler\Guzzle5HttpHandler', $handler);
    }

    public function testBuildsGuzzle6Handler()
    {
        $this->onlyGuzzle6();

        HttpClientCache::setHttpClient(null);
        $handler = HttpHandlerFactory::build();
        $this->assertInstanceOf('Google\Auth\HttpHandler\Guzzle6HttpHandler', $handler);
    }

    public function testBuildsGuzzle7Handler()
    {
        $this->onlyGuzzle7();

        HttpClientCache::setHttpClient(null);
        $handler = HttpHandlerFactory::build();
        $this->assertInstanceOf('Google\Auth\HttpHandler\Guzzle7HttpHandler', $handler);
    }
}
