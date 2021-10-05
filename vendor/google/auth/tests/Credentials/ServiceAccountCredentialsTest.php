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

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\ServiceAccountJwtAccessCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\OAuth2;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

// Creates a standard JSON auth object for testing.
function createTestJson()
{
    return [
        'private_key_id' => 'key123',
        'private_key' => 'privatekey',
        'client_email' => 'test@example.com',
        'client_id' => 'client123',
        'type' => 'service_account',
        'project_id' => 'example_project'
    ];
}

class SACGetCacheKeyTest extends TestCase
{
    public function testShouldBeTheSameAsOAuth2WithTheSameScope()
    {
        $testJson = createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $o = new OAuth2(['scope' => $scope]);
        $this->assertSame(
            $testJson['client_email'] . ':' . $o->getCacheKey(),
            $sa->getCacheKey()
        );
    }

    public function testShouldBeTheSameAsOAuth2WithTheSameScopeWithSub()
    {
        $testJson = createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $sub = 'sub123';
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson,
            $sub
        );
        $o = new OAuth2(['scope' => $scope]);
        $this->assertSame(
            $testJson['client_email'] . ':' . $o->getCacheKey() . ':' . $sub,
            $sa->getCacheKey()
        );
    }

    public function testShouldBeTheSameAsOAuth2WithTheSameScopeWithSubAddedLater()
    {
        $testJson = createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $sub = 'sub123';
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson,
            null
        );
        $sa->setSub($sub);

        $o = new OAuth2(['scope' => $scope]);
        $this->assertSame(
            $testJson['client_email'] . ':' . $o->getCacheKey() . ':' . $sub,
            $sa->getCacheKey()
        );
    }
}

class SACConstructorTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldFailIfScopeIsNotAValidType()
    {
        $testJson = createTestJson();
        $notAnArrayOrString = new \stdClass();
        $sa = new ServiceAccountCredentials(
            $notAnArrayOrString,
            $testJson
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldFailIfJsonDoesNotHaveClientEmail()
    {
        $testJson = createTestJson();
        unset($testJson['client_email']);
        $scope = ['scope/1', 'scope/2'];
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldFailIfJsonDoesNotHavePrivateKey()
    {
        $testJson = createTestJson();
        unset($testJson['private_key']);
        $scope = ['scope/1', 'scope/2'];
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToInitalizeFromANonExistentFile()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/does-not-exist-private.json';
        new ServiceAccountCredentials('scope/1', $keyFile);
    }

    public function testInitalizeFromAFile()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/private.json';
        $this->assertNotNull(
            new ServiceAccountCredentials('scope/1', $keyFile)
        );
    }

    /**
     * @expectedException LogicException
     */
    public function testFailsToInitializeFromInvalidJsonData()
    {
        $tmp = tmpfile();
        fwrite($tmp, '{');

        $path = stream_get_meta_data($tmp)['uri'];

        try {
            new ServiceAccountCredentials('scope/1', $path);
        } catch (\Exception $e) {
            fclose($tmp);
            throw $e;
        }
    }
}

class SACFromEnvTest extends TestCase
{
    protected function tearDown()
    {
        putenv(ServiceAccountCredentials::ENV_VAR);  // removes it from
    }

    public function testIsNullIfEnvVarIsNotSet()
    {
        $this->assertNull(ServiceAccountCredentials::fromEnv());
    }

    /**
     * @expectedException DomainException
     */
    public function testFailsIfEnvSpecifiesNonExistentFile()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/does-not-exist-private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        ApplicationDefaultCredentials::getCredentials('a scope');
    }

    public function testSucceedIfFileExists()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/private.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        $this->assertNotNull(ApplicationDefaultCredentials::getCredentials('a scope'));
    }
}

class SACFromWellKnownFileTest extends TestCase
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
    }

    public function testIsNullIfFileDoesNotExist()
    {
        putenv('HOME=' . __DIR__ . '/../not_exists_fixtures');
        $this->assertNull(
            ServiceAccountCredentials::fromWellKnownFile()
        );
    }

    public function testSucceedIfFileIsPresent()
    {
        putenv('HOME=' . __DIR__ . '/../fixtures');
        $this->assertNotNull(
            ApplicationDefaultCredentials::getCredentials('a scope')
        );
    }
}

