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

namespace Google\Auth\Tests;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\GCECache;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ADCGetTest extends TestCase
{
    private $originalHome;

    protected function setUp()
    {
        $this->originalHome = getenv('HOME');
    }

    protected function tearDown()
    {
        if ($this->originalHome != getenv('HOME')) {
            putenv('HOME=' . $this->originalHome);
        }
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes it from
    }

    /**
     * @expectedException DomainException
     */
    public function testIsFailsEnvSpecifiesNonExistentFile()
    {
        $keyFile = __DIR__ . '/fixtures' . '/does-not-exist-private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getCredentials('a scope');
    }

    public function testLoadsOKIfEnvSpecifiedIsValid()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        $this->assertNotNull(
            ApplicationDefaultCredentials::getCredentials('a scope')
        );
    }

    public function testLoadsDefaultFileIfPresentAndEnvVarIsNotSet()
    {
        putenv('HOME=' . __DIR__ . '/fixtures');
        $this->assertNotNull(
            ApplicationDefaultCredentials::getCredentials('a scope')
        );
    }

    /**
     * @expectedException DomainException
     */
    public function testFailsIfNotOnGceAndNoDefaultFileFound()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');
        // simulate not being GCE and retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);

        ApplicationDefaultCredentials::getCredentials('a scope', $httpHandler);
    }

    public function testSuccedsIfNoDefaultFilesButIsOnGCE()
    {
        putenv('HOME');

        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);

        // simulate the response from GCE.
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);

        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            ApplicationDefaultCredentials::getCredentials('a scope', $httpHandler)
        );
    }
}

class ADCDefaultScopeTest extends TestCase
{
    /** @runInSeparateProcess */
    public function testGceCredentials()
    {
        putenv('HOME');

        $jsonTokens = json_encode(['access_token' => 'abc']);

        $creds = ApplicationDefaultCredentials::getCredentials(
            null, // $scope
            $httpHandler = getHandler([
                buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
                buildResponse(200, [], Utils::streamFor($jsonTokens)),
            ]), // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a+default+scope' // $defaultScope
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            $creds
        );

        $uriProperty = (new ReflectionClass($creds))->getProperty('tokenUri');
        $uriProperty->setAccessible(true);

        // used default scope
        $tokenUri = $uriProperty->getValue($creds);
        $this->assertContains('a+default+scope', $tokenUri);

        $creds = ApplicationDefaultCredentials::getCredentials(
            'a+user+scope', // $scope
            getHandler([
                buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
                buildResponse(200, [], Utils::streamFor($jsonTokens)),
            ]), // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a+default+scope' // $defaultScope
        );

        // did not use default scope
        $tokenUri = $uriProperty->getValue($creds);
        $this->assertContains('a+user+scope', $tokenUri);
    }

    /** @runInSeparateProcess */
    public function testUserRefreshCredentials()
    {
        putenv('HOME=' . __DIR__ . '/fixtures2');

        $creds = ApplicationDefaultCredentials::getCredentials(
            null, // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a default scope' // $defaultScope
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\UserRefreshCredentials',
            $creds
        );

        $authProperty = (new ReflectionClass($creds))->getProperty('auth');
        $authProperty->setAccessible(true);

        // used default scope
        $auth = $authProperty->getValue($creds);
        $this->assertEquals('a default scope', $auth->getScope());

        $creds = ApplicationDefaultCredentials::getCredentials(
            'a user scope', // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a default scope' // $defaultScope
        );

        // did not use default scope
        $auth = $authProperty->getValue($creds);
        $this->assertEquals('a user scope', $auth->getScope());
    }

    /** @runInSeparateProcess */
    public function testServiceAccountCredentials()
    {
        putenv('HOME=' . __DIR__ . '/fixtures');

        $creds = ApplicationDefaultCredentials::getCredentials(
            null, // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a default scope' // $defaultScope
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\ServiceAccountCredentials',
            $creds
        );

        $authProperty = (new ReflectionClass($creds))->getProperty('auth');
        $authProperty->setAccessible(true);

        // did not use default scope
        $auth = $authProperty->getValue($creds);
        $this->assertEquals('', $auth->getScope());

        $creds = ApplicationDefaultCredentials::getCredentials(
            'a user scope', // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a default scope' // $defaultScope
        );

        // used user scope
        $auth = $authProperty->getValue($creds);
        $this->assertEquals('a user scope', $auth->getScope());
    }

