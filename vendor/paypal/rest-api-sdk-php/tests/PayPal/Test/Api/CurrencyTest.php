<?php

namespace PayPal\Test\Api;

use PayPal\Api\Currency;

/**
 * Class Currency
 *
 * @package PayPal\Test\Api
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Currency
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"currency":"TestSample","value":"12.34"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Currency
     */
    public static function getObject()
    {
        return new Currency(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Currency
     */
    public function testSerializationDeserialization()
    {
        $obj = new Currency(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCurrency());
        $this->assertNotNull($obj->getValue());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Currency $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCurrency(), "TestSample");
        $this->assertEquals($obj->getValue(), "12.34");
    }


}
