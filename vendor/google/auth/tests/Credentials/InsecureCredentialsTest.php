<?php
/*
 * Copyright 2018 Google Inc.
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

namespace Google\Auth\Tests\Credentials;

use Google\Auth\Credentials\InsecureCredentials;
use PHPUnit\Framework\TestCase;

/**
 * @group credentials
 * @group credentials-insecure
 */
class InsecureCredentialsTest extends TestCase
{
    public function testFetchAuthToken()
    {
        $insecure = new InsecureCredentials();
        $this->assertEquals(['access_token' => ''], $insecure->fetchAuthToken());
    }

    public function testGetCacheKey()
    {
        $insecure = new InsecureCredentials();
        $this->assertNull($insecure->getCacheKey());
    }

    public function testGetLastReceivedToken()
    {
        $insecure = new InsecureCredentials();
        $this->assertEquals(['access_token' => ''], $insecure->getLastReceivedToken());
    }
}
