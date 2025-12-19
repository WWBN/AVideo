<?php

namespace PayPal\Test\Auth;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Cache\AuthorizationCache;
use PayPal\Core\PayPalConfigManager;
use PayPal\Rest\ApiContext;
use PayPal\Test\Cache\AuthorizationCacheTest;
use PayPal\Test\Constants;

class OAuthTokenCredentialTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group integration
     */
    public function testGetAccessToken()
    {
        $cred = new OAuthTokenCredential(Constants::CLIENT_ID, Constants::CLIENT_SECRET);
        $this->assertEquals(Constants::CLIENT_ID, $cred->getClientId());
        $this->assertEquals(Constants::CLIENT_SECRET, $cred->getClientSecret());
        $config = PayPalConfigManager::getInstance()->getConfigHashmap();
        $token = $cred->getAccessToken($config);
        $this->assertNotNull($token);

        // Check that we get the same token when issuing a new call before token expiry
        $newToken = $cred->getAccessToken($config);
        $this->assertNotNull($newToken);
        $this->assertEquals($token, $newToken);
    }

    /**
     * @group integration
     */
    public function testInvalidCredentials()
    {
        $this->setExpectedException('PayPal\Exception\PayPalConnectionException');
        $cred = new OAuthTokenCredential('dummy', 'secret');
        $this->assertNull($cred->getAccessToken(PayPalConfigManager::getInstance()->getConfigHashmap()));
    }

    public function testGetAccessTokenUnit()
    {
        $config = array(
            'mode' => 'sandbox',
            'cache.enabled' => true,
            'cache.FileName' => AuthorizationCacheTest::CACHE_FILE
        );
         $cred = new OAuthTokenCredential('clientId', 'clientSecret');

        //{"clientId":{"clientId":"clientId","accessToken":"accessToken","tokenCreateTime":1421204091,"tokenExpiresIn":288000000}}
        AuthorizationCache::push($config, 'clientId', $cred->encrypt('accessToken'), 1421204091, 288000000);

        $apiContext = new ApiContext($cred);
        $apiContext->setConfig($config);
        $this->assertEquals('clientId', $cred->getClientId());
        $this->assertEquals('clientSecret', $cred->getClientSecret());
        $result = $cred->getAccessToken($config);
        $this->assertNotNull($result);
    }

    public function testGetAccessTokenUnitMock()
    {
        $config = array(
            'mode' => 'sandbox'
        );
        /** @var OAuthTokenCredential $auth */
        $auth = $this->getMockBuilder('\PayPal\Auth\OAuthTokenCredential')
            ->setConstructorArgs(array('clientId', 'clientSecret'))
            ->setMethods(array('getToken'))
            ->getMock();

        $auth->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(
                array('refresh_token' => 'refresh_token_value')
            ));
        $response = $auth->getRefreshToken($config, 'auth_value');
        $this->assertNotNull($response);
        $this->assertEquals('refresh_token_value', $response);

    }

    public function testUpdateAccessTokenUnitMock()
    {
        $config = array(
            'mode' => 'sandbox'
        );
        /** @var OAuthTokenCredential $auth */
        $auth = $this->getMockBuilder('\PayPal\Auth\OAuthTokenCredential')
            ->setConstructorArgs(array('clientId', 'clientSecret'))
            ->setMethods(array('getToken'))
            ->getMock();

        $auth->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(
                array(
                    'access_token' => 'accessToken',
                    'expires_in' => 280
                )
            ));

        $response = $auth->updateAccessToken($config);
        $this->assertNotNull($response);
        $this->assertEquals('accessToken', $response);

        $response = $auth->updateAccessToken($config, 'refresh_token');
        $this->assertNotNull($response);
        $this->assertEquals('accessToken', $response);

    }

    /**
     * @expectedException \PayPal\Exception\PayPalConnectionException
     * @expectedExceptionMessage Could not generate new Access token. Invalid response from server:
     */
    public function testUpdateAccessTokenNullReturnUnitMock()
    {
        $config = array(
            'mode' => 'sandbox'
        );
        /** @var OAuthTokenCredential $auth */
        $auth = $this->getMockBuilder('\PayPal\Auth\OAuthTokenCredential')
            ->setConstructorArgs(array('clientId', 'clientSecret'))
            ->setMethods(array('getToken'))
            ->getMock();

        $auth->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(
                array(
                )
            ));

        $response = $auth->updateAccessToken($config);
        $this->assertNotNull($response);
        $this->assertEquals('accessToken', $response);

    }

}