    /** @runInSeparateProcess */
    public function testDefaultScopeArray()
    {
        putenv('HOME=' . __DIR__ . '/fixtures2');

        $creds = ApplicationDefaultCredentials::getCredentials(
            null, // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            ['onescope', 'twoscope'] // $defaultScope
        );

        $authProperty = (new ReflectionClass($creds))->getProperty('auth');
        $authProperty->setAccessible(true);

        // used default scope
        $auth = $authProperty->getValue($creds);
        $this->assertEquals('onescope twoscope', $auth->getScope());
    }
}

class ADCGetMiddlewareTest extends TestCase
{
    private $originalHome;

    protected function setUp()
    {
        $this->originalHome = getenv('HOME');
    }

    protected function tearDown()
    {
        if ($this->originalHome != getenv('HOME')) {
            putenv('HOME=' . $this->originalHome);
        }
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes it if assigned
    }

    /**
     * @expectedException DomainException
     */
    public function testIsFailsEnvSpecifiesNonExistentFile()
    {
        $keyFile = __DIR__ . '/fixtures' . '/does-not-exist-private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getMiddleware('a scope');
    }

    public function testLoadsOKIfEnvSpecifiedIsValid()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        $this->assertNotNull(ApplicationDefaultCredentials::getMiddleware('a scope'));
    }

    public function testLoadsDefaultFileIfPresentAndEnvVarIsNotSet()
    {
        putenv('HOME=' . __DIR__ . '/fixtures');
        $this->assertNotNull(ApplicationDefaultCredentials::getMiddleware('a scope'));
    }

    /**
     * @expectedException DomainException
     */
    public function testFailsIfNotOnGceAndNoDefaultFileFound()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        // simulate not being GCE and retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);

        ApplicationDefaultCredentials::getMiddleware('a scope', $httpHandler);
    }

    public function testWithCacheOptions()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $httpHandler = getHandler([
            buildResponse(200),
        ]);

        $cacheOptions = [];
        $cachePool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');

        $middleware = ApplicationDefaultCredentials::getMiddleware(
            'a scope',
            $httpHandler,
            $cacheOptions,
            $cachePool->reveal()
        );
    }

    public function testSuccedsIfNoDefaultFilesButIsOnGCE()
    {
        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);

        // simulate the response from GCE.
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);

        $this->assertNotNull(ApplicationDefaultCredentials::getMiddleware('a scope', $httpHandler));
    }

    /**
     * @expectedException DomainException
     */
    public function testOnGceCacheWithHit()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        $mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $mockCacheItem->isHit()
            ->willReturn(true);
        $mockCacheItem->get()
            ->shouldBeCalledTimes(1)
            ->willReturn(false);

        $mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $mockCache->getItem(GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(1)
            ->willReturn($mockCacheItem->reveal());

        ApplicationDefaultCredentials::getMiddleware(
            'a scope',
            null,
            null,
            $mockCache->reveal()
        );
    }

    public function testOnGceCacheWithoutHit()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        $gceIsCalled = false;
        $dummyHandler = function ($request) use (&$gceIsCalled) {
            $gceIsCalled = true;
            return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
        };
        $mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $mockCacheItem->isHit()
            ->willReturn(false);
        $mockCacheItem->set(true)
            ->shouldBeCalledTimes(1);
        $mockCacheItem->expiresAfter(1500)
            ->shouldBeCalledTimes(1);

        $mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $mockCache->getItem(GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(2)
            ->willReturn($mockCacheItem->reveal());
        $mockCache->save($mockCacheItem->reveal())
            ->shouldBeCalled();

        $creds = ApplicationDefaultCredentials::getMiddleware(
            'a scope',
            $dummyHandler,
            null,
            $mockCache->reveal()
        );

        $this->assertTrue($gceIsCalled);
    }

    public function testOnGceCacheWithOptions()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        $prefix = 'test_prefix_';
        $lifetime = '70707';

        $gceIsCalled = false;
        $dummyHandler = function ($request) use (&$gceIsCalled) {
            $gceIsCalled = true;
            return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
        };
        $mockCacheItem = $this->prophesize('Psr\Cache\CacheItemInterface');
        $mockCacheItem->isHit()
            ->willReturn(false);
        $mockCacheItem->set(true)
            ->shouldBeCalledTimes(1);
        $mockCacheItem->expiresAfter($lifetime)
            ->shouldBeCalledTimes(1);

        $mockCache = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $mockCache->getItem($prefix . GCECache::GCE_CACHE_KEY)
            ->shouldBeCalledTimes(2)
            ->willReturn($mockCacheItem->reveal());
        $mockCache->save($mockCacheItem->reveal())
            ->shouldBeCalled();

        $creds = ApplicationDefaultCredentials::getMiddleware(
            'a scope',
            $dummyHandler,
            ['gce_prefix' => $prefix, 'gce_lifetime' => $lifetime],
            $mockCache->reveal()
        );

        $this->assertTrue($gceIsCalled);
    }
}

