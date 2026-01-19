<?php

namespace PayPal\Test\Api;

use PayPal\Api\ShippingCost;

/**
 * Class ShippingCost
 *
 * @package PayPal\Test\Api
 */
class ShippingCostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ShippingCost
     * @return string
     */
    public static function getJson()
    {
        return '{"amount":' .CurrencyTest::getJson() . ',"tax":' .TaxTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return ShippingCost
     */
    public static function getObject()
    {
        return new ShippingCost(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return ShippingCost
     */
    public function testSerializationDeserialization()
    {
        $obj = new ShippingCost(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getTax());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ShippingCost $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getTax(), TaxTest::getObject());
    }

}
