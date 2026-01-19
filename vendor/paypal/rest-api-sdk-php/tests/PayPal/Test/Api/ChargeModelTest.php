<?php

namespace PayPal\Test\Api;

use PayPal\Api\ChargeModel;

/**
 * Class ChargeModel
 *
 * @package PayPal\Test\Api
 */
class ChargeModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ChargeModels
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","type":"TestSample","amount":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return ChargeModel
     */
    public static function getObject()
    {
        return new ChargeModel(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return ChargeModel
     */
    public function testSerializationDeserialization()
    {
        $obj = new ChargeModel(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ChargeModel $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
    }

}