class SACFetchAuthTokenTest extends TestCase
{
    private $privateKey;

    public function setUp()
    {
        $this->privateKey =
            file_get_contents(__DIR__ . '/../fixtures' . '/private.pem');
    }

    private function createTestJson()
    {
        $testJson = createTestJson();
        $testJson['private_key'] = $this->privateKey;

        return $testJson;
    }

    /**
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testFailsOnClientErrors()
    {
        $testJson = $this->createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $httpHandler = getHandler([
            buildResponse(400),
        ]);
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->fetchAuthToken($httpHandler);
    }

    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testFailsOnServerErrors()
    {
        $testJson = $this->createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $httpHandler = getHandler([
            buildResponse(500),
        ]);
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->fetchAuthToken($httpHandler);
    }

    public function testCanFetchCredsOK()
    {
        $testJson = $this->createTestJson();
        $testJsonText = json_encode($testJson);
        $scope = ['scope/1', 'scope/2'];
        $httpHandler = getHandler([
            buildResponse(200, [], Utils::streamFor($testJsonText)),
        ]);
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $tokens = $sa->fetchAuthToken($httpHandler);
        $this->assertEquals($testJson, $tokens);
    }

    public function testUpdateMetadataFunc()
    {
        $testJson = $this->createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $access_token = 'accessToken123';
        $responseText = json_encode(array('access_token' => $access_token));
        $httpHandler = getHandler([
            buildResponse(200, [], Utils::streamFor($responseText)),
        ]);
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $update_metadata = $sa->getUpdateMetadataFunc();
        $this->assertInternalType('callable', $update_metadata);

        $actual_metadata = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = null,
            $httpHandler
        );
        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );
        $this->assertEquals(
            $actual_metadata[CredentialsLoader::AUTH_METADATA_KEY],
            array('Bearer ' . $access_token)
        );
    }

    public function testShouldBeIdTokenWhenTargetAudienceIsSet()
    {
        $testJson = $this->createTestJson();
        $expectedToken = ['id_token' => 'idtoken12345'];
        $timesCalled = 0;
        $httpHandler = function ($request) use (&$timesCalled, $expectedToken) {
            $timesCalled++;
            parse_str($request->getBody(), $post);
            $this->assertArrayHasKey('assertion', $post);
            list($header, $payload, $sig) = explode('.', $post['assertion']);
            $jwtParams = json_decode(base64_decode($payload), true);
            $this->assertArrayHasKey('target_audience', $jwtParams);
            $this->assertEquals('a target audience', $jwtParams['target_audience']);

            return new Psr7\Response(200, [], Utils::streamFor(json_encode($expectedToken)));
        };
        $sa = new ServiceAccountCredentials(null, $testJson, null, 'a target audience');
        $this->assertEquals($expectedToken, $sa->fetchAuthToken($httpHandler));
        $this->assertEquals(1, $timesCalled);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Scope and targetAudience cannot both be supplied
     */
    public function testSettingBothScopeAndTargetAudienceThrowsException()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountCredentials(
            'a-scope',
            $testJson,
            null,
            'a-target-audience'
        );
    }
}

class SACGetClientNameTest extends TestCase
{
    public function testReturnsClientEmail()
    {
        $testJson = createTestJson();
        $sa = new ServiceAccountCredentials('scope/1', $testJson);
        $this->assertEquals($testJson['client_email'], $sa->getClientName());
    }
}

class SACGetProjectIdTest extends TestCase
{
    public function testGetProjectId()
    {
        $testJson = createTestJson();
        $sa = new ServiceAccountCredentials('scope/1', $testJson);
        $this->assertEquals($testJson['project_id'], $sa->getProjectId());
    }
}

class SACGetQuotaProjectTest extends TestCase
{
    public function testGetQuotaProject()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/private.json';
        $sa = new ServiceAccountCredentials('scope/1', $keyFile);
        $this->assertEquals('test_quota_project', $sa->getQuotaProject());
    }
}

class SACJwtAccessTest extends TestCase
{
    private $privateKey;

    public function setUp()
    {
        $this->privateKey =
            file_get_contents(__DIR__ . '/../fixtures' . '/private.pem');
    }

