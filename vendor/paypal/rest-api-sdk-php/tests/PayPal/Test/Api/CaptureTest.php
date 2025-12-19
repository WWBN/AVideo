<?php

namespace PayPal\Test\Api;

use PayPal\Api\Capture;
use PayPal\Transport\PPRestCall;

/**
 * Class Capture
 *
 * @package PayPal\Test\Api
 */
class CaptureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Capture
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","amount":' . AmountTest::getJson() . ',"is_final_capture":true,"state":"TestSample","parent_payment":"TestSample","transaction_fee":' . CurrencyTest::getJson() . ',"create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Capture
     */
    public static function getObject()
    {
        return new Capture(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Capture
     */
    public function testSerializationDeserialization()
    {
        $obj = new Capture(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getIsFinalCapture());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getParentPayment());
        $this->assertNotNull($obj->getTransactionFee());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Capture $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
        $this->assertEquals($obj->getIsFinalCapture(), true);
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getParentPayment(), "TestSample");
        $this->assertEquals($obj->getTransactionFee(), CurrencyTest::getObject());
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Capture $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                CaptureTest::getJson()
            ));

        $result = $obj->get("captureId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Capture $obj
     */
    public function testRefund($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                RefundTest::getJson()
            ));
        $refund = RefundTest::getObject();

        $result = $obj->refund($refund, $mockApiContext, $mockPPRestCall);
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