class ADCGetCredentialsWithTargetAudienceTest extends TestCase
{
    private $originalHome;
    private $targetAudience = 'a target audience';

    protected function setUp()
    {
        $this->originalHome = getenv('HOME');
    }

    protected function tearDown()
    {
        if ($this->originalHome != getenv('HOME')) {
            putenv('HOME=' . $this->originalHome);
        }
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes environment variable
    }

    /**
     * @expectedException DomainException
     */
    public function testIsFailsEnvSpecifiesNonExistentFile()
    {
        $keyFile = __DIR__ . '/fixtures' . '/does-not-exist-private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getIdTokenCredentials($this->targetAudience);
    }

    public function testLoadsOKIfEnvSpecifiedIsValid()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getIdTokenCredentials($this->targetAudience);
    }

    public function testLoadsDefaultFileIfPresentAndEnvVarIsNotSet()
    {
        putenv('HOME=' . __DIR__ . '/fixtures');
        ApplicationDefaultCredentials::getIdTokenCredentials($this->targetAudience);
    }

    /**
     * @expectedException DomainException
     */
    public function testFailsIfNotOnGceAndNoDefaultFileFound()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        // simulate not being GCE and retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);

        ApplicationDefaultCredentials::getIdTokenCredentials(
            $this->targetAudience,
            $httpHandler
        );
    }

    public function testWithCacheOptions()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $httpHandler = getHandler([
            buildResponse(200),
        ]);

        $cacheOptions = [];
        $cachePool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');

        $credentials = ApplicationDefaultCredentials::getIdTokenCredentials(
            $this->targetAudience,
            $httpHandler,
            $cacheOptions,
            $cachePool->reveal()
        );

        $this->assertInstanceOf('Google\Auth\FetchAuthTokenCache', $credentials);
    }

    public function testSuccedsIfNoDefaultFilesButIsOnGCE()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');
        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);

        // simulate the response from GCE.
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);

        $credentials = ApplicationDefaultCredentials::getIdTokenCredentials(
            $this->targetAudience,
            $httpHandler
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            $credentials
        );
    }
}

class ADCGetCredentialsWithQuotaProjectTest extends TestCase
{
    private $originalHome;
    private $quotaProject = 'a-quota-project';

    protected function setUp()
    {
        $this->originalHome = getenv('HOME');
    }

    protected function tearDown()
    {
        if ($this->originalHome != getenv('HOME')) {
            putenv('HOME=' . $this->originalHome);
        }
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes environment variable
    }

    public function testWithServiceAccountCredentialsAndExplicitQuotaProject()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $credentials = ApplicationDefaultCredentials::getCredentials(
            null,
            null,
            null,
            null,
            $this->quotaProject
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\ServiceAccountCredentials',
            $credentials
        );

        $this->assertEquals(
            $this->quotaProject,
            $credentials->getQuotaProject()
        );
    }

    public function testGetCredentialsUtilizesQuotaProjectInKeyFile()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $credentials = ApplicationDefaultCredentials::getCredentials();

        $this->assertEquals(
            'test_quota_project',
            $credentials->getQuotaProject()
        );
    }

    public function testWithFetchAuthTokenCacheAndExplicitQuotaProject()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $httpHandler = getHandler([
            buildResponse(200),
        ]);

        $cacheOptions = [];
        $cachePool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');

        $credentials = ApplicationDefaultCredentials::getCredentials(
            null,
            $httpHandler,
            $cacheOptions,
            $cachePool->reveal(),
            $this->quotaProject
        );

        $this->assertInstanceOf('Google\Auth\FetchAuthTokenCache', $credentials);

        $this->assertEquals(
            $this->quotaProject,
            $credentials->getQuotaProject()
        );
    }

    public function testWithGCECredentials()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');
        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);

        // simulate the response from GCE.
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);

        $credentials = ApplicationDefaultCredentials::getCredentials(
            null,
            $httpHandler,
            null,
            null,
            $this->quotaProject
        );

        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            $credentials
        );

        $this->assertEquals(
            $this->quotaProject,
            $credentials->getQuotaProject()
        );
    }
}

