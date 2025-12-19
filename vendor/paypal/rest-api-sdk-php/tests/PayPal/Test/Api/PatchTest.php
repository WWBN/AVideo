<?php

namespace PayPal\Test\Api;

use PayPal\Api\Patch;

/**
 * Class Patch
 *
 * @package PayPal\Test\Api
 */
class PatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Patch
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"op":"TestSample","path":"TestSample","value":"TestSampleObject","from":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Patch
     */
    public static function getObject()
    {
        return new Patch(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Patch
     */
    public function testSerializationDeserialization()
    {
        $obj = new Patch(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getOp());
        $this->assertNotNull($obj->getPath());
        $this->assertNotNull($obj->getValue());
        $this->assertNotNull($obj->getFrom());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Patch $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getOp(), "TestSample");
        $this->assertEquals($obj->getPath(), "TestSample");
        $this->assertEquals($obj->getValue(), "TestSampleObject");
        $this->assertEquals($obj->getFrom(), "TestSample");
    }


}