    private function createTestJson()
    {
        $testJson = createTestJson();
        $testJson['private_key'] = $this->privateKey;

        return $testJson;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToInitalizeFromANonExistentFile()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/does-not-exist-private.json';
        new ServiceAccountJwtAccessCredentials($keyFile);
    }

    public function testInitalizeFromAFile()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/private.json';
        $this->assertNotNull(
            new ServiceAccountJwtAccessCredentials($keyFile)
        );
    }

    /**
     * @expectedException LogicException
     */
    public function testFailsToInitializeFromInvalidJsonData()
    {
        $tmp = tmpfile();
        fwrite($tmp, '{');

        $path = stream_get_meta_data($tmp)['uri'];

        try {
            new ServiceAccountJwtAccessCredentials($path);
        } catch (\Exception $e) {
            fclose($tmp);
            throw $e;
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsOnMissingClientEmail()
    {
        $testJson = $this->createTestJson();
        unset($testJson['client_email']);
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsOnMissingPrivateKey()
    {
        $testJson = $this->createTestJson();
        unset($testJson['private_key']);
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Cannot sign both audience and scope in JwtAccess
     */
    public function testFailsWithBothAudienceAndScope()
    {
        $scope = 'scope/1';
        $audience = 'https://example.com/service';
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials($testJson, $scope);
        $sa->updateMetadata([], $audience);
    }

    public function testCanInitializeFromJson()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
        $this->assertNotNull($sa);
    }

    public function testNoOpOnFetchAuthToken()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
        $this->assertNotNull($sa);

        $httpHandler = getHandler([
            buildResponse(200),
        ]);
        $result = $sa->fetchAuthToken($httpHandler); // authUri has not been set
        $this->assertNull($result);
    }

    public function testAuthUriIsNotSet()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
        $this->assertNotNull($sa);

        $update_metadata = $sa->getUpdateMetadataFunc();
        $this->assertInternalType('callable', $update_metadata);

        $actual_metadata = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = null
        );
        $this->assertArrayNotHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );
    }

    public function testGetLastReceivedToken()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials($testJson);
        $token = $sa->fetchAuthToken();
        $this->assertEquals($token, $sa->getLastReceivedToken());
    }

    public function testUpdateMetadataFunc()
    {
        $testJson = $this->createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials(
            $testJson
        );
        $this->assertNotNull($sa);

        $update_metadata = $sa->getUpdateMetadataFunc();
        $this->assertInternalType('callable', $update_metadata);

        $actual_metadata = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = 'https://example.com/service'
        );
        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );

        $authorization = $actual_metadata[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization);

        $bearer_token = current($authorization);
        $this->assertInternalType('string', $bearer_token);
        $this->assertEquals(0, strpos($bearer_token, 'Bearer '));
        $this->assertGreaterThan(30, strlen($bearer_token));

        $actual_metadata2 = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = 'https://example.com/anotherService'
        );
        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata2
        );

        $authorization2 = $actual_metadata2[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization2);

        $bearer_token2 = current($authorization2);
        $this->assertInternalType('string', $bearer_token2);
        $this->assertEquals(0, strpos($bearer_token2, 'Bearer '));
        $this->assertGreaterThan(30, strlen($bearer_token2));
        $this->assertNotEquals($bearer_token2, $bearer_token);
    }
}

class SACJwtAccessComboTest extends TestCase
{
    private $privateKey;

    public function setUp()
    {
        $this->privateKey =
            file_get_contents(__DIR__ . '/../fixtures' . '/private.pem');
    }

    private function createTestJson()
    {
        $testJson = createTestJson();
        $testJson['private_key'] = $this->privateKey;

        return $testJson;
    }

