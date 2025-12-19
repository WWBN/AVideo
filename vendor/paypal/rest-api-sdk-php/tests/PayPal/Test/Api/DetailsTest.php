<?php

namespace PayPal\Test\Api;

use PayPal\Api\Details;

/**
 * Class Details
 *
 * @package PayPal\Test\Api
 */
class DetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Details
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"subtotal":"12.34","shipping":"12.34","tax":"12.34","handling_fee":"12.34","shipping_discount":"12.34","insurance":"12.34","gift_wrap":"12.34","fee":"12.34"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Details
     */
    public static function getObject()
    {
        return new Details(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Details
     */
    public function testSerializationDeserialization()
    {
        $obj = new Details(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getSubtotal());
        $this->assertNotNull($obj->getShipping());
        $this->assertNotNull($obj->getTax());
        $this->assertNotNull($obj->getHandlingFee());
        $this->assertNotNull($obj->getShippingDiscount());
        $this->assertNotNull($obj->getInsurance());
        $this->assertNotNull($obj->getGiftWrap());
        $this->assertNotNull($obj->getFee());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Details $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getSubtotal(), "12.34");
        $this->assertEquals($obj->getShipping(), "12.34");
        $this->assertEquals($obj->getTax(), "12.34");
        $this->assertEquals($obj->getHandlingFee(), "12.34");
        $this->assertEquals($obj->getShippingDiscount(), "12.34");
        $this->assertEquals($obj->getInsurance(), "12.34");
        $this->assertEquals($obj->getGiftWrap(), "12.34");
        $this->assertEquals($obj->getFee(), "12.34");
    }


}
