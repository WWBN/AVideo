<?php

namespace PayPal\Test\Api;

use PayPal\Api\CountryCode;

/**
 * Class CountryCode
 *
 * @package PayPal\Test\Api
 */
class CountryCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CountryCode
     * @return string
     */
    public static function getJson()
    {
        return '{"country_code":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CountryCode
     */
    public static function getObject()
    {
        return new CountryCode(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CountryCode
     */
    public function testSerializationDeserialization()
    {
        $obj = new CountryCode(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCountryCode());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CountryCode $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCountryCode(), "TestSample");
    }


}