    public function testNoScopeUseJwtAccess()
    {
        $testJson = $this->createTestJson();
        // no scope, jwt access should be used, no outbound
        // call should be made
        $scope = null;
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $this->assertNotNull($sa);

        $update_metadata = $sa->getUpdateMetadataFunc();
        $this->assertInternalType('callable', $update_metadata);

        $actual_metadata = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = 'https://example.com/service'
        );
        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );

        $authorization = $actual_metadata[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization);

        $bearer_token = current($authorization);
        $this->assertInternalType('string', $bearer_token);
        $this->assertEquals(0, strpos($bearer_token, 'Bearer '));
        $this->assertGreaterThan(30, strlen($bearer_token));
    }

    public function testUpdateMetadataWithScopeAndUseJwtAccessWithScopeParameter()
    {
        $testJson = $this->createTestJson();
        // jwt access should be used even when scopes are supplied, no outbound
        // call should be made
        $scope = 'scope1 scope2';
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->useJwtAccessWithScope();

        $actual_metadata = $sa->updateMetadata(
            $metadata = array('foo' => 'bar'),
            $authUri = 'https://example.com/service'
        );

        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );

        $authorization = $actual_metadata[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization);

        $bearer_token = current($authorization);
        $this->assertInternalType('string', $bearer_token);
        $this->assertEquals(0, strpos($bearer_token, 'Bearer '));

        // Ensure scopes are signed inside
        $token = substr($bearer_token, strlen('Bearer '));
        $this->assertEquals(2, substr_count($token, '.'));
        list($header, $payload, $sig) = explode('.', $bearer_token);
        $json = json_decode(base64_decode($payload), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('scope', $json);
        $this->assertEquals($json['scope'], $scope);
    }

    public function testUpdateMetadataWithScopeAndUseJwtAccessWithScopeParameterAndArrayScopes()
    {
        $testJson = $this->createTestJson();
        // jwt access should be used even when scopes are supplied, no outbound
        // call should be made
        $scope = ['scope1', 'scope2'];
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->useJwtAccessWithScope();

        $actual_metadata = $sa->updateMetadata(
            $metadata = array('foo' => 'bar'),
            $authUri = 'https://example.com/service'
        );

        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );

        $authorization = $actual_metadata[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization);

        $bearer_token = current($authorization);
        $this->assertInternalType('string', $bearer_token);
        $this->assertEquals(0, strpos($bearer_token, 'Bearer '));

        // Ensure scopes are signed inside
        $token = substr($bearer_token, strlen('Bearer '));
        $this->assertEquals(2, substr_count($token, '.'));
        list($header, $payload, $sig) = explode('.', $bearer_token);
        $json = json_decode(base64_decode($payload), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('scope', $json);
        $this->assertEquals($json['scope'], implode(' ', $scope));

        // Test last received token
        $cachedToken = $sa->getLastReceivedToken();
        $this->assertInternalType('array', $cachedToken);
        $this->assertArrayHasKey('access_token', $cachedToken);
        $this->assertEquals($token, $cachedToken['access_token']);
    }

    public function testFetchAuthTokenWithScopeAndUseJwtAccessWithScopeParameter()
    {
        $testJson = $this->createTestJson();
        // jwt access should be used even when scopes are supplied, no outbound
        // call should be made
        $scope = 'scope1 scope2';
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->useJwtAccessWithScope();

        $access_token = $sa->fetchAuthToken();
        $this->assertInternalType('array', $access_token);
        $this->assertArrayHasKey('access_token', $access_token);
        $token = $access_token['access_token'];

        // Ensure scopes are signed inside
        $this->assertEquals(2, substr_count($token, '.'));
        list($header, $payload, $sig) = explode('.', $token);
        $json = json_decode(base64_decode($payload), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('scope', $json);
        $this->assertEquals($json['scope'], $scope);
    }

    public function testFetchAuthTokenWithScopeAndUseJwtAccessWithScopeParameterAndArrayScopes()
    {
        $testJson = $this->createTestJson();
        // jwt access should be used even when scopes are supplied, no outbound
        // call should be made
        $scope = ['scope1', 'scope2'];
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $sa->useJwtAccessWithScope();

        $access_token = $sa->fetchAuthToken();
        $this->assertInternalType('array', $access_token);
        $this->assertArrayHasKey('access_token', $access_token);
        $token = $access_token['access_token'];

        // Ensure scopes are signed inside
        $this->assertEquals(2, substr_count($token, '.'));
        list($header, $payload, $sig) = explode('.', $token);
        $json = json_decode(base64_decode($payload), true);
        $this->assertInternalType('array', $json);
        $this->assertArrayHasKey('scope', $json);
        $this->assertEquals($json['scope'], implode(' ', $scope));

        // Test last received token
        $cachedToken = $sa->getLastReceivedToken();
        $this->assertInternalType('array', $cachedToken);
        $this->assertArrayHasKey('access_token', $cachedToken);
        $this->assertEquals($token, $cachedToken['access_token']);
    }

    /** @runInSeparateProcess */
    public function testJwtAccessFromApplicationDefault()
    {
        $keyFile = __DIR__ . '/../fixtures3/service_account_credentials.json';
        putenv(ServiceAccountCredentials::ENV_VAR . '=' . $keyFile);
        $creds = ApplicationDefaultCredentials::getCredentials(
            null, // $scope
            null, // $httpHandler
            null, // $cacheConfig
            null, // $cache
            null, // $quotaProject
            'a default scope' // $defaultScope
        );
        $authUri = 'https://example.com/service';

        $metadata = $creds->updateMetadata(['foo' => 'bar'], $authUri);

        $this->assertArrayHasKey('authorization', $metadata);
        $token = str_replace('Bearer ', '', $metadata['authorization'][0]);
        $key = file_get_contents(__DIR__ . '/../fixtures3/key.pub');

        $class = 'JWT';
        if (class_exists('Firebase\JWT\JWT')) {
            $class = 'Firebase\JWT\JWT';
        }
        $jwt = new $class();
        $result = $jwt::decode($token, $key, ['RS256']);

        $this->assertEquals($authUri, $result->aud);
    }

    public function testNoScopeAndNoAuthUri()
    {
        $testJson = $this->createTestJson();
        // no scope, jwt access should be used, no outbound
        // call should be made
        $scope = null;
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $this->assertNotNull($sa);

        $update_metadata = $sa->getUpdateMetadataFunc();
        $this->assertInternalType('callable', $update_metadata);

        $actual_metadata = call_user_func(
            $update_metadata,
            $metadata = array('foo' => 'bar'),
            $authUri = null
        );
        // no access_token is added to the metadata hash
        // but also, no error should be thrown
        $this->assertInternalType('array', $actual_metadata);
        $this->assertArrayNotHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $actual_metadata
        );
    }

    public function testUpdateMetadataJwtAccess()
    {
        $testJson = $this->createTestJson();
        // no scope, jwt access should be used, no outbound
        // call should be made
        $scope = null;
        $sa = new ServiceAccountCredentials(
            $scope,
            $testJson
        );
        $this->assertNotNull($sa);
        $metadata = $sa->updateMetadata(
            array('foo' => 'bar'),
            'https://example.com/service'
        );
        $this->assertArrayHasKey(
            CredentialsLoader::AUTH_METADATA_KEY,
            $metadata
        );

        $authorization = $metadata[CredentialsLoader::AUTH_METADATA_KEY];
        $this->assertInternalType('array', $authorization);

        $bearerToken = current($authorization);
        $this->assertInternalType('string', $bearerToken);
        $this->assertEquals(0, strpos($bearerToken, 'Bearer '));
        $token = str_replace('Bearer ', '', $bearerToken);

        $lastReceivedToken = $sa->getLastReceivedToken();
        $this->assertArrayHasKey('access_token', $lastReceivedToken);
        $this->assertEquals($token, $lastReceivedToken['access_token']);
    }
}

class SACJWTGetCacheKeyTest extends TestCase
{
    public function testShouldBeTheSameAsOAuth2WithTheSameScope()
    {
        $testJson = createTestJson();
        $scope = ['scope/1', 'scope/2'];
        $sa = new ServiceAccountJwtAccessCredentials($testJson);
        $this->assertNull($sa->getCacheKey());
    }
}

class SACJWTGetClientNameTest extends TestCase
{
    public function testReturnsClientEmail()
    {
        $testJson = createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials($testJson);
        $this->assertEquals($testJson['client_email'], $sa->getClientName());
    }
}

class SACJWTGetProjectIdTest extends TestCase
{
    public function testGetProjectId()
    {
        $testJson = createTestJson();
        $sa = new ServiceAccountJwtAccessCredentials($testJson);
        $this->assertEquals($testJson['project_id'], $sa->getProjectId());
    }
}

class SACJWTGetQuotaProjectTest extends TestCase
{
    public function testGetQuotaProject()
    {
        $keyFile = __DIR__ . '/../fixtures' . '/private.json';
        $sa = new ServiceAccountJwtAccessCredentials($keyFile);
        $this->assertEquals('test_quota_project', $sa->getQuotaProject());
    }
}
