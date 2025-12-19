<?php

namespace PayPal\Test\Api;

use PayPal\Api\Authorization;
use PayPal\Transport\PPRestCall;

/**
 * Class Authorization
 *
 * @package PayPal\Test\Api
 */
class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Authorization
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","amount":' . AmountTest::getJson() . ',"payment_mode":"TestSample","state":"TestSample","reason_code":"TestSample","pending_reason":"TestSample","protection_eligibility":"TestSample","protection_eligibility_type":"TestSample","fmf_details":' . FmfDetailsTest::getJson() . ',"parent_payment":"TestSample","valid_until":"TestSample","create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Authorization
     */
    public static function getObject()
    {
        return new Authorization(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Authorization
     */
    public function testSerializationDeserialization()
    {
        $obj = new Authorization(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getPaymentMode());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getReasonCode());
        $this->assertNotNull($obj->getPendingReason());
        $this->assertNotNull($obj->getProtectionEligibility());
        $this->assertNotNull($obj->getProtectionEligibilityType());
        $this->assertNotNull($obj->getFmfDetails());
        $this->assertNotNull($obj->getParentPayment());
        $this->assertNotNull($obj->getValidUntil());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Authorization $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
        $this->assertEquals($obj->getPaymentMode(), "TestSample");
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getReasonCode(), "TestSample");
        $this->assertEquals($obj->getPendingReason(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibility(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibilityType(), "TestSample");
        $this->assertEquals($obj->getFmfDetails(), FmfDetailsTest::getObject());
        $this->assertEquals($obj->getParentPayment(), "TestSample");
        $this->assertEquals($obj->getValidUntil(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Authorization $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                AuthorizationTest::getJson()
            ));

        $result = $obj->get("authorizationId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Authorization $obj
     */
    public function testCapture($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                CaptureTest::getJson()
            ));
        $capture = CaptureTest::getObject();

        $result = $obj->capture($capture, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Authorization $obj
     */
    public function testVoid($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                self::getJson()
            ));

        $result = $obj->void($mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Authorization $obj
     */
    public function testReauthorize($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                self::getJson()
            ));

        $result = $obj->reauthorize($mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    public function mockProvider()
    {
        $obj = self::getObject();
        $mockApiContext = $this->getMockBuilder('ApiContext')
            ->disableOriginalConstructor()
            ->getMock();
        return array(
            array($obj, $mockApiContext),
            array($obj, null)
        );
    }
}
