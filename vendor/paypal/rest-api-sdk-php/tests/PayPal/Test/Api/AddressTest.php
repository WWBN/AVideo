<?php

namespace PayPal\Test\Api;

use PayPal\Api\Address;

/**
 * Class Address
 *
 * @package PayPal\Test\Api
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Address
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"line1":"TestSample","line2":"TestSample","city":"TestSample","country_code":"TestSample","postal_code":"TestSample","state":"TestSample","phone":"TestSample","normalization_status":"TestSample","status":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Address
     */
    public static function getObject()
    {
        return new Address(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Address
     */
    public function testSerializationDeserialization()
    {
        $obj = new Address(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getLine1());
        $this->assertNotNull($obj->getLine2());
        $this->assertNotNull($obj->getCity());
        $this->assertNotNull($obj->getCountryCode());
        $this->assertNotNull($obj->getPostalCode());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getPhone());
        $this->assertNotNull($obj->getNormalizationStatus());
        $this->assertNotNull($obj->getStatus());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Address $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getLine1(), "TestSample");
        $this->assertEquals($obj->getLine2(), "TestSample");
        $this->assertEquals($obj->getCity(), "TestSample");
        $this->assertEquals($obj->getCountryCode(), "TestSample");
        $this->assertEquals($obj->getPostalCode(), "TestSample");
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getPhone(), "TestSample");
        $this->assertEquals($obj->getNormalizationStatus(), "TestSample");
        $this->assertEquals($obj->getStatus(), "TestSample");
    }


}
