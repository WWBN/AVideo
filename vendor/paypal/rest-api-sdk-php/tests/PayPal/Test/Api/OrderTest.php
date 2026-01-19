<?php

namespace PayPal\Test\Api;

use PayPal\Api\Authorization;
use PayPal\Api\Order;

/**
 * Class Order
 *
 * @package PayPal\Test\Api
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Order
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","purchase_unit_reference_id":"TestSample","amount":' . AmountTest::getJson() . ',"payment_mode":"TestSample","state":"TestSample","reason_code":"TestSample","pending_reason":"TestSample","protection_eligibility":"TestSample","protection_eligibility_type":"TestSample","parent_payment":"TestSample","fmf_details":' . FmfDetailsTest::getJson() . ',"create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Order
     */
    public static function getObject()
    {
        return new Order(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Order
     */
    public function testSerializationDeserialization()
    {
        $obj = new Order(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getPurchaseUnitReferenceId());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getPaymentMode());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getReasonCode());
        $this->assertNotNull($obj->getPendingReason());
        $this->assertNotNull($obj->getProtectionEligibility());
        $this->assertNotNull($obj->getProtectionEligibilityType());
        $this->assertNotNull($obj->getParentPayment());
        $this->assertNotNull($obj->getFmfDetails());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Order $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getPurchaseUnitReferenceId(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
        $this->assertEquals($obj->getPaymentMode(), "TestSample");
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getReasonCode(), "TestSample");
        $this->assertEquals($obj->getPendingReason(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibility(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibilityType(), "TestSample");
        $this->assertEquals($obj->getParentPayment(), "TestSample");
        $this->assertEquals($obj->getFmfDetails(), FmfDetailsTest::getObject());
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Order $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                OrderTest::getJson()
            ));

        $result = $obj->get("orderId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Order $obj
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
     * @param Order $obj
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
     * @param Order $obj
     */
    public function testAuthorize($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                AuthorizationTest::getJson()
            ));

        $authorization = new Authorization();
        $result = $obj->authorize($authorization, $mockApiContext, $mockPPRestCall);
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
