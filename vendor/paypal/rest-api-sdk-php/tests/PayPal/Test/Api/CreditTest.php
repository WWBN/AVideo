<?php

namespace PayPal\Test\Api;

use PayPal\Api\Credit;

/**
 * Class Credit
 *
 * @package PayPal\Test\Api
 */
class CreditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Credit
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","type":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Credit
     */
    public static function getObject()
    {
        return new Credit(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Credit
     */
    public function testSerializationDeserialization()
    {
        $obj = new Credit(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getType());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Credit $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
    }


}
