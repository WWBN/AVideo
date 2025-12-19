<?php

namespace PayPal\Test\Api;

use PayPal\Api\Links;

/**
 * Class Links
 *
 * @package PayPal\Test\Api
 */
class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Links
     * @return string
     */
    public static function getJson()
    {
        return '{"href":"TestSample","rel":"TestSample","targetSchema":' .HyperSchemaTest::getJson() . ',"method":"TestSample","enctype":"TestSample","schema":' .HyperSchemaTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Links
     */
    public static function getObject()
    {
        return new Links(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Links
     */
    public function testSerializationDeserialization()
    {
        $obj = new Links(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getHref());
        $this->assertNotNull($obj->getRel());
        $this->assertNotNull($obj->getTargetSchema());
        $this->assertNotNull($obj->getMethod());
        $this->assertNotNull($obj->getEnctype());
        $this->assertNotNull($obj->getSchema());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Links $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getHref(), "TestSample");
        $this->assertEquals($obj->getRel(), "TestSample");
        $this->assertEquals($obj->getTargetSchema(), HyperSchemaTest::getObject());
        $this->assertEquals($obj->getMethod(), "TestSample");
        $this->assertEquals($obj->getEnctype(), "TestSample");
        $this->assertEquals($obj->getSchema(), HyperSchemaTest::getObject());
    }

}
