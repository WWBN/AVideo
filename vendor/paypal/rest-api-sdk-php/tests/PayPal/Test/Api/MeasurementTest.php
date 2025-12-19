<?php

namespace PayPal\Test\Api;

use PayPal\Api\Measurement;

/**
 * Class Measurement
 *
 * @package PayPal\Test\Api
 */
class MeasurementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Measurement
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"value":"TestSample","unit":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Measurement
     */
    public static function getObject()
    {
        return new Measurement(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Measurement
     */
    public function testSerializationDeserialization()
    {
        $obj = new Measurement(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getValue());
        $this->assertNotNull($obj->getUnit());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Measurement $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getValue(), "TestSample");
        $this->assertEquals($obj->getUnit(), "TestSample");
    }


}
