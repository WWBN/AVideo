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

use Google\Auth\Credentials\GCECredentials;
use Google\Auth\HttpHandler\HttpClientCache;
use Google\Auth\Tests\BaseTest;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Utils;
use Prophecy\Argument;

/**
 * @group credentials
 * @group credentials-gce
 */
class GCECredentialsTest extends BaseTest
{
    public function testOnGceMetadataFlavorHeader()
    {
        $hasHeader = false;
        $dummyHandler = function ($request) use (&$hasHeader) {
            $hasHeader = $request->getHeaderLine(GCECredentials::FLAVOR_HEADER) === 'Google';

            return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
        };

        $onGce = GCECredentials::onGce($dummyHandler);
        $this->assertTrue($hasHeader);
        $this->assertTrue($onGce);
    }

    public function testOnGCEIsFalseOnClientErrorStatus()
    {
        // simulate retry attempts by returning multiple 400s
        $httpHandler = getHandler([
            buildResponse(400),
            buildResponse(400),
            buildResponse(400)
        ]);
        $this->assertFalse(GCECredentials::onGCE($httpHandler));
    }

    public function testOnGCEIsFalseOnServerErrorStatus()
    {
        // simulate retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);
        $this->assertFalse(GCECredentials::onGCE($httpHandler));
    }

    public function testOnGCEIsFalseOnOkStatusWithoutExpectedHeader()
    {
        $httpHandler = getHandler([
            buildResponse(200),
        ]);
        $this->assertFalse(GCECredentials::onGCE($httpHandler));
    }

