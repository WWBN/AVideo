<?php

namespace PayPal\Test\Api;

use PayPal\Api\Phone;

/**
 * Class Phone
 *
 * @package PayPal\Test\Api
 */
class PhoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Phone
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"country_code":"TestSample","national_number":"TestSample","extension":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Phone
     */
    public static function getObject()
    {
        return new Phone(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Phone
     */
    public function testSerializationDeserialization()
    {
        $obj = new Phone(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCountryCode());
        $this->assertNotNull($obj->getNationalNumber());
        $this->assertNotNull($obj->getExtension());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Phone $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCountryCode(), "TestSample");
        $this->assertEquals($obj->getNationalNumber(), "TestSample");
        $this->assertEquals($obj->getExtension(), "TestSample");
    }


}
