<?php

namespace PayPal\Test\Api;

use PayPal\Api\Cost;

/**
 * Class Cost
 *
 * @package PayPal\Test\Api
 */
class CostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Cost
     * @return string
     */
    public static function getJson()
    {
        return '{"percent":"12.34","amount":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Cost
     */
    public static function getObject()
    {
        return new Cost(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Cost
     */
    public function testSerializationDeserialization()
    {
        $obj = new Cost(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPercent());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Cost $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPercent(), "12.34");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
    }
    
}
