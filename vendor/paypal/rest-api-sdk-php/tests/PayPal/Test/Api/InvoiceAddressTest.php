<?php

namespace PayPal\Test\Api;

use PayPal\Api\InvoiceAddress;

/**
 * Class InvoiceAddress
 *
 * @package PayPal\Test\Api
 */
class InvoiceAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Address
     * @return string
     */
    public static function getJson()
    {
        return '{"line1":"TestSample","line2":"TestSample","city":"TestSample","country_code":"TestSample","postal_code":"TestSample","state":"TestSample","phone":'. PhoneTest::getJson() . "}";
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return InvoiceAddress
     */
    public static function getObject()
    {
        return new InvoiceAddress(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return InvoiceAddress
     */
    public function testSerializationDeserialization()
    {
        $obj = new InvoiceAddress(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getLine1());
        $this->assertNotNull($obj->getLine2());
        $this->assertNotNull($obj->getCity());
        $this->assertNotNull($obj->getCountryCode());
        $this->assertNotNull($obj->getPostalCode());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getPhone());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InvoiceAddress $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getLine1(), "TestSample");
        $this->assertEquals($obj->getLine2(), "TestSample");
        $this->assertEquals($obj->getCity(), "TestSample");
        $this->assertEquals($obj->getCountryCode(), "TestSample");
        $this->assertEquals($obj->getPostalCode(), "TestSample");
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getPhone(), PhoneTest::getObject());
    }

}
