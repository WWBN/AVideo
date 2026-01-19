<?php

namespace PayPal\Test\Api;

use PayPal\Api\HyperSchema;

/**
 * Class HyperSchema
 *
 * @package PayPal\Test\Api
 */
class HyperSchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object HyperSchema
     * @return string
     */
    public static function getJson()
    {
        return '{"fragmentResolution":"TestSample","readonly":true,"contentEncoding":"TestSample","pathStart":"TestSample","mediaType":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return HyperSchema
     */
    public static function getObject()
    {
        return new HyperSchema(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return HyperSchema
     */
    public function testSerializationDeserialization()
    {
        $obj = new HyperSchema(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getLinks());
        $this->assertNotNull($obj->getFragmentResolution());
        $this->assertNotNull($obj->getReadonly());
        $this->assertNotNull($obj->getContentEncoding());
        $this->assertNotNull($obj->getPathStart());
        $this->assertNotNull($obj->getMediaType());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param HyperSchema $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
        $this->assertEquals($obj->getFragmentResolution(), "TestSample");
        $this->assertEquals($obj->getReadonly(), true);
        $this->assertEquals($obj->getContentEncoding(), "TestSample");
        $this->assertEquals($obj->getPathStart(), "TestSample");
        $this->assertEquals($obj->getMediaType(), "TestSample");
    }

}
