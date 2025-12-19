<?php

namespace PayPal\Test\Api;

use PayPal\Api\Refund;

/**
 * Class Refund
 *
 * @package PayPal\Test\Api
 */
class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Refund
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","amount":' . AmountTest::getJson() . ',"state":"TestSample","reason":"TestSample","sale_id":"TestSample","capture_id":"TestSample","parent_payment":"TestSample","description":"TestSample","create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Refund
     */
    public static function getObject()
    {
        return new Refund(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Refund
     */
    public function testSerializationDeserialization()
    {
        $obj = new Refund(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getReason());
        $this->assertNotNull($obj->getSaleId());
        $this->assertNotNull($obj->getCaptureId());
        $this->assertNotNull($obj->getParentPayment());
        $this->assertNotNull($obj->getDescription());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Refund $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getReason(), "TestSample");
        $this->assertEquals($obj->getSaleId(), "TestSample");
        $this->assertEquals($obj->getCaptureId(), "TestSample");
        $this->assertEquals($obj->getParentPayment(), "TestSample");
        $this->assertEquals($obj->getDescription(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Refund $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                RefundTest::getJson()
            ));

        $result = $obj->get("refundId", $mockApiContext, $mockPPRestCall);
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
