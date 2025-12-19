<?php

namespace PayPal\Test\Api;

use PayPal\Api\Image;

/**
 * Class Image
 *
 * @package PayPal\Test\Api
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Patch
     * @return string
     */
    public static function getJson()
    {
        return '{"image":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Image
     */
    public static function getObject()
    {
        return new Image(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Image
     */
    public function testSerializationDeserialization()
    {
        $obj = new Image(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getImage());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Image $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getImage(), "TestSample");
    }

}
