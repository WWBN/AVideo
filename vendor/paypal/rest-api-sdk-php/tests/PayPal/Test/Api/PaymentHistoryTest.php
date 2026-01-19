<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentHistory;

/**
 * Class PaymentHistory
 *
 * @package PayPal\Test\Api
 */
class PaymentHistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentHistory
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"payments":' . PaymentTest::getJson() . ',"count":123,"next_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return PaymentHistory
     */
    public static function getObject()
    {
        return new PaymentHistory(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return PaymentHistory
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentHistory(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPayments());
        $this->assertNotNull($obj->getCount());
        $this->assertNotNull($obj->getNextId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentHistory $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPayments(), PaymentTest::getObject());
        $this->assertEquals($obj->getCount(), 123);
        $this->assertEquals($obj->getNextId(), "TestSample");
    }


}
