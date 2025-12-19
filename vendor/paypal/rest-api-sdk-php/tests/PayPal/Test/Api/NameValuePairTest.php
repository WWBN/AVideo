<?php

namespace PayPal\Test\Api;

use PayPal\Api\NameValuePair;

/**
 * Class NameValuePair
 *
 * @package PayPal\Test\Api
 */
class NameValuePairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object NameValuePair
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"name":"TestSample","value":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return NameValuePair
     */
    public static function getObject()
    {
        return new NameValuePair(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return NameValuePair
     */
    public function testSerializationDeserialization()
    {
        $obj = new NameValuePair(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getValue());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param NameValuePair $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getValue(), "TestSample");
    }


}
