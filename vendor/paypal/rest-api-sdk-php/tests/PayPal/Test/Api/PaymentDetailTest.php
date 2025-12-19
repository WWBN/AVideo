<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentDetail;

/**
 * Class PaymentDetail
 *
 * @package PayPal\Test\Api
 */
class PaymentDetailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentDetail
     * @return string
     */
    public static function getJson()
    {
        return '{"type":"TestSample","transaction_id":"TestSample","transaction_type":"TestSample","date":"TestSample","method":"TestSample","note":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentDetail
     */
    public static function getObject()
    {
        return new PaymentDetail(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentDetail
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentDetail(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getTransactionId());
        $this->assertNotNull($obj->getTransactionType());
        $this->assertNotNull($obj->getDate());
        $this->assertNotNull($obj->getMethod());
        $this->assertNotNull($obj->getNote());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentDetail $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getTransactionId(), "TestSample");
        $this->assertEquals($obj->getTransactionType(), "TestSample");
        $this->assertEquals($obj->getDate(), "TestSample");
        $this->assertEquals($obj->getMethod(), "TestSample");
        $this->assertEquals($obj->getNote(), "TestSample");
    }

}
