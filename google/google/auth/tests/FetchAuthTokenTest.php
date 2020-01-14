<?php
/*
 * Copyright 2010 Google Inc.
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

namespace Google\Auth\tests;

use Google\Auth\Credentials\AppIdentityCredentials;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\ServiceAccountJwtAccessCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\OAuth2;

class FetchAuthTokenTest extends BaseTest
{
    /** @dataProvider provideAuthTokenFetcher */
    public function testGetLastReceivedToken(FetchAuthTokenInterface $fetcher)
    {
        $accessToken = $fetcher->getLastReceivedToken();

        $this->assertNotNull($accessToken);
        $this->assertArrayHasKey('access_token', $accessToken);
        $this->assertArrayHasKey('expires_at', $accessToken);

        $this->assertEquals('xyz', $accessToken['access_token']);
        $this->assertEquals(strtotime('2001'), $accessToken['expires_at']);
    }

    public function provideAuthTokenFetcher()
    {
        $scopes = ['https://www.googleapis.com/auth/drive.readonly'];
        $jsonPath = sprintf(
            '%s/fixtures/.config/%s',
            __DIR__,
            CredentialsLoader::WELL_KNOWN_PATH
        );
        $jsonPath2 = sprintf(
            '%s/fixtures2/.config/%s',
            __DIR__,
            CredentialsLoader::WELL_KNOWN_PATH
        );

        return [
            [$this->getAppIdentityCredentials()],
            [$this->getGCECredentials()],
            [$this->getServiceAccountCredentials($scopes, $jsonPath)],
            [$this->getServiceAccountJwtAccessCredentials($jsonPath)],
            [$this->getUserRefreshCredentials($scopes, $jsonPath2)],
            [$this->getOAuth2()],
        ];
    }

    private function getAppIdentityCredentials()
    {
        $class = new \ReflectionClass(
            'Google\Auth\Credentials\AppIdentityCredentials'
        );
        $property = $class->getProperty('lastReceivedToken');
        $property->setAccessible(true);

        $credentials = new AppIdentityCredentials();
        $property->setValue($credentials, [
            'access_token' => 'xyz',
            'expiration_time' => strtotime('2001'),
        ]);

        return $credentials;
    }

    private function getGCECredentials()
    {
        $class = new \ReflectionClass(
            'Google\Auth\Credentials\GCECredentials'
        );
        $property = $class->getProperty('lastReceivedToken');
        $property->setAccessible(true);

        $credentials = new GCECredentials();
        $property->setValue($credentials, [
            'access_token' => 'xyz',
            'expires_at' => strtotime('2001'),
        ]);

        return $credentials;
    }

    private function getServiceAccountCredentials($scopes, $jsonPath)
    {
        $class = new \ReflectionClass(
            'Google\Auth\Credentials\ServiceAccountCredentials'
        );
        $property = $class->getProperty('auth');
        $property->setAccessible(true);

        $credentials = new ServiceAccountCredentials($scopes, $jsonPath);
        $property->setValue($credentials, $this->getOAuth2Mock());

        return $credentials;
    }

    private function getServiceAccountJwtAccessCredentials($jsonPath)
    {
        $class = new \ReflectionClass(
            'Google\Auth\Credentials\ServiceAccountJwtAccessCredentials'
        );
        $property = $class->getProperty('auth');
        $property->setAccessible(true);

        $credentials = new ServiceAccountJwtAccessCredentials($jsonPath);
        $property->setValue($credentials, $this->getOAuth2Mock());

        return $credentials;
    }

    private function getUserRefreshCredentials($scopes, $jsonPath)
    {
        $class = new \ReflectionClass(
            'Google\Auth\Credentials\UserRefreshCredentials'
        );
        $property = $class->getProperty('auth');
        $property->setAccessible(true);

        $credentials = new UserRefreshCredentials($scopes, $jsonPath);
        $property->setValue($credentials, $this->getOAuth2Mock());

        return $credentials;
    }

    private function getOAuth2()
    {
        $oauth = new OAuth2([
            'access_token' => 'xyz',
            'expires_at' => strtotime('2001'),
        ]);

        return $oauth;
    }

    private function getOAuth2Mock()
    {
        $mock = $this->getMockBuilder('Google\Auth\OAuth2')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('getLastReceivedToken')
            ->will($this->returnValue([
                'access_token' => 'xyz',
                'expires_at' => strtotime('2001'),
            ]));

        return $mock;
    }
}
