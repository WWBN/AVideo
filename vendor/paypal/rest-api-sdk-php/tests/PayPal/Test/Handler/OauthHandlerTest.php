<?php

namespace PayPal\Test\Handler;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Core\PayPalHttpConfig;
use PayPal\Handler\OauthHandler;
use PayPal\Rest\ApiContext;

class OauthHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PayPal\Handler\OauthHandler
     */
    public $handler;

    /**
     * @var PayPalHttpConfig
     */
    public $httpConfig;

    /**
     * @var ApiContext
     */
    public $apiContext;

    /**
     * @var array
     */
    public $config;

    public function setUp()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                'clientId',
                'clientSecret'
            )
        );

    }

    public function modeProvider()
    {
        return array(
            array( array('mode' => 'sandbox') ),
            array( array('mode' => 'live')),
            array( array( 'mode' => 'sandbox','oauth.EndPoint' => 'http://localhost/')),
            array( array('mode' => 'sandbox','service.EndPoint' => 'http://service.localhost/'))
        );
    }


    /**
     * @dataProvider modeProvider
     * @param $configs
     */
    public function testGetEndpoint($configs)
    {
        $config = $configs + array(
            'cache.enabled' => true,
            'http.headers.header1' => 'header1value'
        );
        $this->apiContext->setConfig($config);
        $this->httpConfig = new PayPalHttpConfig(null, 'POST', $config);
        $this->handler = new OauthHandler($this->apiContext);
        $this->handler->handle($this->httpConfig, null, $this->config);
    }


}
