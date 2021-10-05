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

namespace Google\Auth\Tests\Credentials;

use google\appengine\api\app_identity\AppIdentityService;
// included from tests\mocks\AppIdentityService.php
use Google\Auth\Credentials\AppIdentityCredentials;
use PHPUnit\Framework\TestCase;

/**
 * If a test includes mocks/AppIdentityService.php, be sure to use the
 * `@runInSeparateProcess` annotation.
 *
 * @group credentials
 * @group credentials-appidentity
 */
class AppIdentityCredentialsTest extends TestCase
{
    public function testOnAppEngineIsFalseByDefault()
    {
        $this->assertFalse(AppIdentityCredentials::onAppEngine());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnAppEngineIsTrueWhenServerSoftwareIsGoogleAppEngine()
    {
        $this->imitateInAppEngine();
        $this->assertTrue(AppIdentityCredentials::onAppEngine());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnAppEngineIsTrueWhenAppEngineRuntimeIsPhp()
    {
        $this->imitateInAppEngine();
        $this->assertTrue(AppIdentityCredentials::onAppEngine());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnAppEngineIsTrueInDevelopmentServer()
    {
        $_SERVER['APPENGINE_RUNTIME'] = 'php';
        $this->assertTrue(AppIdentityCredentials::onAppEngine());
    }

    public function testGetCacheKeyShouldBeEmpty()
    {
        $g = new AppIdentityCredentials();
        $this->assertEmpty($g->getCacheKey());
    }

    public function testFetchAuthTokenShouldBeEmptyIfNotOnAppEngine()
    {
        $g = new AppIdentityCredentials();
        $this->assertEquals(array(), $g->fetchAuthToken());
    }

    /* @expectedException */
    public function testThrowsExceptionIfClassDoesntExist()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Google App Engine';
        $g = new AppIdentityCredentials();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFetchAuthTokenReturnsExpectedToken()
    {
        $this->imitateInAppEngine();

        $wantedToken = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];

        AppIdentityService::$accessToken = $wantedToken;

        $g = new AppIdentityCredentials();
        $this->assertEquals($wantedToken, $g->fetchAuthToken());
    }

    /**
     * @runInSeparateProcess
     */
    public function testScopeIsAlwaysArray()
    {
        $this->imitateInAppEngine();

        $scope1 = ['scopeA', 'scopeB'];
        $scope2 = 'scopeA scopeB';
        $scope3 = 'scopeA';

        $g = new AppIdentityCredentials($scope1);
        $g->fetchAuthToken();
        $this->assertEquals($scope1, AppIdentityService::$scope);

        $g = new AppIdentityCredentials($scope2);
        $g->fetchAuthToken();
        $this->assertEquals(explode(' ', $scope2), AppIdentityService::$scope);

        $g = new AppIdentityCredentials($scope3);
        $g->fetchAuthToken();
        $this->assertEquals([$scope3], AppIdentityService::$scope);
    }

    /**
     * @dataProvider appEngineRequired
     */
    public function testMethodsFailWhenNotInAppEngine($method, $args = [], $expected = null)
    {
        if ($expected === null) {
            if (method_exists($this, 'expectException')) {
                $this->expectException('\Exception');
            } else {
                $this->setExpectedException('\Exception');
            }
        }

        $creds = new AppIdentityCredentials();
        $res = call_user_func_array([$creds, $method], $args);

        if ($expected) {
            $this->assertEquals($expected, $res);
        }
    }

    public function appEngineRequired()
    {
        return [
            ['fetchAuthToken', [], []],
            ['signBlob', ['foo']],
            ['getClientName']
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testSignBlob()
    {
        $this->imitateInAppEngine();

        $creds = new AppIdentityCredentials();
        $string = 'test';
        $res = $creds->signBlob($string);

        $this->assertEquals(base64_encode('Signed: ' . $string), $res);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetClientName()
    {
        $this->imitateInAppEngine();

        $creds = new AppIdentityCredentials();

        $expected = 'foobar';
        AppIdentityService::$serviceAccountName = $expected;

        $this->assertEquals($expected, $creds->getClientName());

        AppIdentityService::$serviceAccountName = 'notreturned';
        $this->assertEquals($expected, $creds->getClientName());
    }

    public function testGetLastReceivedTokenNullByDefault()
    {
        $creds = new AppIdentityCredentials();
        $this->assertNull($creds->getLastReceivedToken());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetLastReceviedTokenCaches()
    {
        $this->imitateInAppEngine();

        $creds = new AppIdentityCredentials();

        $wantedToken = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'expiration_time' => time() + 57,
            'token_type' => 'Bearer',
        ];

        AppIdentityService::$accessToken = $wantedToken;

        $creds->fetchAuthToken();

        $this->assertEquals([
            'access_token' => $wantedToken['access_token'],
            'expires_at' => $wantedToken['expiration_time']
        ], $creds->getLastReceivedToken());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetProjectId()
    {
        $this->imitateInAppEngine();

        $projectId = 'foobar';
        AppIdentityService::$applicationId = $projectId;
        $this->assertEquals($projectId, (new AppIdentityCredentials())->getProjectId());
    }

    public function testGetProjectOutsideAppEngine()
    {
        $this->assertNull((new AppIdentityCredentials())->getProjectId());
    }

    private function imitateInAppEngine()
    {
        // include the mock AppIdentityService class
        require_once __DIR__ . '/../mocks/AppIdentityService.php';
        $_SERVER['SERVER_SOFTWARE'] = 'Google App Engine';
        // $_SERVER['APPENGINE_RUNTIME'] = 'php';
    }
}
