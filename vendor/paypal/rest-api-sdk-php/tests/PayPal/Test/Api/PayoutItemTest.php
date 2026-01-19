<?php

namespace PayPal\Test\Api;

use PayPal\Api\ItemsArray;
use PayPal\Api\PayoutItem;
use PayPal\Transport\PPRestCall;

/**
 * Class PayoutItem
 *
 * @package PayPal\Test\Api
 */
class PayoutItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PayoutItem
     * @return string
     */
    public static function getJson()
    {
        return '{"recipient_type":"TestSample","amount":' .CurrencyTest::getJson() . ',"note":"TestSample","receiver":"TestSample","sender_item_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PayoutItem
     */
    public static function getObject()
    {
        return new PayoutItem(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PayoutItem
     */
    public function testSerializationDeserialization()
    {
        $obj = new PayoutItem(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getRecipientType());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getNote());
        $this->assertNotNull($obj->getReceiver());
        $this->assertNotNull($obj->getSenderItemId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PayoutItem $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getRecipientType(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getNote(), "TestSample");
        $this->assertEquals($obj->getReceiver(), "TestSample");
        $this->assertEquals($obj->getSenderItemId(), "TestSample");
    }

    /**
     * @dataProvider mockProvider
     * @param PayoutItem $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                PayoutItemDetailsTest::getJson()
            ));

        $result = $obj->get("payoutItemId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param PayoutItem $obj
     */
    public function testCancel($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                PayoutItemDetailsTest::getJson()
            ));

        $result = $obj->cancel("payoutItemId", $mockApiContext, $mockPPRestCall);
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