class ADCGetCredentialsAppEngineTest extends BaseTest
{
    private $originalHome;
    private $originalServiceAccount;
    private $targetAudience = 'a target audience';

    protected function setUp()
    {
        // set home to be somewhere else
        $this->originalHome = getenv('HOME');
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        // remove service account path
        $this->originalServiceAccount = getenv(ServiceAccountCredentials::ENV_VAR);
        putenv(ServiceAccountCredentials::ENV_VAR);
    }

    protected function tearDown()
    {
        // removes it if assigned
        putenv('HOME=' . $this->originalHome);
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $this->originalServiceAccount);
        putenv('GAE_INSTANCE');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAppEngineStandard()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Google App Engine';
        $this->assertInstanceOf(
            'Google\Auth\Credentials\AppIdentityCredentials',
            ApplicationDefaultCredentials::getCredentials()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testAppEngineFlexible()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Google App Engine';
        putenv('GAE_INSTANCE=aef-default-20180313t154438');
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
        ]);
        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            ApplicationDefaultCredentials::getCredentials(null, $httpHandler)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testAppEngineFlexibleIdToken()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Google App Engine';
        putenv('GAE_INSTANCE=aef-default-20180313t154438');
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
        ]);
        $creds = ApplicationDefaultCredentials::getIdTokenCredentials(
            $this->targetAudience,
            $httpHandler
        );
        $this->assertInstanceOf(
            'Google\Auth\Credentials\GCECredentials',
            $creds
        );
    }
}

// @todo consider a way to DRY this and above class up
class ADCGetSubscriberTest extends BaseTest
{
    private $originalHome;

    protected function setUp()
    {
        $this->onlyGuzzle5();

        $this->originalHome = getenv('HOME');
    }

    protected function tearDown()
    {
        if ($this->originalHome != getenv('HOME')) {
            putenv('HOME=' . $this->originalHome);
        }
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes it if assigned
    }

    /**
     * @expectedException DomainException
     */
    public function testIsFailsEnvSpecifiesNonExistentFile()
    {
        $keyFile = __DIR__ . '/fixtures' . '/does-not-exist-private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getSubscriber('a scope');
    }

    public function testLoadsOKIfEnvSpecifiedIsValid()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        $this->assertNotNull(ApplicationDefaultCredentials::getSubscriber('a scope'));
    }

    public function testLoadsDefaultFileIfPresentAndEnvVarIsNotSet()
    {
        putenv('HOME=' . __DIR__ . '/fixtures');
        $this->assertNotNull(ApplicationDefaultCredentials::getSubscriber('a scope'));
    }

    /**
     * @expectedException DomainException
     */
    public function testFailsIfNotOnGceAndNoDefaultFileFound()
    {
        putenv('HOME=' . __DIR__ . '/not_exist_fixtures');

        // simulate not being GCE by return 500
        $httpHandler = getHandler([
            buildResponse(500),
        ]);

        ApplicationDefaultCredentials::getSubscriber('a scope', $httpHandler);
    }

    public function testWithCacheOptions()
    {
        $keyFile = __DIR__ . '/fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);

        $httpHandler = getHandler([
            buildResponse(200),
        ]);

        $cacheOptions = [];
        $cachePool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');

        $subscriber = ApplicationDefaultCredentials::getSubscriber(
            'a scope',
            $httpHandler,
            $cacheOptions,
            $cachePool->reveal()
        );
    }

    public function testSuccedsIfNoDefaultFilesButIsOnGCE()
    {
        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);

        // simulate the response from GCE.
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);

        $this->assertNotNull(ApplicationDefaultCredentials::getSubscriber('a scope', $httpHandler));
    }
}
