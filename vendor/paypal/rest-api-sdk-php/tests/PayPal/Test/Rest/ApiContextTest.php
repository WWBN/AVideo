<?php

use PayPal\Rest\ApiContext;

/**
 * Test class for ApiContextTest.
 *
 */
class ApiContextTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ApiContext
     */
    public $apiContext;

    public function setUp()
    {
        $this->apiContext = new ApiContext();
    }

    public function testGetRequestId()
    {
        $requestId = $this->apiContext->getRequestId();
        $this->assertNotNull($requestId);
        $this->assertEquals($requestId, $this->apiContext->getRequestId());
    }

    public function testResetRequestId()
    {
        $requestId = $this->apiContext->getRequestId();
        $newRequestId = $this->apiContext->resetRequestId();
        $this->assertNotNull($newRequestId);
        $this->assertNotEquals($newRequestId, $requestId);
    }

}