    public function testOnGCEIsOkIfGoogleIsTheFlavor()
    {
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
        ]);
        $this->assertTrue(GCECredentials::onGCE($httpHandler));
    }

    public function testOnAppEngineFlexIsFalseByDefault()
    {
        $this->assertFalse(GCECredentials::onAppEngineFlexible());
    }

    public function testOnAppEngineFlexIsTrueWhenGaeInstanceHasAefPrefix()
    {
        putenv('GAE_INSTANCE=aef-default-20180313t154438');
        $this->assertTrue(GCECredentials::onAppEngineFlexible());
        putenv('GAE_INSTANCE');
    }

    public function testGetCacheKeyShouldNotBeEmpty()
    {
        $g = new GCECredentials();
        $this->assertNotEmpty($g->getCacheKey());
    }

    public function testFetchAuthTokenShouldBeEmptyIfNotOnGCE()
    {
        // simulate retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);
        $g = new GCECredentials();
        $this->assertEquals(array(), $g->fetchAuthToken($httpHandler));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid JSON response
     */
    public function testFetchAuthTokenShouldFailIfResponseIsNotJson()
    {
        $notJson = '{"foo": , this is cannot be passed as json" "bar"}';
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], $notJson),
        ]);
        $g = new GCECredentials();
        $g->fetchAuthToken($httpHandler);
    }

    public function testFetchAuthTokenShouldReturnTokenInfo()
    {
        $wantedTokens = [
            'access_token' => '1/abdef1234567890',
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $jsonTokens = json_encode($wantedTokens);
        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($jsonTokens)),
        ]);
        $g = new GCECredentials();
        $receivedToken = $g->fetchAuthToken($httpHandler);
        $this->assertEquals(
            $wantedTokens['access_token'],
            $receivedToken['access_token']
        );
        $this->assertEquals(time() + 57, $receivedToken['expires_at']);
        $this->assertEquals(time() + 57, $g->getLastReceivedToken()['expires_at']);
    }

    public function testFetchAuthTokenShouldBeIdTokenWhenTargetAudienceIsSet()
    {
        $expectedToken = ['id_token' => 'idtoken12345'];
        $timesCalled = 0;
        $httpHandler = function ($request) use (&$timesCalled, $expectedToken) {
            $timesCalled++;
            if ($timesCalled == 1) {
                return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
            }
            $this->assertEquals(
                '/computeMetadata/' . GCECredentials::ID_TOKEN_URI_PATH,
                $request->getUri()->getPath()
            );
            $this->assertEquals(
                'audience=a+target+audience',
                $request->getUri()->getQuery()
            );
            return new Psr7\Response(200, [], Utils::streamFor($expectedToken['id_token']));
        };
        $g = new GCECredentials(null, null, 'a+target+audience');
        $this->assertEquals($expectedToken, $g->fetchAuthToken($httpHandler));
        $this->assertEquals(2, $timesCalled);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Scope and targetAudience cannot both be supplied
     */
    public function testSettingBothScopeAndTargetAudienceThrowsException()
    {
        $g = new GCECredentials(null, 'a-scope', 'a+target+audience');
    }

    /**
     * @dataProvider scopes
     */
    public function testFetchAuthTokenCustomScope($scope, $expected)
    {
        $this->onlyGuzzle6And7();

        $uri = null;
        $client = $this->prophesize('GuzzleHttp\ClientInterface');
        $client->send(Argument::any(), Argument::any())
            ->will(function () use (&$uri) {
                $this->send(Argument::any(), Argument::any())->will(function ($args) use (&$uri) {
                    $uri = $args[0]->getUri();

                    return buildResponse(200, [], Utils::streamFor('{"expires_in": 0}'));
                });

                return buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
            });

        HttpClientCache::setHttpClient($client->reveal());

        $g = new GCECredentials(null, $scope);
        $g->fetchAuthToken();
        parse_str($uri->getQuery(), $query);

        $this->assertArrayHasKey('scopes', $query);
        $this->assertEquals($expected, $query['scopes']);
    }

    public function scopes()
    {
        return [
            ['foobar', 'foobar'],
            [['foobar'], 'foobar'],
            ['hello world', 'hello,world'],
            [['hello', 'world'], 'hello,world']
        ];
    }

    public function testGetLastReceivedTokenIsNullByDefault()
    {
        $creds = new GCECredentials();
        $this->assertNull($creds->getLastReceivedToken());
    }

    public function testGetClientName()
    {
        $expected = 'foobar';

        $httpHandler = getHandler([
            buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
            buildResponse(200, [], Utils::streamFor($expected)),
            buildResponse(200, [], Utils::streamFor('notexpected'))
        ]);

        $creds = new GCECredentials();
        $this->assertEquals($expected, $creds->getClientName($httpHandler));

        // call again to test cached value
        $this->assertEquals($expected, $creds->getClientName($httpHandler));
    }

    public function testGetClientNameShouldBeEmptyIfNotOnGCE()
    {
        // simulate retry attempts by returning multiple 500s
        $httpHandler = getHandler([
            buildResponse(500),
            buildResponse(500),
            buildResponse(500)
        ]);

        $creds = new GCECredentials();
        $this->assertEquals('', $creds->getClientName($httpHandler));
    }

    public function testSignBlob()
    {
        $this->onlyGuzzle6And7();

        $expectedEmail = 'test@test.com';
        $expectedAccessToken = 'token';
        $stringToSign = 'inputString';
        $resultString = 'foobar';
        $token = [
            'access_token' => $expectedAccessToken,
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];

        $iam = $this->prophesize('Google\Auth\Iam');
        $iam->signBlob($expectedEmail, $expectedAccessToken, $stringToSign)
            ->shouldBeCalled()
            ->willReturn($resultString);

        $client = $this->prophesize('GuzzleHttp\ClientInterface');
        $client->send(Argument::any(), Argument::any())
            ->willReturn(
                buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
                buildResponse(200, [], Utils::streamFor($expectedEmail)),
                buildResponse(200, [], Utils::streamFor(json_encode($token)))
            );

        HttpClientCache::setHttpClient($client->reveal());

        $creds = new GCECredentials($iam->reveal());
        $signature = $creds->signBlob($stringToSign);
    }

    public function testSignBlobWithLastReceivedAccessToken()
    {
        $this->onlyGuzzle6And7();

        $expectedEmail = 'test@test.com';
        $expectedAccessToken = 'token';
        $notExpectedAccessToken = 'othertoken';
        $stringToSign = 'inputString';
        $resultString = 'foobar';
        $token1 = [
            'access_token' => $expectedAccessToken,
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];
        $token2 = [
            'access_token' => $notExpectedAccessToken,
            'expires_in' => '57',
            'token_type' => 'Bearer',
        ];

        $iam = $this->prophesize('Google\Auth\Iam');
        $iam->signBlob($expectedEmail, $expectedAccessToken, $stringToSign)
            ->shouldBeCalled()
            ->willReturn($resultString);

        $client = $this->prophesize('GuzzleHttp\ClientInterface');
        $client->send(Argument::any(), Argument::any())
            ->willReturn(
                buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
                buildResponse(200, [], Utils::streamFor(json_encode($token1))),
                buildResponse(200, [], Utils::streamFor($expectedEmail)),
                buildResponse(200, [], Utils::streamFor(json_encode($token2)))
            );

        HttpClientCache::setHttpClient($client->reveal());

        $creds = new GCECredentials($iam->reveal());
        // cache a token
        $creds->fetchAuthToken();

        $signature = $creds->signBlob($stringToSign);
    }

    public function testGetProjectId()
    {
        $this->onlyGuzzle6And7();

        $expected = 'foobar';

        $client = $this->prophesize('GuzzleHttp\ClientInterface');
        $client->send(Argument::any(), Argument::any())
            ->willReturn(
                buildResponse(200, [GCECredentials::FLAVOR_HEADER => 'Google']),
                buildResponse(200, [], Utils::streamFor($expected)),
                buildResponse(200, [], Utils::streamFor('notexpected'))
            );

        HttpClientCache::setHttpClient($client->reveal());

        $creds = new GCECredentials();
        $this->assertEquals($expected, $creds->getProjectId());

        // call again to test cached value
        $this->assertEquals($expected, $creds->getProjectId());
    }

    public function testGetProjectIdShouldBeEmptyIfNotOnGCE()
    {
        $this->onlyGuzzle6And7();

        // simulate retry attempts by returning multiple 500s
        $client = $this->prophesize('GuzzleHttp\ClientInterface');
        $client->send(Argument::any(), Argument::any())
            ->willReturn(
                buildResponse(500),
                buildResponse(500),
                buildResponse(500)
            );

        HttpClientCache::setHttpClient($client->reveal());

        $creds = new GCECredentials();
        $this->assertNull($creds->getProjectId());
    }

    public function testGetTokenUriWithServiceAccountIdentity()
    {
        $tokenUri = GCECredentials::getTokenUri('foo');
        $this->assertEquals(
            'http://169.254.169.254/computeMetadata/v1/instance/service-accounts/foo/token',
            $tokenUri
        );
    }

    public function testGetAccessTokenWithServiceAccountIdentity()
    {
        $expected = [
            'access_token' => 'token12345',
            'expires_in' => 123,
        ];
        $timesCalled = 0;
        $httpHandler = function ($request) use (&$timesCalled, $expected) {
            $timesCalled++;
            if ($timesCalled == 1) {
                return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
            }
            $this->assertEquals(
                '/computeMetadata/v1/instance/service-accounts/foo/token',
                $request->getUri()->getPath()
            );
            $this->assertEquals('', $request->getUri()->getQuery());
            return new Psr7\Response(200, [], Utils::streamFor(json_encode($expected)));
        };

        $g = new GCECredentials(null, null, null, null, 'foo');
        $this->assertEquals(
            $expected['access_token'],
            $g->fetchAuthToken($httpHandler)['access_token']
        );
    }

    public function testGetIdTokenWithServiceAccountIdentity()
    {
        $expected = 'idtoken12345';
        $timesCalled = 0;
        $httpHandler = function ($request) use (&$timesCalled, $expected) {
            $timesCalled++;
            if ($timesCalled == 1) {
                return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
            }
            $this->assertEquals(
                '/computeMetadata/v1/instance/service-accounts/foo/identity',
                $request->getUri()->getPath()
            );
            $this->assertEquals(
                'audience=a+target+audience',
                $request->getUri()->getQuery()
            );
            return new Psr7\Response(200, [], Utils::streamFor($expected));
        };
        $g = new GCECredentials(null, null, 'a+target+audience', null, 'foo');
        $this->assertEquals(
            ['id_token' => $expected],
            $g->fetchAuthToken($httpHandler)
        );
    }

    public function testGetClientNameUriWithServiceAccountIdentity()
    {
        $clientNameUri = GCECredentials::getClientNameUri('foo');
        $this->assertEquals(
            'http://169.254.169.254/computeMetadata/v1/instance/service-accounts/foo/email',
            $clientNameUri
        );
    }

    public function testGetClientNameWithServiceAccountIdentity()
    {
        $expected = 'expected';
        $timesCalled = 0;
        $httpHandler = function ($request) use (&$timesCalled, $expected) {
            $timesCalled++;
            if ($timesCalled == 1) {
                return new Psr7\Response(200, [GCECredentials::FLAVOR_HEADER => 'Google']);
            }
            $this->assertEquals(
                '/computeMetadata/v1/instance/service-accounts/foo/email',
                $request->getUri()->getPath()
            );
            $this->assertEquals('', $request->getUri()->getQuery());
            return new Psr7\Response(200, [], Utils::streamFor($expected));
        };

        $creds = new GCECredentials(null, null, null, null, 'foo');
        $this->assertEquals($expected, $creds->getClientName($httpHandler));
    }
}
