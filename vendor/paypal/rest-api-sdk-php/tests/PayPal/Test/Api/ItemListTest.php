<?php

namespace PayPal\Test\Api;

use PayPal\Api\ItemList;

/**
 * Class ItemList
 *
 * @package PayPal\Test\Api
 */
class ItemListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ItemList
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"items":' . ItemTest::getJson() . ',"shipping_address":' . ShippingAddressTest::getJson() . ',"shipping_method":"TestSample","shipping_phone_number":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return ItemList
     */
    public static function getObject()
    {
        return new ItemList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return ItemList
     */
    public function testSerializationDeserialization()
    {
        $obj = new ItemList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getItems());
        $this->assertNotNull($obj->getShippingAddress());
        $this->assertNotNull($obj->getShippingMethod());
        $this->assertNotNull($obj->getShippingPhoneNumber());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ItemList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getItems(), ItemTest::getObject());
        $this->assertEquals($obj->getShippingAddress(), ShippingAddressTest::getObject());
        $this->assertEquals($obj->getShippingMethod(), "TestSample");
        $this->assertEquals($obj->getShippingPhoneNumber(), "TestSample");
    }


}
