<?php

namespace PayPal\Test\Api;

use PayPal\Api\Presentation;

/**
 * Class Presentation
 *
 * @package PayPal\Test\Api
 */
class PresentationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Presentation
     * @return string
     */
    public static function getJson()
    {
        return json_encode(json_decode('{"brand_name":"TestSample","logo_image":"TestSample","locale_code":"TestSample"}'));
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Presentation
     */
    public static function getObject()
    {
        return new Presentation(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Presentation
     */
    public function testSerializationDeserialization()
    {
        $obj = new Presentation(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBrandName());
        $this->assertNotNull($obj->getLogoImage());
        $this->assertNotNull($obj->getLocaleCode());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Presentation $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBrandName(), "TestSample");
        $this->assertEquals($obj->getLogoImage(), "TestSample");
        $this->assertEquals($obj->getLocaleCode(), "TestSample");
    }

}
