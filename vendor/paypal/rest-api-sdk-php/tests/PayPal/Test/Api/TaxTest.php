<?php

namespace PayPal\Test\Api;

use PayPal\Api\Tax;

/**
 * Class Tax
 *
 * @package PayPal\Test\Api
 */
class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Tax
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","name":"TestSample","percent":"12.34","amount":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Tax
     */
    public static function getObject()
    {
        return new Tax(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Tax
     */
    public function testSerializationDeserialization()
    {
        $obj = new Tax(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getPercent());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Tax $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getPercent(), "12.34");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
    }

}
