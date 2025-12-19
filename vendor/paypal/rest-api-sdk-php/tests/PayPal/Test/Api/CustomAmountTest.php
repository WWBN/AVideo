<?php

namespace PayPal\Test\Api;

use PayPal\Api\CustomAmount;

/**
 * Class CustomAmount
 *
 * @package PayPal\Test\Api
 */
class CustomAmountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CustomAmount
     * @return string
     */
    public static function getJson()
    {
        return '{"label":"TestSample","amount":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CustomAmount
     */
    public static function getObject()
    {
        return new CustomAmount(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CustomAmount
     */
    public function testSerializationDeserialization()
    {
        $obj = new CustomAmount(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getLabel());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CustomAmount $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getLabel(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
    }

}
